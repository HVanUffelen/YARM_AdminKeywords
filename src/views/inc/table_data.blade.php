{{App()->setLocale(Session::get('userLanguage'))}}
@if (isset($keywords))
    <div class="pagination">
        {!! $keywords->links() !!}
    </div>
@endif
<table class="table">
    <thead>
    <tr>
        <th>Keyword</th>
        <th class="text-center">Used in # records</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    @foreach($keywords as $keyword)
        <tr>
            <td>{{ $keyword->name.(($keyword->name_id!=null)?' (Ps)':'')  }}</td>
            <td class="text-center">{{ $keyword->refs()->count() }}</td>
            <td>
                <form action="/dlbt/keywords/delete/{{ $keyword->id }}" method="post">
                    @method('delete')
                    @csrf
                    <div class="btn-group btn-group-sm">
                        <a href="/dlbt/keywords/edit/{{ $keyword->id.'&p'.$keywords->currentPage()}}" class="btn btn-outline-success border-0"
                           data-toggle="tooltip"
                           title="Clean {{ $keyword->name }}">
                            <i class="fa-solid fa-brush"></i>
                        </a>
                        <button type="button" data-id="{{$keyword->id}}" data-redirect="{{$keywords->currentPage()}}" id="edit-keyword"
                                class="btn btn-outline-secondary border-0"
                                data-toggle="tooltip"
                                title="Edit {{ $keyword->name }}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="delete-keyword btn btn-outline-danger border-0"
                                data-keyword="{{$keyword->name}}"
                                data-toggle="tooltip"
                                title="Delete {{ $keyword->name }}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                        {!! Form::hidden('page', $keywords->currentPage(), ['id' => 'page']) !!}
                    </div>
                </form>
            </td>
        </tr>
    @endforeach
    {{--    todo lang--}}
    {{$keywords->count()==0? __('0 result found')  :''}}
    </tbody>
</table>
@include('dlbt.add_edit.inc.keywordModal_inc')


