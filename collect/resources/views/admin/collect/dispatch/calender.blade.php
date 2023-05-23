@extends('admin.layouts.layout')
@section("title","ガラスリソーシング：引取管理システム")
@section("css")
<link rel="stylesheet" href="{{ URL::asset('adminpublic/css/calendar.css') }}">
@endsection
@section("js")
<link href="https://code.jquery.com/ui/1.10.4/themes/ui-lightness/jquery-ui.css" rel="stylesheet">
<script src="https://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>

<script src="{{ URL::asset('adminpublic/js/calendar.js') }}"></script>
@endsection
@section('content2')
<div class="content p-2 ">
    <form action="{{ route('a.dcalender') }}" method="POST">
        <input type="hidden" name="_token" value="{!! csrf_token() !!}">
        <div>
            <div class="d-inline-block mr-4 float-left">
                <h5 class="m-0"></h5>
            </div>
            <div class="d-inline-block ml-2 mr-4 float-left">
                <label>凡例：</label>

                @foreach($status as $s)
                <label class="mg-checkbox m-0">
                    <span class="btn-color btn" style="color: {{ $s->font_color}}; background-color: {{ $s->background_color}};">{{ $s->status_name}}</span>
                </label>
                @endforeach
            </div>
            <div class="d-inline-block ml-4 float-left position-relative">
                <label class="m-0 float-left mt-1">表示範囲：</label>
                <input name="datestart" height="22" value="{{ old('datestart',$datestart) }}" class="date datestart mt-1 form-control float-left w-7em pl-1 pr-1 datetimepicker d-inline-block" type="text"> <span class=" mt-1 float-left">～</span>
                <input name="dateend" height="22" value="{{ old('dateend',$dateend) }}" class="date dateend mt-1 datetimepicker float-left form-control w-7em pl-1 pr-1 d-inline-block" type="text">
                <button type="button" class="btn btn-primary pt-0 pb-0 ml-3 w-8em float-left btnSearch" name="btnSearch" value="1">表示更新</button>
            </div>
            <div class="clear-both"></div>
        </div>
    </form>
    <div class="mt-2 wrapper">
        @if($resources)
        <table class="table table-calendar" id='table-draggable1'>
            <thead class="table-calendar-head">
                {!! $th !!}

            </thead>
            <tbody class="tb-calendar ">
                {!! $html !!}
            </tbody>
        </table>
        @else
        対象データがありません。
        @endif
    </div>
    <a href="{{ route('a.dedit2') }}" class="route-dedit d-none"></a>
    <a href="{{ route('a.redit2') }}" class="route-redittest d-none"></a>
    <a href="{{ route('a.dupdate')  }}" class="route-dupdate d-none"></a>
    <a href="{{ route('a.dvalimoverep')  }}" class="route-dvali d-none"></a>
    <input type="hidden" name="RDStatus_CUSTOMER" value="{!! config('const.RDStatus.CUSTOMER') !!}">
    <input type="hidden" name="RDStatus_CONFIRM" value="{!! config('const.RDStatus.CONFIRM') !!}">
</div>
<ul class="rightclick">
    <li class="move">手配移動</li>
    <li class="replace">手配入替</li>
</ul>
<!-- Modal Confirm2 -->
<div class="modal fade" id="ConfirmModal2" role="dialog" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <form action="{{ route('a.dmovereplace')}}" method="POST" id="form-move-replace">
                @csrf
                <div class="modal-header modal-header-error bg-danger">
                    <h5 class="modal-title text-white"></h5>
                </div>
                <div class="modal-body">
                    <table class="w-100 modal-table">
                        <tr>
                            <td>
                            </td>
                            <td class="error-show">
                            </td>
                        </tr>
                        <tr>
                            <td class="tdleft">日付</td>
                            <td class="select-date"></td>
                        </tr>
                        <tr>
                            <td class="tdleft tdprv">移動元</td>
                            <td class="select-title"></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td class="arrow"><i class="fa fa-exchange arrowclass" aria-hidden="true"></i></td>
                        </tr>
                        <tr>
                            <td class="tdleft tdnext">移動先</td>
                            <td>
                                <select name="new_resource" class="new-resource">
                                    <option value=""></option>
                                    @foreach($resources as $r)
                                    <option value="{{$r["id"]}}">{{$r["title2"]}}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer text-center">
                    <input type="hidden" name="select_resource" class="select_resource" value="">
                    <input type="hidden" name="select_data_id" class="select-data-id" value="">
                    <input type="hidden" name="btnPopupOk" class="btnPopupOk" value="">
                    <button type="button" name="btnPopup" class="btn btn-primary btnPopup  w-10em" value="1"></button>
                    <input type="submit" name="btnPopupOk" class="btn btn-primary btnPopupOk w-10em d-none" value="1">
                    <button type="button" class="btn btn-danger close-modal w-8em" data-dismiss="modal">キャンセル</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection