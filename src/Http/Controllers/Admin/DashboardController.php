<?php

namespace A17\Twill\Http\Controllers\Admin;

use A17\Twill\Models\Behaviors\HasMedias;
use Analytics;
use App\Models\Translations\WorkTranslation;
use App\Models\Work;
use DB;
use Spatie\Activitylog\Models\Activity;
use Spatie\Analytics\Exceptions\InvalidConfiguration;
use Spatie\Analytics\Period;

class DashboardController extends Controller
{
    public function index()
    {
        $modules = collect(config('twill.dashboard.modules'));

        return view('twill::layouts.dashboard', [
            'allActivityData' => $this->getAllActivities(),
            'myActivityData' => $this->getLoggedInUserActivities(),
            'tableColumns' => [
                [
                    'name' => 'thumbnail',
                    'label' => 'Thumbnail',
                    'visible' => true,
                    'optional' => false,
                    'sortable' => false,
                ],
                [
                    'name' => 'published',
                    'label' => 'Published',
                    'visible' => true,
                    'optional' => false,
                    'sortable' => false,
                ],
                [
                    'name' => 'name',
                    'label' => 'Name',
                    'visible' => true,
                    'optional' => false,
                    'sortable' => true,
                ],
            ],
            'shortcuts' => $this->getShortcuts($modules),
            'facts' => config('twill.dashboard.analytics.enabled', false) ? $this->getFacts() : null,
        ]);
    }

    public function search()
    {
        $workIds = WorkTranslation::select('work_id')
            ->where('locale', 'en')
            ->twillSearch(request('search'))
            ->limit(3)
            ->get()->map(function ($workTranslation) {
            return $workTranslation->work_id;
        })->values()->toArray();

        $works = Work::whereIn('id', $workIds)->orderBy(DB::raw('FIELD(`id`, ' . implode(',', $workIds) . ')'))->get();

        $results = $works->map(function ($work) {

            try {
                $author = $work->revisions()->latest()->first()->user->name ?? 'Admin';
            } catch (\Exception $e) {
                $author = 'Admin';
            }

            return [
                'id' => $work->id,
                'href' => moduleRoute('works', 'work', 'edit', $work->id),
                'thumbnail' => $work->cmsImage('cover', 'default', ['w' => 100, 'h' => 100]),
                'published' => $work->published,
                'activity' => 'Last edited',
                'date' => $work->updated_at->toIso8601String(),
                'title' => $work->title,
                'author' => $author,
                'type' => "Work",
            ];
        })->toArray();

        return $results;
    }

    private function getAllActivities()
    {
        return Activity::take(20)->latest()->get()->map(function ($activity) {
            return $this->formatActivity($activity);
        })->filter()->values();
    }

    private function getLoggedInUserActivities()
    {
        return Activity::where('causer_id', auth()->user()->id)->take(20)->latest()->get()->map(function ($activity) {
            return $this->formatActivity($activity);
        })->filter()->values();
    }

    private function formatActivity($activity)
    {
        $dashboardModule = config('twill.dashboard.modules.' . $activity->subject_type);

        if (!$dashboardModule) {
            return null;
        }

        return [
            'id' => $activity->id,
            'type' => ucfirst($activity->subject_type),
            'date' => $activity->created_at->toIso8601String(),
            'author' => $activity->causer->name ?? 'Unknown',
            'name' => $activity->subject->titleInDashboard ?? $activity->subject->title,
            'activity' => ucfirst($activity->description),
        ] + (classHasTrait($activity->subject, HasMedias::class) ? [
            'thumbnail' => $activity->subject->defaultCmsImage(['w' => 100, 'h' => 100]),
        ] : []) + (!$activity->subject->trashed() ? [
            'edit' => moduleRoute($activity->subject_type, $dashboardModule ? $dashboardModule['routePrefix'] : '', 'edit', $activity->subject_id),
        ] : []) + (!is_null($activity->subject->published) ? [
            'published' => $activity->description === 'published' ? true : ($activity->description === 'unpublished' ? false : $activity->subject->published),
        ] : []);
    }

