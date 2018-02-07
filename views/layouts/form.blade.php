@extends('cms-toolkit::layouts.main')

@section('appTypeClass', 'app--form')

@php
    $permalink = $permalink ?? true;
    $editor = $editor ?? false;
    $translate = $translate ?? false;
    $translateTitle = $translateTitle ?? $translate ?? false;
    $titleFormKey = $titleFormKey ?? 'title';
@endphp

@section('content')
    <div class="form" v-sticky data-sticky-id="navbar" data-sticky-offset="0" data-sticky-topoffset="12" >
        <div class="navbar navbar--sticky" data-sticky-top="navbar">
            @php
                $additionalFieldsets = $additionalFieldsets ?? [];
                array_unshift($additionalFieldsets, [
                    'fieldset' => 'content',
                    'label' => $contentFieldsetLabel ?? 'Content'
                ]);
            @endphp
            <a17-sticky-nav data-sticky-target="navbar" :items="{{ json_encode($additionalFieldsets) }}">

                <a17-title-editor @if(isset($editModalTitle)) modal-title="{{ $editModalTitle }}" @endif name="{{ $titleFormKey }}" slot="title">
                    <template slot="modal-form">
                        @partialView(($moduleName ?? null), 'create')
                    </template>
                </a17-title-editor>
                <div slot="actions">
                    <a17-langswitcher></a17-langswitcher>
                    <a17-button v-if="editor" type="button" variant="editor" size="small" @click="openEditor(-1)"><span v-svg symbol="editor"></span>Editor</a17-button>
                </div>
            </a17-sticky-nav>
        </div>
        <form action="{{ $saveUrl }}" novalidate v-on:submit.prevent="submitForm">
            <div class="container">
                <div class="wrapper wrapper--reverse" v-sticky data-sticky-id="publisher" data-sticky-offset="80">
                    <aside class="col col--aside">
                        <div class="publisher" data-sticky-target="publisher">
                            <a17-publisher></a17-publisher>
                            <a17-page-nav placeholder="Go to page" previous-url="{{ $parentPreviousUrl ?? '' }}" next-url="{{ $parentNextUrl ?? '' }}"></a17-page-nav>
                        </div>
                    </aside>
                    <section class="col col--primary">
                        <a17-fieldset title="{{ $contentFieldsetLabel ?? 'Content' }}" id="content" data-sticky-top="publisher">
                            @yield('contentFields')
                        </a17-fieldset>

                        @yield('fieldsets')
                    </section>
                </div>
            </div>

            <!-- Move to trash -->
            {{-- <a17-modal class="modal--tiny modal--form modal--withintro" ref="moveToTrashModal" title="Move To Trash">
                <p class="modal--tiny-title"><strong>Are you sure ?</strong></p>
                <p>This change can't be undone.</p>
                <a17-inputframe>
                    <a17-button variant="validate">Ok</a17-button> <a17-button variant="aslink" @click="$refs.moveToTrashModal.close()"><span>Cancel</span></a17-button>
                </a17-inputframe>
            </a17-modal> --}}

            <a17-spinner v-if="loading"></a17-spinner>
        </form>
    </div>
    <a17-modal class="modal--browser" ref="browser" mode="medium">
        <a17-browser />
    </a17-modal>
    <a17-editor v-if="editor" ref="editor" bg-color="{{ $siteColor ?? '#FFFFFF' }}"></a17-editor>
    <a17-previewer ref="preview"></a17-previewer>
@stop

@section('initialStore')

    window.STORE.form = {
        permalink: '{{ $permalink ? ($item->slug ?? '') : '' }}',
        baseUrl: '{{ $baseUrl }}',
        saveUrl: '{{ $saveUrl }}',
        previewUrl: '{{ $previewUrl or '' }}',
        restoreUrl: '{{ $restoreUrl or '' }}',
        blockPreviewUrl: '{{ $blockPreviewUrl or '' }}',
        availableRepeaters: {!! json_encode(config('cms-toolkit.block_editor.repeaters')) !!},
        repeaters: {!! json_encode(($form_fields['repeaters'] ?? []) + ($form_fields['blocksRepeaters'] ?? [])) !!},
        fields: []
    }

    window.STORE.publication = {
        withPublicationToggle: {{ json_encode($item->isFillable('published')) }},
        published: {{ json_encode($item->published) }},
        withPublicationTimeframe: {{ json_encode($item->isFillable('publish_start_date')) }},
        startDate: '{{ $item->publish_start_date ?? '' }}',
        endDate: '{{ $item->publish_end_date ?? '' }}',
        visibility: '{{ $item->isFillable('public') ? ($item->public ? 'public' : 'private') : false }}',
        reviewProcess: {!! isset($reviewProcess) ? json_encode($reviewProcess) : '[]' !!}
    }

    window.STORE.form.editor = {{ $editor ? 'true' : 'false' }}

    window.STORE.revisions = {!! json_encode($revisions ?? [])  !!}

    window.STORE.parentId = {{ $parentId ?? 0 }}
    window.STORE.parents = {!! json_encode($parents ?? [])  !!}

    window.STORE.medias.crops = {!! json_encode(($item->mediasParams ?? []) + config('cms-toolkit.block_editor.crops')) !!}
    window.STORE.medias.selected = {}

    window.STORE.browser = {}
    window.STORE.browser.selected = {}

    window.APIKEYS = {
        'googleMapApi': '{{ config('services.google.maps_api_key') }}'
    }
@stop

@push('extra_js')
    <script src="{{ mix('/assets/admin/js/manifest.js') }}"></script>
    <script src="{{ mix('/assets/admin/js/vendor.js') }}"></script>
    <script src="{{ mix('/assets/admin/js/main-form.js') }}"></script>
@endpush
