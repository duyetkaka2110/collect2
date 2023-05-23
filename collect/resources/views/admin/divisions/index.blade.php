@extends('admin.layouts.layout')
@section("title","ガラスリソーシング：引取管理システム")
@section("css")
<link href="{{ URL::asset('adminpublic/css/ulist.css') }}" rel="stylesheet">
<link href="{{ URL::asset('adminpublic/css/divisions.css') }}" rel="stylesheet">
@endsection
@section("js")
<script src="{{ URL::asset('adminpublic/js/divisions.js') }}"></script>
@endsection
@section('content')
<div class="mg-content w-100">
    <form action="" class="formbtn " method="POST">
        <input type="hidden" name="_token" value="{!! csrf_token() !!}">
        <input class='page_no' name='page' value='{{ $list->currentPage()  }}' type='hidden'>
        <div class="d-inline-block float-right mg-btn">
            <div>
                <div class="form-group d-inline-block ml-2 mb-1">
                    <label class="d-block m-0" for="">取引先名称</label>
                    <label>
                        <input value="{{ old('CUSTNM') }}" class="form-control input-1" name="CUSTNM" type="text">
                    </label>
                </div>
                <div class="form-group d-inline-block ml-2 mb-1">
                    <label class="d-block m-0" for="">銘柄名称</label>
                    <label>
                        <input value="{{ old('BRNDNM') }}" class="form-control input-1" name="BRNDNM" type="text">
                    </label>
                </div>
                <button type="submit" class="btn btn-primary pt-0 pb-0 ml-3 w-8em btnSearch2 " >検索</button>
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
                        <th scope="col" class="w-3em  text-nowrap">取引先コード</th>
                        <th scope="col" class="w-5em text-nowrap">取引先名称</th>
                        <th scope="col" class="w-3em text-nowrap">銘柄コード</th>
                        <th scope="col" class="w-6em text-nowrap">銘柄名称</th>
                        <th class="w-4em text-nowrap ">原料区分</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1 ?>
                    @foreach($list as $l)
                    <?php $i++ ?>
                    <tr>
                        <td class="text-center">{{ $l->CUSTCD }}</td>
                        <td class="">{{ $l->CUSTNM }}</td>
                        <td class="text-center">{{ $l->BRNDCD }}</td>
                        <td class="">{{ $l->BRNDNM }}</td>
                        <td class="text-center">
                            <input type="hidden" name="CUSTCD[{{ $i }}]" value="{{ $l->CUSTCD }}">
                            <input type="hidden" name="BRNDCD[{{ $i }}]" value="{{ $l->BRNDCD }}">
                            <select name="material_division[{{ $i }}]" class="form-control w-100">
                                <option></option>
                                @foreach($list_enums as $e)
                                <option value="{{ $e->enum_key }}" {{ $l->material_division == $e->enum_key ? "selected" : "" }}>{{ $e->enum_value }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    @endforeach
                    @if(!@$list->total())
                    <tr>
                        <td colspan="5" class="text-center">対象データがありません。</td>
                    </tr>
                    @endif
                </tbody>
            </table>

        </div>
        <div class="mt-2 mg-pagi">
            {{ @$list->appends(request()->input())->links(); }}
        </div>
    </form>
</div>

@endsection