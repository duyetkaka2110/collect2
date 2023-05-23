@extends('admin.layouts.layout')
@section("title","ガラスリソーシング：引取管理システム")
@section("css")
<link rel="stylesheet" href="{{ URL::asset('adminpublic/css/edit.css') }}">
@endsection
@section("js")
<link href="https://code.jquery.com/ui/1.10.4/themes/ui-lightness/jquery-ui.css" rel="stylesheet">
<script src="https://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
<script src="{{ URL::asset('adminpublic/js/edit.js') }}"></script>
@endsection
@section('content')
<div class="content">
    <form action="{{ route('a.dedit',['id'=>@$idrq]) }}" class="form {{ @$RDdone }}" method="POST">
        <h5 class="d-inline-block title-header">{{ @$title_header }}</h5>
        <input type="hidden" name="_token" value="{!! csrf_token() !!}">
        <div class="d-inline-block float-right mg-btn">
            <div>
                @if(array_key_exists(1, $authority_type ) || array_key_exists(2, $authority_type ))
                <a href="{{ route('a.dcalender') }}" class="btn btn-primary pt-0 pb-0 ml-3 w-8em color-white float-right btnBack">戻る</a>
                <button type="button" class="btn btn-primary pt-0 pb-0 ml-3 w-8em btnOk float-right" name="btnOk" value="保存">保存</button>
                <button type="button" class="btn btn-primary pt-0 pb-0 ml-3 w-8em btnCancel float-right" name="btnCancel" value="キャンセル">キャンセル</button>
                @else
                <a href="{{ route('a.dcalender') }}" class="btn btn-primary pt-0 pb-0 ml-3 w-8em color-white float-right ">戻る</a>
                @endif
            </div>
        </div>
        <div class="mt-3">
            <label class="mr-4 mb-0">{{ @$dispatch["dispatched_on"] }}</label>
            <label class="ml-3 mb-0">{{ @$dispatch["title"] }}</label>
        </div>
        <div class="mg-container">
            <table class="w-100 table-custom {{ (array_key_exists(1, $authority_type ) || array_key_exists(2, $authority_type )) ? 'connectedSortable' : '' }}">
                <tr>
                    <td colspan="3" class="text-right">
                        <label class="mt-2 cursor-pointer mb-0">
                            @if(array_key_exists(1, $authority_type ) || array_key_exists(2, $authority_type ))
                            <input class="" type="checkbox" name="is_confirmed" {{ @$dispatch['is_confirmed'] ? 'checked':'' }} value="1" />
                            @else
                            <input class="" type="checkbox" disabled name="" {{ @$dispatch['is_confirmed'] ? 'checked':'' }} value="1" />
                            <input class="" type="hidden" name="is_confirmed" {{ @$dispatch['is_confirmed'] ? 'checked':'' }} value="{{ @$dispatch['is_confirmed'] }}" />
                            @endif
                            <span class="pl-1">確定する</span>
                        </label>
                    </td>
                </tr>
                @if($dispatch_detail->toArray())
                @foreach($dispatch_detail as $k => $dd)
                <tr class="tr-input">
                    <td class="text-center">
                        <i class="fa fa-th-list cursor-move font-17px pr-1" aria-hidden="true"></i>
                    </td>
                    <td colspan="2">
                        <input type="hidden" value="{{@$dd->dispatch_detail_id}}" name="dispatch_detail_id[old{{$k}}]" />
                        <input type="hidden" value="{{@$dd->sort_no}}" class="sort_no" name="sort_no[old{{$k}}]" />
                        <input name="instruct[old{{$k}}]" value="{{ @$dd->instruct }}" {{ (array_key_exists(1, $authority_type ) || array_key_exists(2, $authority_type )) ? '' : 'readonly' }} maxlength="1024" type="text" class="instruct form-control input-custom" />
                    </td>
                    <td class="text-center">
                        @if($dd->request_detail_id)
                        <i class="fa fa-truck cursor-pointer font-20px" aria-hidden="true"></i>
                        @else
                        @if(array_key_exists(1, $authority_type ) || array_key_exists(2, $authority_type ))
                        <i class="fa fa-trash cursor-pointer font-17px btnDel" title="削除" aria-hidden="true"></i>
                        @endif
                        @endif
                    </td>
                </tr>
                @endforeach
                @else
                <tr class="tr-input">
                    <td class="text-center">
                        <i class="fa fa-th-list cursor-move font-17px pr-1" aria-hidden="true"></i>
                    </td>
                    <td colspan="2">
                        <input name="instruct[]" value="" {{ (array_key_exists(1, $authority_type ) || array_key_exists(2, $authority_type )) ? '' : 'readonly' }} maxlength="1024" type="text" class="instruct form-control input-custom" />
                        <input type="hidden" value="0" name="dispatch_detail_id[]" />
                        <input type="hidden" value="0" class="sort_no" name="sort_no[]" />
                    </td>
                    <td class="text-center"><i class="fa fa-truck cursor-pointer font-20px" aria-hidden="true"></i></td>
                </tr>
                @endif
                <tr>
                    <td></td>
                    <td colspan="2">
                        @if(array_key_exists(1, $authority_type ) || array_key_exists(2, $authority_type ))
                        <label class="cursor-pointer btnAdd mt-2"><i class="fa fa-plus-circle" aria-hidden="true"></i><span class="pl-1">行を追加する</span></label>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        <div class="mt-2">

        </div>
    </form>
</div>
@endsection