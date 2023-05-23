@extends('request.layouts.layout')
@section("title","ガラスリソーシング：引取依頼システム")
@section("css")
<link href="{{ URL::asset('requestpublic/css/client.css') }}" rel="stylesheet">
@endsection
@section("js")
<script src="{{ URL::asset('requestpublic/js/client.js') }}"></script>
@endsection
@section('content')
<div class="mg-content float-right">
    <form action="{{ route('r.client') }}" class="form-search" method="POST">
        <input type="hidden" name="_token" value="{!! csrf_token() !!}">
        <h5 class="title-header d-inline-block">登録情報</h5>
        <div class="d-inline-block float-right">
            <button type="button" class="btn btn-primary pt-0 pb-0 ml-3 w-8em btnOk" name="btnOk" value="1">保存</button>
        </div>
        <div class="top-table mt-2">
            <table class="w-100 m-w-1000">
                <tr>
                    <td class="td-1">ユーザーID</td>
                    <td class="td-2"><input type="text" readonly name="" value="{{ @$data->user_id}}" class="form-control input-2"></td>
                </tr>
                <tr>
                    <td class="td-1">お取引先名</td>
                    <td class="td-2"><input type="text" readonly name="" value="{{ @$data->CUSTNM}}" class="form-control input-1"></td>
                </tr>
                <tr>
                    <td class="td-1">パスワード</td>
                    <td class="td-2"><input type="password" name="password" maxlength="256" value="{{ @$data->password}}" class="form-control input-1"></td>
                </tr>
                <tr>
                    <td class="td-1">ご担当者名（姓）</td>
                    <td class="td-2"><input type="text" name="family_name" maxlength="100" value="{{ @$data->family_name  }}" class="form-control input-1"></td>
                </tr>
                <tr>
                    <td class="td-1">ご担当者名（名）</td>
                    <td class="td-2"><input type="text" name="first_name" maxlength="100" value="{{ @$data->first_name  }}" class="form-control input-1"></td>
                </tr>
                <tr>
                    <td class="td-1">メールアドレス</td>
                    <td class="td-2"><input type="email" multiple name="mail_address" maxlength="256" value="{{ @$data->mail_address}}" class="form-control input-1 contact_type_1"></td>
                </tr>
                <tr>
                    <td class="td-1">CCメールアドレス</td>
                    <td class="td-2"><input type="email" multiple name="ccmail_address" maxlength="256" value="{{ @$data->ccmail_address}}" class="form-control input-1 contact_type_1"></td>
                </tr>
                <tr>
                    <td class="td-1">電話番号</td>
                    <td class="td-2"><input type="text" name="tel_no" maxlength="32" value="{{ @$data->tel_no}}" class="form-control input-1 contact_type_0"></td>
                </tr>
                <tr>
                    <td class="td-1">連絡方法</td>
                    <td class="td-2">
                        <div class="d-inline-block"><label><input type="radio" {{ @$data->contact_type == 1 ? "checked" : ""}} name="contact_type" value="1" class=""> <span>メールでご連絡</span></label></div>
                        &emsp;
                        <div class="d-inline-block"><label><input type="radio" {{ @$data->contact_type == 0 ? "checked" : ""}} name="contact_type" value="0" class=""> <span>電話でご連絡</span></label></div>
                    </td>
                </tr>
            </table>
        </div>
    </form>
</div>
@endsection