<?php

namespace A17\Twill\Http\Controllers\Admin;

use A17\Twill\Http\Requests\Admin\MediaRequest;
use A17\Twill\Services\Uploader\SignS3Upload;
use A17\Twill\Services\Uploader\SignS3UploadListener;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\Router;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Session\Store as SessionStore;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\View\Factory as ViewFactory;

class MediaLibraryController extends ModuleController implements SignS3UploadListener
{
    /**
     * @var string
     */
    protected $moduleName = 'medias';

    /**
     * @var string
     */
    protected $namespace = 'A17\Twill';

    /**
     * @var array
     */
    protected $defaultOrders = [
        'id' => 'desc',
    ];

    /**
     * @var array
     */
    protected $defaultFilters = [
        'search' => 'search',
        'tag' => 'tag_id',
    ];

    /**
     * @var int
     */
    protected $perPage = 40;

    /**
     * @var string
     */
    protected $endpointType;

    /**
     * @var array
     */
    protected $customFields;

    /**
     * @param Application $app
     * @param Request $request
     * @param Router $router
     * @param SessionStore $sessionStore
     * @param Redirector $redirector
     * @param UrlGenerator $urlGenerator
     * @param ViewFactory $viewFactory
     * @param AuthFactory $authFactory
     */
    public function __construct(
        Application $app,
        Request $request,
        Router $router,
        SessionStore $sessionStore,
        Redirector $redirector,
        UrlGenerator $urlGenerator,
        ViewFactory $viewFactory,
        AuthFactory $authFactory
    ) {
        parent::__construct($app, $request, $router, $sessionStore, $redirector, $urlGenerator, $viewFactory, $authFactory);
        $this->removeMiddleware('can:edit');
        $this->middleware('can:edit', ['only' => ['signS3Upload', 'tags', 'store', 'singleUpdate', 'bulkUpdate']]);
        $this->endpointType = config('twill.media_library.endpoint_type');
        $this->customFields = config('twill.media_library.extra_metadatas_fields');
    }

    /**
     * @param int|null $parentModuleId
     * @return array
     */
    public function index($parentModuleId = null)
    {
        if ($this->request->has('except')) {
            $prependScope['exceptIds'] = $this->request->get('except');
        }

        return $this->getIndexData($prependScope ?? []);
    }

    /**
     * @param array $prependScope
     * @return array
     */
    public function getIndexData($prependScope = [])
    {
        $scopes = $this->filterScope($prependScope);
        $items = $this->getIndexItems($scopes);

        return [
            'items' => $items->map(function ($item) {
                return $item->toCmsArray();
            })->toArray(),
            'maxPage' => $items->lastPage(),
            'total' => $items->total(),
            'tags' => $this->repository->getTagsList(),
        ];
    }

    /**
     * @return array
     */
    protected function getRequestFilters()
    {
        if ($this->request->has('search')) {
            $requestFilters['search'] = $this->request->get('search');
        }

        if ($this->request->has('tag')) {
            $requestFilters['tag'] = $this->request->get('tag');
        }

        return $requestFilters ?? [];
    }

    /**
     * @param int|null $parentModuleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($parentModuleId = null)
    {
        $request = $this->app->get(MediaRequest::class);

        if ($this->endpointType === 'local') {
            $media = $this->storeFile($request);
        } else {
            $media = $this->storeReference($request);
        }

        return response()->json(['media' => $media->toCmsArray(), 'success' => true], 200);
    }

    /**
     * @param Request $request
     * @return \A17\Twill\Models\Media
     */
    public function storeFile($request)
    {
        $originalFilename = $request->input('qqfilename');

        $filename = sanitizeFilename($originalFilename);

        $fileDirectory = $request->input('unique_folder_name');

        $disk = config('twill.media_library.disk');

        $request->file('qqfile')->storeAs($fileDirectory, $filename, $disk);

        $filePath = Storage::disk($disk)->path($fileDirectory . '/' . $filename);

        list($w, $h) = getimagesize($filePath);

        $fields = [
            'uuid' => $request->input('unique_folder_name') . '/' . $filename,
            'filename' => $originalFilename,
            'width' => $w,
            'height' => $h,
        ];

        return $this->repository->create($fields);
    }

    /**
     * @param Request $request
     * @return \A17\Twill\Models\Media
     */
    public function storeReference($request)
    {
        $fields = [
            'uuid' => $request->input('key'),
            'filename' => $request->input('name'),
            'width' => $request->input('width'),
            'height' => $request->input('height'),
        ];

        return $this->repository->create($fields);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function singleUpdate()
    {
        $this->repository->update(
            $this->request->input('id'),
            array_merge([
                'alt_text' => $this->request->get('alt_text', null),
                'caption' => $this->request->get('caption', null),
                'tags' => $this->request->get('tags', null),
            ], $this->getExtraMetadatas()->toArray())
        );

        return response()->json([
            'tags' => $this->repository->getTagsList(),
        ], 200);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkUpdate()
    {
        $ids = explode(',', $this->request->input('ids'));

        $metadatasFromRequest = $this->getExtraMetadatas()->reject(function ($meta) {
            return is_null($meta);
        })->toArray();

        $extraMetadatas = array_diff_key($metadatasFromRequest, array_flip((array) $this->request->get('fieldsRemovedFromBulkEditing', [])));

        if (in_array('tags', $this->request->get('fieldsRemovedFromBulkEditing', []))) {
            $this->repository->addIgnoreFieldsBeforeSave('bulk_tags');
        } else {
            $previousCommonTags = $this->repository->getTags(null, $ids);
            $newTags = array_filter(explode(',', $this->request->input('tags')));
        }

        foreach ($ids as $id) {
            $this->repository->update($id, [
                'bulk_tags' => $newTags ?? [],
                'previous_common_tags' => $previousCommonTags ?? [],
            ] + $extraMetadatas);
        }

        $scopes = $this->filterScope(['id' => $ids]);
        $items = $this->getIndexItems($scopes);

        return response()->json([
            'items' => $items->map(function ($item) {
                return $item->toCmsArray();
            })->toArray(),
            'tags' => $this->repository->getTagsList(),
        ], 200);
    }

    /**
     * @param Request $request
     * @param SignS3Upload $signS3Upload
     * @return mixed
     */
    public function signS3Upload(Request $request, SignS3Upload $signS3Upload)
    {
        return $signS3Upload->fromPolicy($request->getContent(), $this, config('twill.media_library.disk'));
    }

    /**
     * @param mixed $signedPolicy
     * @return \Illuminate\Http\JsonResponse
     */
    public function policyIsSigned($signedPolicy)
    {
        return response()->json($signedPolicy, 200);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function policyIsNotValid()
    {
        return response()->json(["invalid" => true], 500);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    private function getExtraMetadatas()
    {
        return Collection::make($this->customFields)->mapWithKeys(function ($field) {
            $fieldInRequest = $this->request->get($field['name']);

            if (isset($field['type']) && $field['type'] === 'checkbox') {
                return [$field['name'] => $fieldInRequest ? Arr::first($fieldInRequest) : false];
            }

            return [$field['name'] => $fieldInRequest];
        });
    }
}
