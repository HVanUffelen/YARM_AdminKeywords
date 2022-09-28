@extends('layouts.app')
{{App()->setLocale(Session::get('userLanguage'))}}
{{-- // TODO Lang --}}
@section('content')
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h3>@lang('Keywords Cleaning')
                {{--                todo info anps--}}
                <a title="Info" data-placement="top" data-toggle="popover"
                   data-trigger="hover"
                   data-content="@lang('Keyword Cleaning manual info...')"><i
                        class="fa fa-info-circle"
                        style="color: grey"></i></a>
                <a href="{{url('dlbt/keywords/edit/0')}}" class="btn btn-danger rounded-circle float-right"
                   data-placement="top" data-toggle="popover"
                   data-trigger="hover" data-content="@lang('Clear and refresh form')">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </a>
            </h3>
        </div>
        <div class="card-body">
            {!! Form::open(['id' => 'keywordCleaning', 'action' => '\Yarm\Adminkeywords\Http\Controllers\KeywordAdminController@update', 'method' => 'PUT', 'enctype' => 'multipart/form-data']) !!}
            @include('adminkeywords::inc.keyword_cleaning_inc')
            <div class="mt-3 pb-5">
                {{Form::button("<i class='fa-solid fa-plus'></i>", ["type"=>"button", "id"=>"btn-add-field-keyword",
                        "class"=>"float-right mr-1 btn btn-success rounded-circle",
                        "title"=>"Add a new keyword field", "data-toggle"=>"tooltip",
                "data-placement"=>"right"])}}
            </div>
            <div id="keywordCleaningErrors" class="alert alert-danger mt-3 mb-0" role="alert" hidden>
                <ul class="mb-0 pl-3">
                    <li id="selectKeywordError" hidden>@lang('Please select a keyword').</li>
                    <li id="keywordCleaningIdError" hidden>@lang('Please make sure every item is a keyword from the list or a newly created keyword').</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="mt-3 d-flex flex-wrap justify-content-between">
        {{Form::button("<i class='fa-solid fa-folder-plus'></i>  ".__('Create new keyword'),
                        ['class'=>'shadow-sm m-1 btn btn-success cleaner-add create-new-keyword', 'id'=>'btn-add-new-keyword'])}}
        {{Form::button("<i class='fa-solid fa-arrows-split-up-and-left'></i> ".__('Split keywords (;)'),
                        ['id'=>'btn-split-keyword','class'=>'m-1 btn btn-outline-success shadow-sm',
                        "title"=>"Split the keyword based on the position of the ';'",
           "data-toggle"=>"tooltip", "data-placement"=>"bottom"])}}
        {{Form::button(__('Change keywords to selected'),['class'=>'shadow-sm m-1 btn btn-primary', 'id'=>'btn-change-keyword', 'disabled'=>true])}}
        {!! Form::hidden('split-keyword', 'no', ['id' => 'split-keyword']) !!}
        {!! Form::hidden('redirect', $redirect??'', ['id' => 'redirect']) !!}
        {!! Form::close() !!}
    </div>
    @include('dlbt.add_edit.inc.keywordModal_inc')
    @include('dlbt.shared.new_person_modal')

@endsection
