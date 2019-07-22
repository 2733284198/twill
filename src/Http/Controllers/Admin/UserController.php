<?php

namespace A17\Twill\Http\Controllers\Admin;

use A17\Twill\Models\Enums\UserRole;
use Illuminate\Auth\AuthManager;
use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\Router;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Session\Store as SessionStore;
use Illuminate\Support\Collection;
use Illuminate\View\Factory as ViewFactory;
use PragmaRX\Google2FAQRCode\Google2FA;

class UserController extends ModuleController
{
    /**
     * @var AuthManager
     */
    protected $authManager;

    /**
     * @var string
     */
    protected $namespace = 'A17\Twill';

    /**
     * @var string
     */
    protected $moduleName = 'users';

    /**
     * @var string[]
     */
    protected $indexWith = ['medias'];

    /**
     * @var array
     */
    protected $defaultOrders = ['name' => 'asc'];

    /**
     * @var array
     */
    protected $defaultFilters = [
        'search' => 'search',
    ];

    /**
     * @var array
     */
    protected $filters = [
        'role' => 'role',
    ];

    /**
     * @var string
     */
    protected $titleColumnKey = 'name';

    /**
     * @var array
     */
    protected $indexColumns = [
        'name' => [
            'title' => 'Name',
            'field' => 'name',
        ],
        'email' => [
            'title' => 'Email',
            'field' => 'email',
            'sort' => true,
        ],
        'role_value' => [
            'title' => 'Role',
            'field' => 'role_value',
            'sort' => true,
            'sortKey' => 'role',
        ],
    ];

    /**
     * @var array
     */
    protected $indexOptions = [
        'permalink' => false,
    ];

    /**
     * @var array
     */
    protected $fieldsPermissions = [
        'role' => 'manage-users',
    ];

    /**
     * @param Application $app
     * @param Config $config
     * @param Request $request
     * @param Router $router
     * @param SessionStore $sessionStore
     * @param AuthManager $authManager
     * @param Redirector $redirector
     * @param UrlGenerator $urlGenerator
     * @param ViewFactory $viewFactory
     * @param AuthFactory $authFactory
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        Application $app,
        Config $config,
        Request $request,
        Router $router,
        SessionStore $sessionStore,
        AuthManager $authManager,
        Redirector $redirector,
        UrlGenerator $urlGenerator,
        ViewFactory $viewFactory,
        AuthFactory $authFactory,
        ResponseFactory $responseFactory
    ) {
        parent::__construct($app, $request, $router, $sessionStore, $redirector, $urlGenerator, $viewFactory, $authFactory, $responseFactory, $config);
        $this->authManager = $authManager;
        $this->removeMiddleware('can:edit');
        $this->removeMiddleware('can:delete');
        $this->removeMiddleware('can:publish');
        $this->middleware('can:manage-users', ['only' => ['index']]);
        $this->middleware('can:edit-user,user', ['only' => ['store', 'edit', 'update', 'destroy', 'bulkDelete', 'restore', 'bulkRestore']]);
        $this->middleware('can:publish-user', ['only' => ['publish']]);

        if ($this->config->get('twill.enabled.users-image')) {
            $this->indexColumns = [
                'image' => [
                    'title' => 'Image',
                    'thumb' => true,
                    'variant' => [
                        'role' => 'profile',
                        'crop' => 'default',
                    ],
                ],
            ] + $this->indexColumns;
        }
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function indexData($request)
    {
        return [
            'defaultFilterSlug' => 'published',
            'create' => $this->getIndexOption('create') && $this->authFactory->guard('twill_users')->user()->can('manage-users'),
            'roleList' => Collection::make(UserRole::toArray()),
            'single_primary_nav' => [
                'users' => [
                    'title' => 'Users',
                    'module' => true,
                ],
            ],
            'customPublishedLabel' => 'Enabled',
            'customDraftLabel' => 'Disabled',
        ];
    }

    /**
     * @param Request $request
     * @return array
     * @throws \PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException
     * @throws \PragmaRX\Google2FA\Exceptions\InvalidCharactersException
     */
    protected function formData($request)
    {
        $user = $this->authManager->guard('twill_users')->user();
        $with2faSettings = $this->config->get('twill.enabled.users-2fa') && $user->id == $this->request->get('user');

        if ($with2faSettings) {
            $google2fa = new Google2FA();

            if (is_null($user->google_2fa_secret)) {
                $secret = $google2fa->generateSecretKey();
                $user->google_2fa_secret = \Crypt::encrypt($secret);
                $user->save();
            }

            $qrCode = $google2fa->getQRCodeInline(
                $this->config->get('app.name'),
                $user->email,
                \Crypt::decrypt($user->google_2fa_secret),
                200
            );
        }

        return [
            'roleList' => Collection::make(UserRole::toArray()),
            'single_primary_nav' => [
                'users' => [
                    'title' => 'Users',
                    'module' => true,
                ],
            ],
            'customPublishedLabel' => 'Enabled',
            'customDraftLabel' => 'Disabled',
            'with2faSettings' => $with2faSettings,
            'qrCode' => $qrCode ?? null,
        ];
    }

    /**
     * @return array
     */
    protected function getRequestFilters()
    {
        return json_decode($this->request->get('filter'), true) ?? ['status' => 'published'];
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection $items
     * @param array $scopes
     * @return array
     */
    public function getIndexTableMainFilters($items, $scopes = [])
    {
        $statusFilters = [];

        array_push($statusFilters, [
            'name' => 'Active',
            'slug' => 'published',
            'number' => $this->repository->getCountByStatusSlug('published'),
        ], [
            'name' => 'Disabled',
            'slug' => 'draft',
            'number' => $this->repository->getCountByStatusSlug('draft'),
        ]);

        if ($this->getIndexOption('restore')) {
            array_push($statusFilters, [
                'name' => 'Trash',
                'slug' => 'trash',
                'number' => $this->repository->getCountByStatusSlug('trash'),
            ]);
        }

        return $statusFilters;
    }

    /**
     * @param string $option
     * @return bool
     */
    protected function getIndexOption($option)
    {
        if (in_array($option, ['publish', 'delete', 'restore'])) {
            return $this->authFactory->guard('twill_users')->user()->can('manage-users');
        }

        return parent::getIndexOption($option);
    }

    /**
     * @param \A17\Twill\Models\Model $item
     * @return array
     */
    protected function indexItemData($item)
    {

        $user = $this->authFactory->guard('twill_users')->user();
        $canEdit = $user->can('manage-users') || $user->id === $item->id;
        return [
            'edit' => $canEdit ? $this->getModuleRoute($item->id, 'edit') : null,
        ];
    }
}
