@extends('admin.layouts.layout')
@section("title","ガラスリソーシング：引取管理システム")
@section("css")
<link href="{{ URL::asset('adminpublic/css/search.css') }}" rel="stylesheet">
@endsection
@section("js")
<script src="{{ URL::asset('js/js.cookie.min.js') }}"></script>
<script src="{{ URL::asset('js/jquery.floatThead.js') }}"></script>
<script src="{{ URL::asset('adminpublic/js/search.js') }}"></script>
@endsection
@section('content')
<div class="mg-content w-100">
    <div class="top-table">
        <input type="hidden" value="{{ route('a.rgetsetting') }}" name="rgetsetting" class="route-rgetsetting" />
        <form action="{{ route('a.rsearch') }}" class="form-search formbtn" method="POST">
            <input type="hidden" name="_token" value="{!! csrf_token() !!}">
            <div>
                <div class="mg-search p-2">
                    <label class="mg-search-title"><i class="fa fa-chevron-circle-up mr-2 cursor-pointer btn-icon-search" aria-hidden="true"></i>検索条件</label>
                    <table class="search_item">
                        <tr>
                            <td>
                                @foreach($status as $s)
                                <label class="mg-checkbox">
                                    <input type="checkbox" class="status-checkbox checkbox-{{$s->status_id}}" {{ (in_array($s->status_id, old('status', [])) || in_array($s->status_id, $status_first)) ? 'checked' : '' }} name="status[]" value="{{ $s->status_id}}" />
                                    <span class="btn-color btn" style="color: {{ $s->font_color}}; background-color: {{ $s->background_color}};">{{ $s->status_name}}</span>
                                </label>
                                @endforeach
                            </td>
                            <td>
                                <div class="d-inline-block">
                                    <label class="d-block m-0" for="">取引先</label>
                                    <input type="text" name="CUSTNM" value="{{ old('CUSTNM') }}" class="form-control w-8em">
                                </div>
                                <div class="d-inline-block ml-2">
                                    <label class="d-block m-0" for="">種別</label>
                                    @foreach($listRtype as $t)
                                    <label>
                                        <input value="{{  $t->enum_key }}" {{ (!old('request_type') && !$btnSearch) ? 'checked': '' }} {{ in_array( $t->enum_key, old('request_type', [])) ? 'checked' : '' }} name="request_type[]" type="checkbox">
                                        <span>{{ $t->enum_value }}</span>
                                    </label>
                                    @endforeach
                                </div>
                                <div class="d-inline-block">
                                    <label class="d-block m-0" for="">銘柄</label>
                                    <input type="text" name="BRNDNM" value="{{ old('BRNDNM') }}" class="form-control w-7em">
                                </div>
                                <div class="d-inline-block">
                                    <label class="d-block m-0" for="">物流会社</label>
                                    <input type="text" name="PDCPCDNM" value="{{ old('PDCPCDNM') }}" class="form-control w-7em">
                                </div>
                                <div class="d-inline-block">
                                    <label class="d-block m-0" for="">車両No.</label>
                                    <input type="text" name="PCARNO" value="{{ old('PCARNO') }}" class="form-control w-5em">
                                </div>
                            </td>
                            <td>
                                <div class="d-block ">
                                    <div class="d-block float-right ">
                                        <button type="submit" class="btn btn-primary pt-0 pb-0 ml-3 mb-1 w-8em btnSearch" name="BtnOk" value="search">検索</button>
                                    </div>
                                    <div class="clear-both">
                                    </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-block ">
                                    <div class="d-inline-block float-left ">
                                        @if(array_key_exists(1, $authority_type ) )
                                        <button type="button" data-value="{{ config('const.RDStatus.RECEPT') }}" class="btn only btn-primary  pt-0 pb-0 mr-2 mb-1 w-8em btnSetSatusCheckBox">受付のみ表示</button>
                                        @endif
                                        @if(array_key_exists(1, $authority_type ) || array_key_exists(2, $authority_type ) )
                                        <button type="button" data-value="{{ config('const.RDStatus.DISP') }}" class="btn only btn-primary  pt-0 pb-0 mr-2 mb-1 w-8em btnSetSatusCheckBox">配車のみ表示</button>
                                        @endif
                                        <button type="button" data-value="1" class="btn all btn-primary  pt-0 pb-0 mr-2 mb-1 w-8em btnSetSatusCheckBox">全チェック</button>
                                        <button type="button" data-value="0" class="btn all btn-primary  pt-0 pb-0 mb-1 w-8em btnSetSatusCheckBox">全クリア</button>
                                    </div>
                                    <div class="clear-both">
                                    </div>
                            </td>
                            <td>
                                <div class="d-inline-block ">
                                    <div class="d-inline-block mg-select-time2">
                                        <label class="d-block m-0" for="">日付</label>
                                        <select class="form-control w-6em  mr-1 pl-1" name="DateType">
                                            <option {{ old('DateType') == 'R.applied_at' ? 'selected' : '' }} value="R.applied_at">申込日</option>
                                            <option {{ old('DateType') == 'R.created_at' ? 'selected' : '' }} value="R.created_at">作成日</option>
                                            <option {{ old('DateType') == 'RD.hoped_on' ? 'selected' : '' }} value="RD.hoped_on">希望日</option>
                                            <option {{ old('DateType') == 'RD.recovered_on' ? 'selected' : '' }} value="RD.recovered_on">回収日</option>
                                        </select>
                                        <input name="DateFrom" value="{{ old('DateFrom') }}" class="DateFrom form-control w-6em pl-1 pr-1 datetimepicker" type="text"> <span>～</span>
                                        <input name="DateTo" value="{{ old('DateTo') }}" class="DateTo datetimepicker form-control w-6em pl-1 pr-1" type="text">
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-block ">
                                    <div class="d-block float-right ">
                                        <button type="submit" class="btn btn-primary pt-0 pb-0 ml-3 mb-1 w-8em " name="BtnOk" value="export">ファイル出力</button>
                                    </div>
                                    <div class="clear-both">
                                    </div>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="d-block ">
                    <div class="d-block float-right ">
                        <div class="text-right mb-2 mt-2">
                            <button type="button" class="btn btn-primary pt-0 pb-0 w-10em btnSetting mr-2" data-toggle="modal" data-target="#SettingModal">表示設定変更</button>
                            <a type="button" class="btn btn-primary pt-0 pb-0 w-10em btnAdd" href="{{ route('a.rcreate',[0]) }}">新規依頼追加</a>
                        </div>
                    </div>
                    <div class="clear-both">
                    </div>
                </div>

            </div>
        </form>
    </div>
    <div class="bottom-table mt-1 wrapper">
        <table class="table table-history w-100 mt-0 table-list  table-hover table_sticky">
            <colgroup>
                @foreach($header as $key=>$th)
                <col class="" style="{{ ($user_setting && array_key_exists($key, $user_setting->hidden_item ))  ? 'visibility: collapse;' : '' }}" />
                @endforeach
            </colgroup>
            <thead>
                <tr>
                    @foreach($header as $th)
                    <th scope="col" class="{{ $th['class'] }}">{{ $th['name'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($list as $l)
                <tr class='{{ ($l->status_id == config("const.RDStatus.CONFIRM") || $l->status_id == config("const.RDStatus.COMP")) ? "tr-done" : ""  }}'>
                    <td class="text-center">
                        <a href="{!! $l->request_no ? route('a.redit',[$l->request_id]) : route('a.rcreate',[$l->request_id]) !!}" class="a-link" title="編集">
                            <i class="fa fa-pencil" aria-hidden="true"></i>
                        </a>
                    </td>
                    <td class="text-center">{{ $l->request_no }}</td>
                    <td class="text-center">{{ $l->applied_at }}</td>
                    <td class="">{!! $l->CFU !!}</td>
                    <td class="text-center">
                        <p class="btn-color btn mb-0" style="color: {{ $l->font_color }}; background-color: {{ $l->background_color }};">{{ $l->status_name }}</p>
                    </td>
                    <td class="">{{ $l->RTypeNM }}</td>
                    <td class="text-">{{ $l->BRNDNM }}</td>
                    <td class="text-center">{{ $l->hoped_on }}</td>
                    <td class="text-center recovered_on"><b>{{ $l->recovered_on }}</b></td>
                    <td class="">{{ $l->PDCPCDNM }}</td>
                    <td>{{ $l->car_typeNM }}</td>
                    <td><span style="{{ $l->PCARNO_bg ? 'color: red' : '' }}">{{ $l->PCARNO }}</span></td>
                    <td class="text-center"><span style="{{ $l->PCARNO_bg ? 'color: red' : '' }}">{!! $l->DVSTEL !!}</span></td>
                    <td class="white-space-inherit" style="">{{ $l->information }}</td>
                    <td class="text-center">{!! $l->receptionNM !!}</td>
                    <td class="text-center">{!! $l->dispatchedNM !!}</td>
                    <td class="text-center">{!! $l->confirmedNM !!}</td>
                    <td class="text-center">{!! $l->acceptancedNM !!}</td>
                    <td class="text-center">{!! @$l->locked_user !!}</td>
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


<!-- 表示設定変更ダイアログ -->
<div class="modal fade" id="SettingModal" role="dialog" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        <!-- Modal content-->
        <form action="{{ route('a.rsetting') }}" method="get" class="user-setting">
            <div class="modal-content">
                <div class="modal-header modal-header-error bg-danger">
                    <h5 class="modal-title text-white">表示設定変更</h5>
                </div>
                <div class="modal-body">
                    <table class="pl-4">
                        <tr>
                            <td><label class="p-2">表示行数</label></td>
                            <td><input name="line_no" class="form-control" type="number" min="1" max="999" value="" /></td>
                        </tr>
                        <tr>
                            <td class="align-top"><label class="pl-2 pr-4">列の表示／非表示</label></td>
                            <td>
                                @foreach($header as $key=>$th)
                                <div>
                                    <label>
                                        <input type="checkbox" name="key[]" class="form-control checbox-custom"  name="setting" value="{{ $key }}" {{ $th["disabled"] }} />
                                        @if($key == "edit")
                                        <input type="hidden" name="key[]" value="{{ $key }}" />
                                        @endif
                                        <span>{{ $th["name"] }}</span>
                                    </label>
                                </div>
                                @endforeach
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer text-center">
                    <button type="submit" class="btn btn-primary  w-8em" data-btn="1" name="btnOk" value="">保存</button>
                    <button type="button" class="btn btn-danger close-modal w-8em" data-dismiss="modal">キャンセル</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection