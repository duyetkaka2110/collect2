@extends('admin.layouts.layout')
@section("title","ガラスリソーシング：引取管理システム")
@section("css")
<link href="{{ URL::asset('adminpublic/css/rq-edit.css') }}" rel="stylesheet">
<style>
    .mg-content {
        padding-left: inherit;
    }

    .menu-list.title-header {
        padding-left: inherit;
        top: -2px;
    }

    .mg-content {
        margin-top: -20px;
    }

    .menu-list {
        top: -8px;
    }
</style>
@endsection
@section("js")
<script>
    listCarWherePDCPCDList = <?php echo json_encode(array_flip(config("const.CarWherePDCPCDList"))) ?>;
</script>
<script src="{{ URL::asset('js/js.cookie.min.js') }}"></script>
<script src="{{ URL::asset('adminpublic/js/rq-edit2.js') }}"></script>
@endsection
@section('content')
<div class="mg-content w-100">
    <input type="hidden" name="redit" value="{{ route('a.redit',[@$data->request_id]) }}" />
    <input type="hidden" name="redit_store" value="{{ route('a.redit_store') }}" />
    <input type="hidden" name="redit_ajax" value="{{ route('a.redit_ajax') }}" />
    <input type="hidden" name="rlockuser" value="{{ route('a.rlockuser') }}" />
    <form action="" class="mg-form {{ $active_user_id }} " method="POST">
        <input type="hidden" name="_token" value="{!! csrf_token() !!}">
        <input type="hidden" name="request_id" value="{{ @$data->request_id}}">
        <input type="hidden" name="TEMP" value="{{ config('const.RDStatus.TEMP') }}">
        <input type="hidden" name="RECEPT" value="{{ config('const.RDStatus.RECEPT') }}">
        <input type="hidden" value="" name="btnAjax" class="btnAjax d-none">
        <input type="hidden" value="" name="btnAjaxRow" class="btnAjaxRow">
        <div class="d-inline-block float-right mg-btn">
            @if(array_key_exists(1, $authority_type ) || array_key_exists(2, $authority_type ))
            <button type="button" class="btn btn-primary pt-0 pb-0 ml-3 w-8em btnTemp btnlock new temp" name="btnOk" value="save">保存</button>
            @endif
            @if(array_key_exists(1, $authority_type ) && @$data->request_type == 0)
            <button type="button" class="btn btn-primary pt-0 pb-0 ml-3 w-10em  new temp btnlock btnExport" name="btnOk" value="export">引取依頼書出力</button>
            <button type="button" class="d-none btnExportOK btnlock" name="btnOk" value="exportOk"></button>
            @endif
            <button type="button" href="{{ route('a.rsearch') }}" class="btn btn-primary pt-0 pb-0 ml-3 w-8em btnBack " name="" value="">戻る</button>
        </div>
        <div class="top-table mt-2">
            <table class="w-100 ">
                <tr>
                    <td class="td-1"></td>
                    <td class="td-2 mg-errors ">
                    </td>
                </tr>
                <tr>
                    <td class="td-1">お取引先名</td>
                    <td class="td-2">
                        <span>{{ @$data->CUSTNM}}</span>
                        <input type="hidden" name="CUSTCD" value="{{ @$data->CUSTCD}}">
                    </td>
                </tr>
                <tr>
                    <td class="td-1">依頼No.</td>
                    <td class="td-2"><input type="text" readonly name="request_no" value="{{ @$data->request_no}}" class="form-control input-1"></td>
                </tr>
                <tr>
                    <td class="td-1">申込日</td>
                    <td class="td-2"><input type="text" readonly name="applied_at" value="{{ @$data->applied_at2}}" class="form-control input-1"></td>
                </tr>
                <tr>
                    <td class="td-1">種別</td>
                    <td class="td-2">
                        <input name="request_type" value="{{ @$data->request_type }}" type="hidden">
                        <input class="form-control input-1"  value="{{ $data->request_typeNM }}" readonly>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">依頼内容</td>
                </tr>
                <tr>
                    <td colspan="2" class="mg-tb-detail">
                        @include("admin.collect.request.table")
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        @if(array_key_exists(1, $authority_type ))
                        <label class="cursor-pointer btnAdd"><i class="fa fa-plus-circle" aria-hidden="true"></i><span class="pl-1">行を追加する</span></label>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="td-1">依頼備考</td>
                    <td class="td-2"><textarea class="form-control request_note" {{ array_key_exists(1, $authority_type ) ? "" : "readonly"}} name="request_note" maxlength="1024">{{ old('request_note',@$data->request_note)  }}</textarea></td>
                </tr>
                <tr>
                    <td class="td-1">ご担当者氏名</td>
                    <td class="td-2"><input type="text" maxlength="256" name="manager_name" readonly value="{{ @$data->applied_user ? @$data->manager_name : @$user->user_name  }}" class="form-control input-1"></td>
                </tr>
                <tr>
                    <td class="td-1">ご連絡方法</td>
                    <td class="td-2">
                        @if(@$data->applied_user ? @$data->contact_type : @$user->contact_type == 1)
                        <div>
                            <label class=" w-9em"><span class="pl-1">メールでご連絡</span></label>
                            <input type="email" name="mail_address" readonly value="{{ @$data->applied_user ? @$data->mail_address : @$user->mail_address }}" maxlength="256" class="form-control input-1 contact_type_1 contact_type">
                        </div>
                        <div>
                            <label class=" w-9em"><span class="pl-1">CCメールアドレス</span></label>
                            <input type="email" name="ccmail_address" readonly value="{{ @$data->applied_user ? @$data->ccmail_address : @$user->ccmail_address }}" maxlength="256" class="form-control input-1 contact_type_1 contact_type">
                        </div>
                        @else
                        <div class="mb-1">
                            <label class=" w-9em"><span class="pl-1 w-8em">電話でご連絡</span></label>
                            <input type="text" name="tel_no" maxlength="32" readonly value="{{ @$data->applied_user ?  @$data->tel_no :  @$user->tel_no }}" class="form-control input-1 contact_type_0 contact_type">
                        </div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </form>
    <a class="d-none route-getListSmst" href="{{ route('a.getListSmst') }}"></a>
    <a class="d-none route-getListCarTypeDetail" href="{{ route('a.getListCarTypeDetail') }}"></a>
