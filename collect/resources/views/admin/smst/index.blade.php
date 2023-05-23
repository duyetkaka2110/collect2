@extends('admin.layouts.layout')
@section("title","ガラスリソーシング：引取管理システム")
@section("css")
<link href="{{ URL::asset('adminpublic/css/ulist.css') }}" rel="stylesheet">
<link href="{{ URL::asset('adminpublic/css/divisions.css') }}" rel="stylesheet">
<style>
    .table td select {
        min-width: 120px;
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
                    <label class="d-block m-0" for="">取引先名称</label>
                    <label>
                        <input value="{{ old('CUSTNM') }}" class="form-control input-1" name="CUSTNM" type="text">
                    </label>
                </div>
                <div class="form-group d-inline-block ml-2 mb-1">
                    <label class="d-block m-0" for="">物流会社名称</label>
                    <label>
                        <input value="{{ old('PDCPNM') }}" class="form-control input-1" name="PDCPNM" type="text">
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
                <button type="submit" class="btn btn-primary pt-0 pb-0 ml-3 w-8em btnSearch d-none" name="btnSearch" value="search">検索</button>
            </div>
            <div class="text-right mb-1">
                <button type="button" class="btn btn-primary pt-0 pb-0 ml-3 w-8em  btnOk" name="btnOk" value="save">保存</button>
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
                    <?php $i = 0 ?>
                    @if($list)
                    @foreach($list as $l)
                    <?php $i++ ?>
                    <tr>
                        @foreach($format as $k=>$f)
                        @if($k == 'car_type')
                        <td class="{{ $f['align'] }}">
                            <select name="car_type[{{ $i }}]" class="form-control w-100 car_type">
                                <option></option>
                                @foreach($list_enums as $e)
                                <option value="{{ $e->enum_key }}" {{ $l->car_type == $e->enum_key ? "selected" : "" }}>{{ $e->enum_value }}</option>
                                @endforeach
                            </select>
                        </td>
                        @else
                        <td class="{{ $f['align'] }}">
                            {!! @$l->$k !!}

                            @if(isset($f['key']) && $f['key'])
                            <input type="hidden" name="{{ @$k }}[{{ $i }}]" value="{{ @$l->$k }}">
                            @endif
                        </td>
                        @endif
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