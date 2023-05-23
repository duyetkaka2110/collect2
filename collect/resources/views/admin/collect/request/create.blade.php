@extends('admin.layouts.layout')
@section("title","ガラスリソーシング：引取管理システム")
@section("css")
<link href="{{ URL::asset('requestpublic/css/detail.css') }}" rel="stylesheet">
<link href="{{ URL::asset('adminpublic/css/rcreate.css') }}" rel="stylesheet">
@endsection
@section("js")
<script src="{{ URL::asset('adminpublic/js/rcreate.js') }}"></script>
@endsection
@section('content')
<div class="mg-content float-right">
    <form action="{{ route('a.rcreatestore') }}" class="formbtn {{ @$classtitle }}" method="POST">
        <input type="hidden" name="_token" value="{!! csrf_token() !!}">
        <input type="hidden" name="request_id" value="{{ @$data->request_id}}">
        <input type="hidden" name="request_detail_id" value="">
        <input type="hidden" name="recovered_on" value="">
        <h5 class="d-inline-block title-header">{{ @$title_header }}</h5>
        <div class="d-inline-block float-right mg-btn">
            @if(array_key_exists(1, $authority_type ))
            @if($data ? !$data["request_no"] : 1)
            <button type="button" class="btn btn-primary pt-0 pb-0 ml-3 w-8em btnTemp" name="btnOk" value="btnTemp">一時保存</button>
            @endif
            @if($data ? !$data["applied_at"] : 1)
            <button type="button" class="btn btn-primary pt-0 pb-0 ml-3 w-8em btnApp" name="btnOk" value="btnAppOk">依頼申込</button>
            @endif
            @endif
            <button type="button" class="btn btn-primary pt-0 pb-0 ml-3 w-8em btnCancel" name="" value="1">キャンセル</button>
            @if(array_key_exists(1, $authority_type ))
            @if($data ? (!$data["applied_at"] && !$data["request_no"]) : 0)
            <button type="button" class="btn btn-primary pt-0 pb-0 ml-3 w-8em btnDelele " name="btnOk" value="">削除</button>
            @endif
            @endif
            <button type="submit" class="d-none btnDelelesubmit" name="btnOk" value="btnDelele"></button>
            <button type="submit" class="d-none btnConfSubmit" name="btnOk" value="btnConfirme"></button>
        </div>
        <div class="top-table mt-2">
            <div class="mg-errors "></div>
            <table class="w-100 m-w-1000">
                <tr>
                    <td class="td-1">取引先選択<span class="note">（※）</td>
                    <td class="td-2">
                        <input class="form-control input-1 CUSTCD_UserID" name="CUSTCD_UserID" value="{{ @$data->nameview }}" autocomplete="on" list="CUSTCD_UserID">
                        <datalist id="CUSTCD_UserID">
                            @foreach($listCUSTNM as $p)
                            <option data-value="{{ $p->CUSTCD_UserID  }}" data-label="{{ $p->nameview  }}">{{ $p->nameview  }}</option>
                            @endforeach
                        </datalist>
                        <input type="hidden" name="CUSTCD" class="CUSTCD" value="{{@$data->applied_user ? @$data->CUSTCD : ''}}">
                        <input type="hidden" name="user_id" class="user_id" value="{{@$data->applied_user ? @$data->user_id : ''}}">
                    </td>
                </tr>
                <tr>
                    <td class="td-1">お取引先名</td>
                    <td class="td-2">
                        <span class="CUSTNMtxt">{{ @$data->CUSTNM}}</span>
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
                    <td class="td-1">引取／持込<span class="note">（※）</span></td>
                    <td class="td-2">
                        <select name="request_type" class="form-control input-1">
                            @foreach($listrequesttype as $p)
                            <option value="{{ $p->enum_key  }}" {{ old("request_type",@$data->request_type) == $p->enum_key ? "selected": "" }}>{{ $p->enum_value  }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="td-1">依頼内容</td>
                    <td class="td-2">
                        <table class="table w-100 mt-1 mb-1 tb-detail">
                            <thead>
                                <tr>
                                    <th class="w-7em text-nowrap">銘柄<span class="note">（※）</span></th>
                                    <th class="w-8em text-nowrap">希望日</span></th>
                                    <th class="w-8em text-nowrap">希望日なし</th>
                                    <th class="w-8em text-nowrap">回収日</th>
                                    <th class="w-4em text-nowrap">状態</th>
                                    <th class="w-8em text-nowrap">連絡事項</th>
                                    <th class="w-3em text-nowrap">行複写</th>
                                    <th class="w-3em text-nowrap">削除</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="d-none tr-default">
                                    <td>
                                        <select name="BRNDCD[]" class="form-control input-1 BRNDCD">
                                            <option value=""></option>
                                            @if($listproducts)
                                            @foreach($listproducts as $p)
                                            <option value="{{ $p->BRNDCD }}">{{ $p->BRNDNM  }}</option>
                                            @endforeach
                                            @endif
                                        </select>
                                    </td>
                                    <td class="text-center position-relative"> <input name="hoped_on[]" value="" class="hoped_on text-center datenotsun form-control w-7em pl-1 pr-1" type="text" autocomplete="off"></td>
                                    <td class="text-center position-relative">
                                        <input type="hidden" name="is_nohoped[]" value="0">
                                        <input type="checkbox" name="chk_nohoped[]" value="1" class="is_nohoped">
                                    </td>
                                    <td class="text-center position-relative"> </td>
                                    <td class="text-center">
                                    </td>
                                    <td><textarea class="form-control information" name="information[]" maxlength="1024"></textarea></td>
                                    <td class="text-center"><i class="fa fa-copy cursor-pointer btnCopy" title="行複写" aria-hidden="true"></i></td>
                                    <td class="text-center"><i class="fa fa-trash cursor-pointer btnDelete" title="削除" aria-hidden="true"></i></td>
                                </tr>
                                @if(old("BRNDCD"))
                                @foreach(old("BRNDCD") as $k=>$c)
                                <tr>
                                    <td>
                                        @if($classtitle != "form-show")
                                        <select name="BRNDCD[]" class="form-control input-1 BRNDCD">
                                            <option value=""></option>
                                        </select>
                                        @else
                                        <input type="hidden" name="BRNDCD[]" value="{{ old('BRNDCD.'.$k) }}">
                                        <input type="text" readonly name="BRNDNM[]" value="{{ old('BRNDNM.'.$k) }}" class="form-control input-1">
                                        @endif
                                    </td>
                                    <td class="text-center position-relative"> <input name="hoped_on[]" value="{{ old('hoped_on.'.$k) }}" {{ old('is_nohoped.'.$k) == 1 ? "readonly": "" }} class="hoped_on text-center datenotsun form-control w-7em pl-1 pr-1" type="text" autocomplete="off"></td>
                                    <td class="text-center position-relative">
                                        <input type="hidden" name="is_nohoped[]" value="{{ old('is_nohoped.'.$k) }}">
                                        <input type="checkbox" name="chk_nohoped[]" {{ old('is_nohoped.'.$k) == 1 ? "checked": "" }} value="1" class="is_nohoped">
                                    </td>
                                    <td class="text-center position-relative"> </td>
                                    <td class="text-center">
                                        <p class="btn-color btn mb-0" style="color: #fff; background-color: #ccc;"></p>
                                    </td>
                                    <td><textarea class="form-control information" name="information[]" maxlength="1024"></textarea></td>
                                    <td class="text-center"><i class="fa fa-copy cursor-pointer btnCopy" title="行複写" aria-hidden="true"></i></td>
                                    <td class="text-center"><i class="fa fa-trash cursor-pointer btnDelete" title="削除" aria-hidden="true"></i></td>
                                </tr>
                                @endforeach
                                @endif
                                @if($dataRD)
                                @foreach($dataRD as $key => $rd)
                                <tr>
                                    <td>
                                        @if($classtitle != "form-show")
                                        <select name="BRNDCD[]" class="form-control input-1 BRNDCD">
                                            <option value=""></option>
                                            @if($listproducts)
                                            @foreach($listproducts as $p)
                                            <option value="{{ $p->BRNDCD }}" {{ $rd->BRNDCD == $p->BRNDCD ? "selected": "" }}>{{ $p->BRNDNM  }}</option>
                                            @endforeach
                                            @endif
                                        </select>
                                        @else
                                        <input type="hidden" name="BRNDCD[]" value="{{ @$rd->BRNDCD }}">
                                        <input type="text" readonly name="BRNDNM[]" value="{{ @$rd->BRNDNM }}" class="form-control input-1">
                                        @endif
                                    </td>
                                    <td class="text-center position-relative"> <input name="hoped_on[]" value="{{ $rd->hoped_on  }}" {{ $rd->is_nohoped == 1 ? "readonly": "" }} class="hoped_on text-center datenotsun form-control w-7em pl-1 pr-1" type="text" autocomplete="off"></td>
                                    <td class="text-center position-relative">
                                        <input type="hidden" name="is_nohoped[]" value="{{ $rd->is_nohoped }}">
                                        <input type="checkbox" name="chk_nohoped[]" {{ $rd->is_nohoped == 1 ? "checked": "" }} value="1" class="is_nohoped">
                                    </td>
                                    <td class="text-center position-relative">
                                        <b>{{ $rd->recovered_on }}</b>
                                        <input type="hidden" name="recovered_on[]" value="{{ $rd->recovered_on }}">
                                    </td>
                                    <td class="text-center">
                                        <p class="btn-color btn mb-0" style="color: {{ $rd->font_color  }}; background-color: {{ $rd->background_color  }};">{{ $rd->status_name }}</p>
                                    </td>
                                    <td><textarea class="form-control information" name="information[]" maxlength="1024">{{ $rd->information  }}</textarea></td>
                                    <td class="text-center"><i class="fa fa-copy cursor-pointer btnCopy" title="行複写" aria-hidden="true"></i></td>
                                    <td class="text-center"><i class="fa fa-trash cursor-pointer btnDelete" title="削除" aria-hidden="true" data-rdid="{{ $rd->request_detail_id  }}"></i></td>
                                </tr>
                                @endforeach
                                @endif
                                @if(!old("BRNDCD") && !$dataRD)
                                <tr class="">
                                    <td>
                                        <select name="BRNDCD[]" class="form-control input-1 BRNDCD">
                                            <option value=""></option>
                                        </select>
                                    </td>
                                    <td class="text-center position-relative"> <input name="hoped_on[]" value="" class="hoped_on text-center datenotsun form-control w-7em pl-1 pr-1" type="text" autocomplete="off"></td>
                                    <td class="text-center position-relative">
                                        <input type="hidden" name="is_nohoped[]" value="0">
                                        <input type="checkbox" name="chk_nohoped[]" value="1" class="is_nohoped">
                                    </td>
                                    <td class="text-center position-relative"> </td>
                                    <td class="text-center">
                                        <p class="btn-color btn mb-0" style="color: #000; background-color: #ccc;"></p>
                                    </td>
                                    <td><textarea class="form-control information" name="information[]" maxlength="1024"></textarea></td>
                                    <td class="text-center"><i class="fa fa-copy cursor-pointer btnCopy" title="行複写" aria-hidden="true"></i></td>
                                    <td class="text-center"><i class="fa fa-trash cursor-pointer btnDelete" title="削除" aria-hidden="true"></i></td>
                                </tr>
                                @endif

                            </tbody>
                        </table>
                        <label class="cursor-pointer btnAdd"><i class="fa fa-plus-circle" aria-hidden="true"></i><span class="pl-1">行を追加する</span></label>
                    </td>
                </tr>
                <tr>
                    <td class="td-1">依頼備考</td>
                    <td class="td-2"><textarea class="form-control request_note" name="request_note" maxlength="1024">{{ old('request_note',@$data->request_note)  }}</textarea></td>
                </tr>
                <tr>
                    <td class="td-1">ご担当者名</td>
                    <td class="td-2"><input type="text" maxlength="256" name="manager_name" readonly value="{{ @$data->applied_user ? @$data->manager_name : ''  }}" class="form-control input-1"></td>
                </tr>
                <tr>
                    <td class="td-1">ご連絡方法</td>
                    <td class="td-2">
                        <div class="mg-contact-type contact_type1 {{ (@$data->applied_user ? @$data->contact_type : '') ? '' : 'd-none' }}">
                            <label class=" w-9em"><span class="pl-1">メールでご連絡</span></label>
                            <input type="email" name="mail_address" readonly value="{{ @$data->applied_user ? @$data->mail_address : '' }}" maxlength="256" class="form-control input-1 contact_type_1 contact_type">
                        </div>
                        <div class="mg-contact-type contact_type1 {{ (@$data->applied_user ? @$data->contact_type : '') ? '' : 'd-none' }}">
                            <label class=" w-9em"><span class="pl-1">CCメールアドレス</span></label>
                            <input type="email" name="ccmail_address" readonly value="{{ @$data->applied_user ? @$data->ccmail_address : '' }}" maxlength="256" class="form-control input-1 contact_type_1 contact_type">
                        </div>
                        <div class="mg-contact-type mb-1 contact_type0 {{ (@$data->applied_user ? @$data->contact_type : '') ? 'd-none' : '' }}">
                            <label class=" w-9em"><span class="pl-1 w-8em">電話でご連絡</span></label>
                            <input type="text" name="tel_no" maxlength="32" readonly value="{{ @$data->applied_user ?  @$data->tel_no : '' }}" class="form-control input-1 contact_type_0 contact_type">
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </form>
</div>
<a href="{{ route('a.getCUSTCDInfo') }}" class="d-none route-getCUSTCDInfo"></a>
@endsection