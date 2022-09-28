
<div class="keyword">
    <div class="form-row mt-1 keyword-clean-item rounded py-1">
        <input type="radio" value="0" {{isset($keyword_to_clean)?'checked="true"':''}} name="selected_keyword" class="col-1 selected_keyword" style="margin-top: 0.9em">
        @if (Auth()->user()->autocomplete() !== 'false')
            {!! Form::text('name[]', (isset($keyword_to_clean)?$keyword_to_clean['name']:''), ['class' => 'form-control keyword-input typeahead name auto-keyword-clean col-8',
                                                    'placeholder' => __('... keyword ...'),
                                                    'data-provide' => 'typeahead',
                                                    'autocomplete' => 'off',
                                                    'data-autocomplete-url'=> url('keywordSearch'),
                                                    'title'=>(isset($keyword_to_clean)?'ID:&nbsp;'.$keyword_to_clean['id']:''),
                                                    'data-toggle'=>'tooltip',
                                                    'data-placement'=>'right'
                                                    ]) !!}
        @else
            {!! Form::text('name[]', isset($keyword_to_clean)?$keyword_to_clean['name']:'', ['class' => 'form-control col-8 name', 'placeholder' => __('Name')]) !!}
        @endif
        <small class="ps-message {{(isset($keyword_to_clean) && $keyword_to_clean['name_id']!=null)?'':'invisible'}}  mt-2">Ps. </small>
        {!! Form::hidden('id[]', (isset($keyword_to_clean)?$keyword_to_clean['id']:null), ['class'=>'id']) !!}
        {!! Form::hidden('translation[]', (isset($keyword_to_clean)?$keyword_to_clean['translation']:''), ['class'=>'translation']) !!}
        {!! Form::hidden('name_id[]',(isset($keyword_to_clean)?$keyword_to_clean['name_id']:null), ['class' => 'name_id'] ) !!}

        <div class="col-1 remove-placeholder"></div>
        {{Form::button('<i class="fa fa-trash" style="color:red"></i>',['class'=>'btn col-1 remove float-right', 'hidden'=>true])}}

    </div>
</div>