    private function getFacts()
    {
        try {
            $response = Analytics::performQuery(
                Period::days(60),
                'ga:users,ga:pageviews,ga:bouncerate,ga:pageviewsPerSession',
                ['dimensions' => 'ga:date']
            );
        } catch (InvalidConfiguration $exception) {
            \Log::error($exception);
            return [];
        }

        $statsByDate = collect($response['rows'] ?? [])->map(function (array $dateRow) {
            return [
                'date' => $dateRow[0],
                'users' => (int) $dateRow[1],
                'pageViews' => (int) $dateRow[2],
                'bounceRate' => $dateRow[3],
                'pageviewsPerSession' => $dateRow[4],
            ];
        })->reverse()->values();

        return collect([
            'today',
            'yesterday',
            'week',
            'month',
        ])->mapWithKeys(function ($period) use ($statsByDate) {
            $stats = $this->getPeriodStats($period, $statsByDate);
            return [
                $period => [
                    [
                        'label' => 'Users',
                        'figure' => $this->formatStat($stats['stats']['users']),
                        'insight' => round($stats['stats']['bounceRate']) . '% Bounce rate',
                        'trend' => 'up',
                        'data' => $period == 'yesterday' ? [10, 8, 26, 4, 45, 56, 32, 65, 35, 100, 90, 150] : array_map(function ($el) {return $el * rand(2, 3);}, [864, 1100, 978, 1132, 1291, 1143, 1112, 895, 1075, 1043, 888, 1500]),
                        'url' => 'https://analytics.google.com/analytics/web',
                    ],
                    [
                        'label' => 'Pageviews',
                        'figure' => $this->formatStat($stats['stats']['pageViews']),
                        'insight' => round($stats['stats']['pageviewsPerSession'], 1) . ' Pages / Session',
                        'trend' => 'up',
                        'data' => $period == 'yesterday' ? [10, 10, 20, 8, 50, 40, 20, 80, 30, 100, 90, 120] : [864, 1033, 826, 1018, 1118, 1100, 978, 1132, 1291, 1466, 964, 1500],
                        'url' => 'https://analytics.google.com/analytics/web',
                    ],
                    [
                        'label' => 'Newsletter signup',
                        'figure' => 10,
                        'insight' => '3 Unverified',
                        'trend' => 'up',
                        'data' => $period == 'yesterday' ? [10, 20, 7, 8, 30, 10, 20, 7, 8, 30, 10, 20, 30] : array_map(function ($el) {return $el * rand(1, 2);}, [864, 826, 1018, 1118, 1100, 978, 1132, 1291, 1143, 1112, 895, 1500]),
                        'url' => 'https://analytics.google.com/analytics/web',
                    ],
                ],
            ];
        });
    }

