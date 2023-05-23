@extends('admin.layouts.layout')
@section("title","ガラスリソーシング：引取管理システム")
@section("css")
<link href="{{ URL::asset('adminpublic/css/search.css') }}" rel="stylesheet">
<style> 
</style>
@endsection
@section("js")
<script src="{{ URL::asset('js/jquery.floatThead.js') }}"></script>
<script src="{{ URL::asset('adminpublic/js/search.js') }}"></script>
@if($ErrMsg)
<script>
    $(document).ready(function() {
        $("#MessageModal .modal-body").html('{{ ($ErrMsg) }}');
        $("#MessageModal").modal();
    })
</script>
@endif
@endsection
@section('content')
<div class="mg-content w-100">
    <div class="top-table mt-4">
        <form action="{{ route('a.rsearch') }}" class="form-search" method="POST">
            <input type="hidden" name="_token" value="{!! csrf_token() !!}">
            @foreach($status as $s)
            <label class="mg-checkbox">
                <input type="checkbox" class="statusc-checkbox" {{ (!old('status') && !$btnSearch) ? 'checked': '' }} {{ in_array($s->status_id, old('status', [])) ? 'checked' : '' }} name="status[]" value="{{ $s->status_id}}" />
                <span class="btn-color btn" style="color: {{ $s->font_color}}; background-color: {{ $s->background_color}};">{{ $s->status_name}}</span>
            </label>
            @endforeach
            <div class="d-inline-block mg-select-time">
                <select class="form-control w-8em float-left mr-1 pl-1" name="DateType">
                    <option {{ old('DateType') == 'R.applied_at' ? 'selected' : '' }} value="R.applied_at">申込日</option>
                    <option {{ old('DateType') == 'R.created_at' ? 'selected' : '' }} value="R.created_at">作成日</option>
                    <option {{ old('DateType') == 'RD.hoped_on' ? 'selected' : '' }} value="RD.hoped_on">希望日</option>
                    <option {{ old('DateType') == 'RD.recovered_on' ? 'selected' : '' }} value="RD.recovered_on">回収日</option>
                </select>
                <input name="DateFrom" value="{{ old('DateFrom') }}" class="DateFrom form-control w-7em pl-1 pr-1 datetimepicker" type="text"> <span>～</span>
                <input name="DateTo" value="{{ old('DateTo') }}" class="DateTo datetimepicker form-control w-7em pl-1 pr-1" type="text">
            </div>
            <div class="d-inline-block ">
                <button type="submit" class="btn btn-primary pt-0 pb-0 ml-3 w-8em btnSearch" name="btnSearch" value="1">検索</button>
            </div>
        </form>
    </div>
    <div class="bottom-table mt-1 wrapper">
        <table class="table table-history w-100 mt-0 table-list  table-hover table_sticky">
            <thead>
                <tr>
                    <th scope="col" class="w-4em  text-nowrap">編集</th>
                    <th scope="col" class="w-5em text-nowrap">依頼No.</th>
                    <th scope="col" class="w-7em text-nowrap">申込日</th>
                    <th scope="col" class="w-9em text-nowrap">取引先</th>
                    <th scope="col" class="w-4em text-nowrap">状態</th>
                    <th scope="col" class="w-7em text-nowrap">作成日</th>
                    <th scope="col" class="w-6em text-nowrap">引取／持込</th>
                    <th scope="col" class="w-13em text-nowrap">銘柄</th>
                    <th scope="col" class="w-7em text-nowrap">希望日</th>
                    <th scope="col" class="w-7em text-nowrap">回収日</th>
                    <th scope="col" class="w-info text-nowrap">連絡事項</th>
                </tr>
            </thead>
            <tbody>
                @foreach($list as $l)
                <tr>
                    <td class="text-center"><a href="{!! route('a.redit2') !!}/{{$l->request_id}}" class="a-link" title="">編集</a></td>
                    <td class="text-center">{{ $l->request_no }}</td>
                    <td class="text-center">{{ $l->applied_at }}</td>
                    <td class="text-center">{{ $l->CUSTNM }}</td>
                    <td class="text-center">
                        <p class="btn-color btn mb-0" style="color: {{ $l->font_color }}; background-color: {{ $l->background_color }};">{{ $l->status_name }}</p>
                    </td>
                    <td class="text-center">{{ $l->created_at }}</td>
                    <td>{{ $l->RTypeNM }}</td>
                    <td>{{ $l->BRNDNM }}</td>
                    <td class="text-center">{{ $l->hoped_on }}</td>
                    <td class="text-center"><b>{{ $l->recovered_on }}</b></td>
                    <td style="width: 200px;">{{ $l->information }}</td>
                </tr>
                @endforeach
                @if(!$list->total())
                <tr>
                    <td colspan="100%" class="text-center">対象データがありません。</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    <div class="mt-1">
        {{ $list->appends(request()->input())->links(); }}
    </div>
</div>
@endsection