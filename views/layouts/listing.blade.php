@extends('cms-toolkit::layouts.main')

@section('appTypeClass', 'app--listing')

@php
    $translate = $translate ?? false;
    $translateTitle = $translateTitle ?? $translate ?? false;
@endphp

@section('content')
    <div class="listing">
        <div class="listing__nav">
            <div class="container" ref="form">
                <a17-filter v-on:submit="filterListing" v-bind:closed="hasBulkIds" initial-search-value="{{ $filters['search'] ?? '' }}" :clear-option="true" v-on:clear="clearFiltersAndReloadDatas">
                    <ul class="secondarynav secondarynav--desktop" slot="navigation">
                        <li v-for="(navItem, index) in navFilters" class="secondarynav__item" :class="{ 's--on' : navActive === navItem.slug }">
                            <a href="#" v-on:click.prevent="filterStatus(navItem.slug)">
                                <span class="secondarynav__link">@{{ navItem.name }}</span><span class="secondarynav__number">(@{{ navItem.number }})</span>
                            </a>
                        </li>
                    </ul>

                    <div class="secondarynav secondarynav--mobile secondarynav--dropdown" slot="navigation">
                        <a17-dropdown ref="secondaryNavDropdown" position="bottom-left" width="full" :offset="0">
                            <a17-button class="secondarynav__button" variant="dropdown-transparent" size="small" @click="$refs.secondaryNavDropdown.toggle()">
                                <span class="secondarynav__link">@{{ selectedNav.name }}</span><span class="secondarynav__number">(@{{ selectedNav.number }})</span>
                            </a17-button>
                            <div slot="dropdown__content">
                                <ul>
                                    <li v-for="(navItem, index) in navFilters" class="secondarynav__item">
                                        <a href="#" v-on:click.prevent="filterStatus(navItem.slug)">
                                            <span class="secondarynav__link">@{{ navItem.name }}</span><span class="secondarynav__number">(@{{ navItem.number }})</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </a17-dropdown>
                    </div>

                    @forelse($hiddenFilters as $filter)
                        @if ($loop->first)
                            <div slot="hidden-filters">
                        @endif

                        @if (isset(${$filter.'List'}))
                            <a17-vselect
                            name="{{ $filter }}"
                            :options="{{ json_encode(${$filter.'List'}->map(function($label, $value) {
                                return [
                                    'value' => $value,
                                    'label' => $label
                                ];
                            })->values()->toArray()) }}"
                            placeholder="All {{ strtolower(str_plural(str_replace_first('f', '', $filter))) }}"
                            ></a17-vselect>
                        @endif

                        @if ($loop->last)
                            </div>
                        @endif
                    @empty
                        @hasSection('hiddenFilters')
                            <div slot="hidden-filters">
                                @yield('hiddenFilters')
                            </div>
                        @endif
                    @endforelse

                    @if($create ?? false)
                        <div slot="additional-actions">
                            <a17-button variant="validate" size="small" v-on:click="$refs.addNewModal.open()">Add new</a17-button>
                        </div>
                    @endif
                </a17-filter>
            </div>
            <a17-bulk></a17-bulk>
        </div>

        <a17-datatable :draggable="{{ $reorder ? 'true' : 'false' }}" empty-message="There is no item here yet."></a17-datatable>

        <a17-modal class="modal--form" ref="addNewModal" title="Add new">
            <form action="{{ $storeUrl }}" method="post">
                {{ csrf_field() }}
                @partialView(($moduleName ?? null), 'create', ['renderForModal' => true])
                <a17-modal-validation v-bind:mode="'create'" :active-publish-state="false" :is-publish="false" published-name="published"></a17-modal-validation>
            </form>
        </a17-modal>
    </div>
@stop

@section('initialStore')
    window.CMS_URLS = {
        index: @if(isset($indexUrl)) '{{ $indexUrl }}' @else window.location.href.split('?')[0] @endif,
        publish: '{{ $publishUrl }}',
        bulkPublish: '{{ $bulkPublishUrl }}',
        restore: '{{ $restoreUrl }}',
        bulkRestore: '{{ $bulkRestoreUrl }}',
        reorder: '{{ $reorderUrl }}',
        feature: '{{ $featureUrl }}',
        bulkFeature: '{{ $bulkFeatureUrl }}',
        bulkDelete: '{{ $bulkDeleteUrl }}'
    }

    window.STORE.datatable = {
        data: {!! json_encode($tableData) !!},
        columns: {!! json_encode($tableColumns) !!},
        navigation: {!! json_encode($tableMainFilters) !!},
        filter: { status: '{{ $filters['status'] ?? $defaultFilterSlug ?? 'all' }}' },
        page: {{ request('page') ?? 1 }},
        maxPage: {{ $maxPage ?? 1 }},
        defaultMaxPage: {{ $defaultMaxPage ?? 1 }},
        offset: {{ request('offset') ?? $offset ?? 60 }},
        defaultOffset: {{ $defaultOffset ?? 60 }},
        sortKey: '{{ $reorder ? (request('sortKey') ?? '') : (request('sortKey') ?? 'name') }}',
        sortDir: '{{ request('sortDir') ?? 'asc' }}',
        baseUrl: '{{ rtrim(config('app.url'), '/') . '/' }}',
        localStorageKey: '{{ isset($currentUser) ? $currentUser->id : 0 }}__{{ $moduleName ?? Route::currentRouteName() }}'
    }
@stop

@push('extra_js')
    <script src="{{ mix('/assets/admin/js/manifest.js') }}"></script>
    <script src="{{ mix('/assets/admin/js/vendor.js') }}"></script>
    <script src="{{ mix('/assets/admin/js/main-listing.js') }}"></script>
@endpush