    private function getPeriodStats($period, $statsByDate)
    {
        if ($period === 'today') {
            return [
                'stats' => $stats = $statsByDate->first(),
                'moreUsers' => $stats['users'] > $statsByDate->get(1)['users'],
                'morePageViews' => $stats['pageViews'] > $statsByDate->get(1)['pageViews'],
                'usersData' => $statsByDate->take(7)->map(function ($stat) {
                    return $stat['users'];
                }),
                'pageViewsData' => $statsByDate->take(7)->map(function ($stat) {
                    return $stat['pageViews'];
                }),
            ];
        } elseif ($period === 'yesterday') {
            return [
                'stats' => $stats = $statsByDate->get(1),
                'moreUsers' => $stats['users'] > $statsByDate->get(2)['users'],
                'morePageViews' => $stats['pageViews'] > $statsByDate->get(2)['pageViews'],
                'usersData' => $statsByDate->slice(1)->take(7)->map(function ($stat) {
                    return $stat['users'];
                }),
                'pageViewsData' => $statsByDate->slice(1)->take(7)->map(function ($stat) {
                    return $stat['pageViews'];
                }),
            ];
        } elseif ($period === 'week') {
            $first7stats = $statsByDate->take(7)->all();

            $stats = [
                'users' => array_sum(array_column($first7stats, 'users')),
                'pageViews' => array_sum(array_column($first7stats, 'pageViews')),
                'bounceRate' => array_sum(array_column($first7stats, 'bounceRate')) / 7,
                'pageviewsPerSession' => array_sum(array_column($first7stats, 'pageviewsPerSession')) / 7,
            ];

            $compareStats = [
                'users' => array_sum(array_column($statsByDate->slice(7)->take(7)->all(), 'users')),
                'pageViews' => array_sum(array_column($statsByDate->slice(7)->take(7)->all(), 'pageViews')),
            ];

            return [
                'stats' => $stats,
                'moreUsers' => $stats['users'] > $compareStats['users'],
                'morePageViews' => $stats['pageViews'] > $compareStats['pageViews'],
                'usersData' => $statsByDate->slice(1)->take(29)->map(function ($stat) {
                    return $stat['users'];
                }),
                'pageViewsData' => $statsByDate->slice(1)->take(29)->map(function ($stat) {
                    return $stat['pageViews'];
                }),
            ];
        } elseif ($period === 'month') {
            $first30stats = $statsByDate->take(30)->all();

            $stats = [
                'users' => array_sum(array_column($first30stats, 'users')),
                'pageViews' => array_sum(array_column($first30stats, 'pageViews')),
                'bounceRate' => array_sum(array_column($first30stats, 'bounceRate')) / 30,
                'pageviewsPerSession' => array_sum(array_column($first30stats, 'pageviewsPerSession')) / 30,
            ];

            $compareStats = [
                'users' => array_sum(array_column($statsByDate->slice(30)->take(30)->all(), 'users')),
                'pageViews' => array_sum(array_column($statsByDate->slice(30)->take(30)->all(), 'pageViews')),
            ];

            return [
                'stats' => $stats,
                'moreUsers' => $stats['users'] > $compareStats['users'],
                'morePageViews' => $stats['pageViews'] > $compareStats['pageViews'],
                'usersData' => $statsByDate->slice(1)->take(29)->map(function ($stat) {
                    return $stat['users'];
                }),
                'pageViewsData' => $statsByDate->slice(1)->take(29)->map(function ($stat) {
                    return $stat['pageViews'];
                }),
            ];
        }
    }

    private function formatStat($count)
    {
        if ($count >= 1000) {
            return round($count / 1000, 1) . "k";
        }

        return $count;
    }

    private function getShortcuts($modules)
    {
        return $modules->filter(function ($module) {
            return ($module['count'] ?? false) || ($module['create'] ?? false);
        })->map(function ($module) {
            $repository = $this->getRepository($module['name']);

            $moduleOptions = [
                'count' => $module['count'] ?? false,
                'create' => $module['create'] ?? false,
                'label' => $module['label'] ?? $module['name'],
                'singular' => $module['label_singular'] ?? str_singular($module['name']),
            ];

            return [
                'label' => ucfirst($moduleOptions['label']),
                'singular' => ucfirst($moduleOptions['singular']),
                'number' => $moduleOptions['count'] ? $repository->getCountByStatusSlug(
                    'all', $module['countScope'] ?? []
                ) : null,
                'url' => moduleRoute(
                    $module['name'],
                    $module['routePrefix'] ?? null,
                    'index'
                ),
                'createUrl' => $moduleOptions['create'] ? moduleRoute(
                    $module['name'],
                    $module['routePrefix'] ?? null,
                    'index',
                    ['openCreate' => true]
                ) : null
            ];
        })->values();
    }

    private function getRepository($module)
    {
        return app(config('twill.namespace') . "\Repositories\\" . ucfirst(str_singular($module)) . "Repository");
    }
}
