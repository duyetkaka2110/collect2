@extends('admin.layouts.layout')
@section("title","ガラスリソーシング：引取管理システム")
@section("css")
<link href="{{ URL::asset('requestpublic/css/client.css') }}" rel="stylesheet">
<link href="{{ URL::asset('adminpublic/css/uedit.css') }}" rel="stylesheet">
@endsection
@section("js")
<script src="{{ URL::asset('adminpublic/js/uedit.js') }}"></script>
@endsection
@section('content')
<div class="mg-content float-right">
    <form action="" class="form-search {{ !$user_id ? 'form-new' : (@$data->is_deleted == 1 ? 'form-show' : '') }}" method="POST">
        <input type="hidden" name="_token" value="{!! csrf_token() !!}">
        <h5 class="title-header d-inline-block">{{ @$title}}</h5>
        <div class="d-inline-block float-right">
            @if(!@$data->is_deleted || !$user_id)
            <button type="button" class="btn btn-primary pt-0 pb-0 ml-3 w-8em btnOk" name="btnOk" value="1">保存</button>
            @endif
            @if(@$data->is_deleted == 0 && $user_id)
            <button type="button" class="btn btn-primary pt-0 pb-0 ml-3 w-8em btnDel" name="btnDel" value="1">削除</button>
            @endif
            <a class="btn btn-primary pt-0 pb-0 ml-3 w-8em " href="{{ route('a.ulist') }}">戻る</a>
        </div>
        <div class="top-table mt-2">
            <table class="w-100 m-w-1000">
                <tr>
                    <td class="td-1">ユーザーID<span class="note">（※）</span></td>
                    <td class="td-2"><input type="text" {{ $user_id ? "readonly":"" }} name="user_id" maxlength="32" value="{{ @$data->user_id}}" class="form-control input-2"></td>
                </tr>
                <tr>
                    <td class="td-1">ユーザ種別<span class="note">（※）</span></td>
                    <td class="td-2">
                        @foreach($list_user_type as $l)
                        <div class="d-inline-block"><label><input class="user_type" type="radio" {{ $user_id ? "disabled" : ""}} {{  (!$user_id && $l->enum_key == 0) ? "checked" : "" }} {{ @$data->user_type == $l->enum_key ? "checked" : ""}} name="user_type" value="{{$l->enum_key}}"> <span>{{$l->enum_value}}</span></label></div>
                        &emsp;
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <td class="td-1">ユーザ権限</span></td>
                    <td class="td-2">
                        @foreach($list_authority_type as $l)
                        <div class="d-inline-block">
                            <label>
                                <input type="checkbox" class="authority_type" {{ $authority_types && array_key_exists( $l->enum_key,$authority_types ) ? "checked" : ""}} name="authority_type[]" value="{{$l->enum_key}}"> <span>{{$l->enum_value}}</span>
                            </label>
                        </div>
                        &emsp;
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <td class="td-1">お取引先</td>
                    <td class="td-2">
                        <select class="form-control input-1 CUSTCD" name="CUSTCD">
                            <option value=""></option>
                            @if($listsup)
                            @foreach($listsup as $l)
                            <option value="{{ $l->CUSTCD }}" {{ (@$data->CUSTCD==$l->CUSTCD) ? "selected" : "" }}>{{ $l->CUSTNM }}</option>
                            @endforeach
                            @endif
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="td-1">お取引先担当者</td>
                    <td class="td-2">
                        <select class="form-control input-1 TCSTNO" name="TCSTNO">
                            <option value=""></option>
                            @if($listContact)
                            @foreach($listContact as $l)
                            <option value="{{ $l->TCSTNO }}" {{ (@$data->TCSTNO==$l->TCSTNO) ? "selected" : "" }}>{{ $l->TCSIKJ }}</option>
                            @endforeach
                            @endif
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="td-1">パスワード<span class="note">（※）</span></td>
                    <td class="td-2"><input type="password" name="password" maxlength="256" value="{{ @$data->password }}" class="form-control input-1"></td>
                </tr>
                <tr>
                    <td class="td-1">氏名（姓）<span class="note">（※）</span></td>
                    <td class="td-2"><input type="text" name="family_name" maxlength="100" value="{{ @$data->family_name  }}" class="form-control input-1"></td>
                </tr>
                <tr>
                    <td class="td-1">氏名（名）</td>
                    <td class="td-2"><input type="text" name="first_name" maxlength="100" value="{{ @$data->first_name  }}" class="form-control input-1"></td>
                </tr>
                <tr>
                    <td class="td-1">メールアドレス</td>
                    <td class="td-2"><input type="email" multiple name="mail_address" maxlength="256" value="{{ @$data->mail_address}}" class="form-control input-1 contact_type_1 mail_address"></td>
                </tr>
                <tr>
                    <td class="td-1">CCメールアドレス</td>
                    <td class="td-2"><input type="email" multiple name="ccmail_address" maxlength="256" value="{{ @$data->ccmail_address}}" class="form-control input-1 contact_type_1 ccmail_address"></td>
                </tr>
                <tr>
                    <td class="td-1">電話番号</td>
                    <td class="td-2"><input type="text" name="tel_no" maxlength="32" value="{{ @$data->tel_no}}" class="form-control input-1 contact_type_0"></td>
                </tr>
                <tr>
                    <td class="td-1">連絡方法</td>
                    <td class="td-2">
                        <div class="d-inline-block"><label><input type="radio" {{ (($data && $data->contact_type == 1) || !$data) ? "checked" : ""}} name="contact_type" value="1"> <span>メールでご連絡</span></label></div>
                        &emsp;
                        <div class="d-inline-block"><label><input type="radio" {{ ($data && $data->contact_type == 0) ? "checked" : ""}} name="contact_type" value="0"> <span>電話でご連絡</span></label></div>
                    </td>
                </tr>
            </table>
        </div>
    </form>
</div>
<a href="{{ route('a.getContactHtml') }}" class="d-none route-getContactHtml"></a>
<a href="{{ route('a.getContactValue') }}" class="d-none route-getContactValue"></a>
<a href="{{ route('a.checkUserID') }}" class="d-none route-checkUserID"></a>
@endsection