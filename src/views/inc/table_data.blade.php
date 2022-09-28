@extends('layouts.app')

{{App()->setLocale(Session::get('userLanguage'))}}

@section('content')
    <div class="container">
        <h2>Keywords</h2>
        @include('dlbt.shared.filter_box_inc')
        {{--        Todo --}}
        {{-- new keyword--}}
        <div class="table-responsive adminKeywordContent">
            @include('dlbt.keyword.inc.table_data')
        </div>

    </div>
    <input type="hidden" name="type" id="type" value="keywords_crud"/>
    <input type="hidden" name="view" id="view" value="keywords"/>
    <input type="hidden" name="hidden_column_name" id="hidden_column_name" value="name"/>
    <input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="asc"/>

@endsection
