<?php

namespace A17\CmsToolkit\Http\Controllers\Admin;

use A17\CmsToolkit\Models\Feature;
use App\Http\Controllers\Controller;
use DB;

class FeaturedController extends Controller
{
    public function index()
    {
        $featuredSectionKey = request()->segment(count(request()->segments()));
        $featuredSection = config("cms-toolkit.buckets.$featuredSectionKey");
        $filters = json_decode(request()->get('filter'), true) ?? [];

        $featuredSources = $this->getFeaturedSources($featuredSection, $filters['search'] ?? '');

        $contentTypes = collect($featuredSources)->map(function ($source, $sourceKey) {
            return [
                'label' => $source['name'],
                'value' => $sourceKey,
            ];
        })->values()->toArray();

        if (request()->has('content_type')) {
            $source = array_first($featuredSources, function ($source, $sourceKey) {
                return $sourceKey == request('content_type');
            });

            return [
                'source' => [
                    'content_type' => array_first($contentTypes, function ($contentTypeItem) {
                        return $contentTypeItem['value'] == request('content_type');
                    }),
                    'items' => $source['items'],
                ],
                'maxPage' => $source['maxPage'],
            ];
        }

        $buckets = $this->getFeaturedItemsByBucket($featuredSection, $featuredSectionKey);
        $firstSource = array_first($featuredSources);

        $this->prepareSessionWithCurrentFeatures($buckets);

        return view('cms-toolkit::layouts.buckets', [
            'dataSources' => [
                'selected' => array_first($contentTypes),
                'content_types' => $contentTypes,
            ],
            'items' => $buckets,
            'source' => [
                'content_type' => array_first($contentTypes),
                'items' => $firstSource['items'],
            ],
            'maxPage' => $firstSource['maxPage'],
            'offset' => $firstSource['offset'],
        ]);
    }

    private function getFeaturedItemsByBucket($featuredSection, $featuredSectionKey)
    {
        return collect($featuredSection['buckets'])->map(function ($bucket, $bucketKey) use ($featuredSectionKey) {
            return [
                'id' => $bucketKey,
                'name' => $bucket['name'],
                'max' => $bucket['max_items'],
                'addUrl' => route("admin.featured.$featuredSectionKey.add", ['bucket' => $bucketKey]),
                'removeUrl' => route("admin.featured.$featuredSectionKey.remove", ['bucket' => $bucketKey]),
                'reorderUrl' => route("admin.featured.$featuredSectionKey.sortable", ['bucket' => $bucketKey]),
                'children' => Feature::where('bucket_key', $bucketKey)->with('featured')->get()->map(function ($feature) {
                    $item = $feature->featured;
                    return [
                        'id' => $item->id,
                        'name' => $item->titleInBucket,
                        'edit' => $item->adminEditUrl,
                        'content_type' => [
                            'label' => ucfirst($feature->featured_type),
                            'value' => $feature->featured_type,
                        ],
                    ];
                })->toArray(),
            ];
        })->values()->toArray();
    }

    private function getFeaturedSources($featuredSection, $search = null)
    {
        $fetchedModules = [];
        $featuredSources = [];

        collect($featuredSection['buckets'])->map(function ($bucket, $bucketKey) use (&$fetchedModules, $search) {
            return collect($bucket['bucketables'])->mapWithKeys(function ($bucketable) use (&$fetchedModules, $bucketKey, $search) {

                $module = $bucketable['module'];

                if ($search) {
                    $searchField = $bucketable['search_field'] ?? '%title';
                    $scopes[$searchField] = $search;
                }

                $items = $fetchedModules[$module] ?? $this->getRepository($module)->get(
                    $bucketable['with'] ?? [],
                    ($bucketable['scopes'] ?? []) + ($scopes ?? []),
                    $bucketable['orders'] ?? [],
                    $bucketable['per_page'] ?? request('offset') ?? 10,
                    $forcePagination = true
                )->appends('bucketable', $module);

                $fetchedModules[$module] = $items;

                return [$module => [
                    'name' => $bucketable['name'] ?? ucfirst($module),
                    'items' => $items,
                ]];
            });
        })->each(function ($bucketables, $bucket) use (&$featuredSources) {
            $bucketables->each(function ($bucketableData, $bucketable) use ($bucket, &$featuredSources) {
                // $featuredSources[$bucketable]['buckets'][] = $bucket; // not used at the moment because our new components are not supporting restricting items from going into a certain bucket.
                $featuredSources[$bucketable]['name'] = $bucketableData['name'];
                $featuredSources[$bucketable]['maxPage'] = $bucketableData['items']->lastPage();
                $featuredSources[$bucketable]['offset'] = $bucketableData['items']->perPage();
                $featuredSources[$bucketable]['items'] = $bucketableData['items']->map(function ($item) use ($bucketableData, $bucketable) {
                    return [
                        'id' => $item->id,
                        'name' => $item->titleInBucket,
                        'edit' => $item->adminEditUrl,
                        'content_type' => [
                            'label' => $bucketableData['name'],
                            'value' => $bucketable,
                        ],
                    ];
                })->toArray();
            });

        });

        return $featuredSources;
    }

    private function prepareSessionWithCurrentFeatures($buckets)
    {
        session()->forget('buckets');
        collect($buckets)->each(function ($bucket) {
            foreach ($bucket['children'] as $feature) {
                session()->push("buckets." . $bucket['id'], [
                    'id' => $feature['id'],
                    'type' => $feature['content_type']['value'],
                ]);
            }
        });
    }

    public function add($bucket)
    {
        session()->push("buckets.$bucket", request()->all());
        $this->save();
        return response()->json();
    }

    public function remove($bucket)
    {
        $currentBucket = session()->get("buckets.$bucket");

        collect($currentBucket)->each(function ($bucketItem, $index) use (&$currentBucket) {
            if ($bucketItem['id'] === request('id') && $bucketItem['type'] === request('type')) {
                unset($currentBucket[$index]);
            }
        });

        session()->put("buckets.$bucket", $currentBucket);
        $this->save();
        return response()->json();
    }

    public function sortable($bucket)
    {
        if ($bucket != null && ($values = request('buckets')) && !empty($values)) {
            session()->put("buckets.$bucket", $values);
            $this->save();
        }
    }

    public function save()
    {
        DB::transaction(function () {
            collect(session()->get('buckets'))->each(function ($bucketables, $bucketKey) {
                Feature::where('bucket_key', $bucketKey)->delete();
                foreach (($bucketables ?? []) as $position => $bucketable) {
                    Feature::create([
                        'featured_id' => $bucketable['id'],
                        'featured_type' => $bucketable['type'],
                        'position' => $position + 1,
                        'bucket_key' => $bucketKey,
                    ]);
                }
            });
        });
        \Event::fire('buckets.saved');
    }

    private function getRepository($bucketable)
    {
        return app(config('cms-toolkit.namespace') . "\Repositories\\" . ucfirst(str_singular($bucketable)) . "Repository");
    }

}
