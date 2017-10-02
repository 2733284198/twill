@php
    $create = $create ?? true;
    $search = $search ?? true;
    $sort = $sort ?? false;
    $publish = $publish ?? true;
    $edit = $edit ?? true;
    $show_locale_edit_links = $show_locale_edit_links ?? false;
    $delete = $delete ?? true;
    $toggle_columns = $toggle_columns ?? [];
    $preview = $preview ?? false;
    $previewLandingUrl = $previewLandingUrl ?? false;
@endphp

@extends('cms-toolkit::layouts.main')

@section('content')
    @if($help_message ?? null && !empty($help_message))
        <div class="message message-help">
            <p>{!! $help_message !!}</p>
        </div>
    @endif
    @if($search || !empty($filters))
        <div class="filter">
            <form method="GET" accept-charset="UTF-8" novalidate="novalidate" class="{{ $filtersOn ? 'on' : '' }}">
                @if ($search)
                    <input type="text" name="fSearch" placeholder="Search" autocomplete="off" size="20" value="{{ $fSearch or '' }}" style="padding: 3px 10px 5px 10px;">
                @endif
                {{-- TODO: get rid of those inline styles --}}
                <style>
                    .filter .select2-container--default .select2-selection--single {
                        background-color: #fcfcfc;
                        border: 1px solid #d9d9d9;
                        border-radius: 2px;
                    }
                    .filter .select2-container {
                        margin-right: 10px;
                    }
                </style>
                @foreach($filters as $filter)
                    @if (isset(${$filter.'List'}))
                        {!! Form::select($filter, ${$filter.'List'} , ${$filter} ?? null, [
                            'data-behavior' => 'selector',
                            'data-selector-width' => '168px',
                            'data-minimum-results-for-search' => 16,
                            'class' => 'select',
                            'style' => 'display: none;'
                        ]) !!}
                    @endif
                @endforeach
                @yield('extra_filters')
                <input type="submit" class="btn btn-small" value="Filter">
                @hasSection('clear_link')
                    @yield('clear_link')
                @else
                    <a href="{{ Request::url() }}">Clear</a>
                @endif
            </form>
        </div>
    @endunless

    <section class="box">
        <header class="header_small">
            <h3>
                <b>
                    @if (isset($title))
                        @php
                            $title = ucfirst($title);
                            $countItems = (!$sort ? (method_exists($items, 'total') ? $items->total() : count($items))  : count($items));
                        @endphp
                        {{  $countItems . ' ' . ($countItems > 1 ? str_plural($title) : $title) }}
                    @endif
                </b>
            </h3>
        </header>
        <div class="table_container">
            @if ($sort && $currentUser->can('sort'))
                <table data-behavior="sortable" data-sortable-update-url="{{ moduleRoute($moduleName, $routePrefix, 'sort') }}">
            @else
                <table>
            @endif
                @if(count($items))
                    <thead>
                        <tr>
                            @if ($sort && $currentUser->can('sort'))
                                <th class="tool"></th>
                            @endif
                            @if ($publish)
                                <th class="tool">{{ $publish_title or ''}}</th>
                            @endif
                            @resourceView($moduleName, 'before_index_headers')
                            @foreach ($toggle_columns as $toggle_column)
                                <th class="tool">{{ $toggle_column['toggle_title'] or ''}}</th>
                            @endforeach
                            @foreach ($columns as $column)
                                <th class="{{ isset($column['col']) ? 'colw-' . $column['col'] : '' }}">
                                @if(isset($column['sort']) && $column['sort'])
                                    @resourceView($moduleName, 'sort_link')
                                @else
                                    {{ $column['title'] }}</th>
                                @endif
                            @endforeach
                            @resourceView($moduleName, 'after_index_headers')
                            @if ($edit && $currentUser->can('edit'))
                                <th class="tool"></th>
                            @endif
                            @if ($preview && $currentUser->can('list'))
                                <th class="tool"></th>
                            @endif
                            @if ($delete && $currentUser->can('delete'))
                                <th class="tool"></th>
                            @endif
                        </tr>
                    </thead>
                @endif
                <tbody>
                    @forelse ($items as $item)
                        <tr data-id="{{ $item->id }}" @if(isset($sortDisabledWhen) && $item->$sortDisabledWhen) class="sortable-inactive" @endif>
                            @if ($sort && $currentUser->can('sort'))
                                @resourceView($moduleName, 'sort_action')
                            @endif
                            @if ($publish)
                                @resourceView($moduleName, 'publish_action')
                            @endif
                            @resourceView($moduleName, 'before_index_columns')
                            @foreach ($toggle_columns as $toggle_column_data)
                                @resourceView($moduleName, 'feature_action', $toggle_column_data)
                            @endforeach
                            @foreach ($columns as $column)
                                @php
                                    $columnOptions = $column;
                                @endphp
                                <td class="{{ isset($column['thumb']) && $column['thumb'] ? 'thumb' : '' }}">
                                    @if (isset($column['show_link']) && $column['show_link'] && $currentUser->can('list'))
                                        @resourceView($moduleName, 'column_with_show_link')
                                    @elseif (isset($column['edit_link']) && $column['edit_link'] && $currentUser->can('edit'))
                                        @resourceView($moduleName, 'column_with_edit_link')
                                    @else
                                        @resourceView($moduleName, 'column')
                                    @endif
                                </td>
                            @endforeach
                            @resourceView($moduleName, 'after_index_columns')
                            @if ($edit && $currentUser->can('edit'))
                                @resourceView($moduleName, 'edit_action')
                            @endif
                            @if ($preview && $currentUser->can('list'))
                                @resourceView($moduleName, 'preview_action')
                            @endif
                            @if ($delete && $currentUser->can('delete'))
                                @resourceView($moduleName, 'delete_action')
                            @endif
                        </tr>
                    @empty
                        <tr class="empty_table">
                            <td colspan="8">
                                <h2>No {{ (isset($title)) ? $title : $moduleName }}</h2>
                                @if ($create && $currentUser->can('edit'))
                                    @resourceView($moduleName, 'create_action')
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @unless ($sort || !method_exists($items, 'total'))
            @resourceView($moduleName, 'paginator')
        @endunless
    </section>
@stop

@section('footer')
    <footer id="footer">
    <ul>
        @if ($create && $currentUser->can('edit'))
            <li>
                @resourceView($moduleName, 'create_action')
            </li>
            @if(isset($parent_id) && isset($back_link))
                <li>
                    <a href="{{ $back_link }}" class="btn" title="Back">Back</a>
                </li>
            @endif
        @endif

        @if ($previewLandingUrl && $currentUser->can('list'))
            <li class="float-right">
                <a href="{{ $previewLandingUrl }}" class="btn" target="_blank" title="Back">Preview with drafts &#8599;</a>
            </li>
        @endif
    </ul>
    </footer>
@stop