</div>
<style>
    /* 回収日を太字にする */
    .recovered_on {
        font-weight: bold;
    }

    .mg-errors-popup {
        color: red;
    }

    .mg-body {
        max-width: inherit;
    }

    .mg-tb-detail {
        position: relative;
    }

    .tb-detail {
        position: absolute;
        top: 0;
        overflow: auto;
    }

    .car_detail,
    .information {
        min-width: 100px;
    }

    .car_type {
        min-width: 60px;
    }

    .ui-datepicker {
        z-index: 99999999999999;
    }

    @media screen and (max-width: 1400px) {
        .mg-tb-detail {
            overflow-x: auto;
        }
    }
</style>
@if(Session::has('ExportMsg'))
<script>
    $(document).ready(function() {
        $("#ExportModal").modal();
    });
</script>
<!-- 引取依頼書出力のポップアップ -->
<div class="modal fade" id="ExportModal" role="dialog" data-backdrop="static" tabindex="-1">
    <form action="{{ route('a.redit_export') }}" class="" method="POST">
        <input type="hidden" name="_token" value="{!! csrf_token() !!}">
        <input name="request_id" value="{{ @$id }}" type="hidden">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header modal-header-error bg-danger">
                    <h5 class="modal-title text-white">引取依頼書出力</h5>
                </div>
                <div class="modal-body">
                    <p class="color-red mg-errors-popup"></p>
                    <p class="m-0">以下の物流会社の引取依頼書を出力します。</p>
                    <p class="m-0">宜しいですか？</p>
                    <div class="form-group row col-sm-10 mt-4">
                        <label for="staticEmail" class="col-sm-4 col-form-label">物流会社</label>
                        <div class="col-sm-8">
                            <select class="form-control PDCPCDEExport" name="PDCPCDEExport">
                                <option value=""></option>
                                @foreach($listPDCPCDExport as $p)
                                <option value="{{ $p->CUSTCD }}">{{ $p->CUSTNM  }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer text-center">
                    <button type="button" class="btn btn-primary btnPopupExport w-10em">依頼書を出力</button>
                    <button type="button" class="btn btn-danger close-modal w-10em" data-dismiss="modal">キャンセル</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endif
@endsection