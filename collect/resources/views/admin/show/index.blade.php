@extends('admin.layouts.layout')
@section("title","ガラスリソーシング：引取管理システム")
@section("css")
<link href="{{ URL::asset('adminpublic/css/ulist.css') }}" rel="stylesheet">
<link href="{{ URL::asset('adminpublic/css/divisions.css') }}" rel="stylesheet">
<style>
    .table td select {
        min-width: 120px;
    }

    .wrapper {
        max-height: calc(100vh - 185px);
    }
</style>
@endsection
@section("js")
<script src="{{ URL::asset('adminpublic/js/divisions.js') }}"></script>
<script>
    $(document).ready(function() {
        setDateTimePicker();
    })
</script>
@endsection
@section('content')
<div class="mg-content w-100">
    <form action="" class="formbtn " method="POST">
        <input type="hidden" name="_token" value="{!! csrf_token() !!}">
        <input class='page_no' name='page' value='{{ $list ? $list->currentPage() : ""  }}' type='hidden'>
        <div class="d-inline-block float-right mg-btn">
            <div>
                <div class="form-group d-inline-block ml-2 mb-1">
                    <label class="d-block m-0" for="">{{ isset($searchkey) ? $searchkey['name'] : '取引先名称'  }}</label>
                    <label>
                        <input value="{{ isset($searchkey) ? old($searchkey['key']) : old('CUSTNM') }}" class="form-control input-1" name="{{ isset($searchkey) ? $searchkey['key'] : 'CUSTNM'  }}" type="text">
                    </label>
                </div>
                <div class="form-group d-inline-block ml-2 mb-1">
                    <label class="d-block m-0" for="">有効期間</label>
                    <label>
                        <input value="{{ old('SRTDAY') }}" class="form-control w-8em datetimepicker" name="SRTDAY" type="text">
                        <span>～</span>
                        <input value="{{ old('ENDDAY') }}" class="form-control  w-8em datetimepicker" name="ENDDAY" type="text">
                    </label>
                </div>
                <button type="submit" class="btn btn-primary pt-0 pb-0 ml-3 w-8em btnSearch2 ">検索</button>
                <button type="submit" class="btnSearch d-none" name="btnSearch" value="search"></button>
            </div>
        </div>
        <div class="clear-both"></div>
        <div class="top-table mt-2 wrapper">
            <table class="table table-history w-100 mt-0 table-list  table-hover table_sticky">
                <thead>
                    <tr>
                        @if($format)
                        @foreach($format as $f)
                        <th scope="col" class="{{$f['width']}}">{!! $f['name'] !!}</th>
                        @endforeach
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @if($list)
                    @foreach($list as $l)
                    <tr>
                        @foreach($format as $k=>$f)
                        <td class="{{ $f['align'] }}">
                            {!! @$l->$k !!}
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                    @endif
                    @if($list ? !$list->total(): 1 )
                    <tr>
                        <td colspan="100%" class="text-center">対象データがありません。</td>
                    </tr>
                    @endif
                </tbody>
            </table>

        </div>
        <div class="mt-2 mg-pagi">
            {{ $list ? $list->links() : ''   }}
        </div>
    </form>
</div>

@endsection