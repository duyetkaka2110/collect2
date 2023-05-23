@extends('admin.layouts.layout')
@section("title","ガラスリソーシング：引取管理システム")
@section("css")
<link href="{{ URL::asset('adminpublic/css/ulist.css') }}" rel="stylesheet">
<style>
    .mg-pagination-number {
        margin: 0 auto;
    }
</style>
@endsection
@section("js")
<script>

</script>
<script src="{{ URL::asset('adminpublic/js/ulist.js') }}"></script>
@endsection
@section('content')
<div class="mg-content w-100">
    <form action="" class="formbtn " method="POST">
        <input type="hidden" name="_token" value="{!! csrf_token() !!}">
        <input type="hidden" name="request_id" value="{{ @$data->request_id}}">
        <div class="d-inline-block float-right mg-btn">
            <div>
                <div class="form-group d-inline-block ml-2 mb-1">
                    <label class="d-block m-0" for="">削除</label>
                    <label>
                        <input value="1" {{ (old('DelFlag') == 1) ? 'checked': '' }} name="DelFlag" type="checkbox">
                    </label>
                </div>
                <div class="form-group d-inline-block ml-2 mb-1">
                    <label class="d-block m-0" for="">種別</label>
                    @foreach($listUtype as $t)
                    <label>
                        <input value="{{  $t->enum_key }}" {{ (!old('request_type') && !$btnSearch) ? 'checked': '' }} {{ in_array( $t->enum_key, old('request_type', [])) ? 'checked' : '' }} name="request_type[]" type="checkbox">
                        <span>{{ $t->enum_value }}</span>
                    </label>
                    @endforeach
                </div>

                <div class="form-group d-inline-block ml-2 mb-1">
                    <label class="d-block m-0" for="">ユーザID</label>
                    <label>
                        <input value="{{ old('user_id') }}" class="form-control input-1" name="user_id" type="text">
                    </label>
                </div>
                <div class="form-group d-inline-block ml-2 mb-1">
                    <label class="d-block m-0" for="">取引先</label>
                    <label>
                        <input value="{{ old('CUSTNM') }}" class="form-control input-1" name="CUSTNM" type="text">
                    </label>
                </div>
                <div class="form-group d-inline-block ml-2 mb-1">
                    <label class="d-block m-0" for="">氏名</label>
                    <label>
                        <input value="{{ old('name') }}" class="form-control input-1" name="name" type="text">
                    </label>
                </div>
                <button type="submit" class="btn btn-primary pt-0 pb-0 ml-3 w-8em btnSearch " name="btnSearch" value="search">検索</button>
            </div>
            <div class="text-right mb-1">
                <a type="button" class="btn btn-primary pt-0 pb-0 ml-3 w-8em  " href="{!! route('a.uedit',['id' => 0]) !!}" name="btnOk" value="save">新規追加</a>
            </div>
        </div>
        <div class="clear-both"></div>
        <div class="top-table mt-2 wrapper">
            <table class="table table-history w-100 mt-0 table-list  table-hover table_sticky">
                <thead>
                    <tr>
                        <th scope="col" class="w-3em  text-nowrap">編集</th>
                        <th scope="col" class="w-5em text-nowrap">ユーザID</th>
                        <th scope="col" class="w-7em text-nowrap">ユーザ種別</th>
                        <th scope="col" class="w-6em text-nowrap">ユーザ権限</th>
                        <th scope="col" class="w-5em text-nowrap">取引先</th>
                        <th scope="col" class="w-5em text-nowrap">氏名（姓）</th>
                        <th scope="col" class="w-5em text-nowrap">氏名（名）</th>
                        <th scope="col" class="w-5em text-nowrap">連絡方法</th>
                        <th scope="col" class="w-8em text-nowrap">メールアドレス</th>
                        <th scope="col" class="w-9em text-nowrap">CCメールアドレス</th>
                        <th scope="col" class="w-10em text-nowrap">電話番号</th>
                        <th scope="col" class="w-3em text-nowrap">削除</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach($list as $l)
                    <tr>
                        <td class="text-center"><a href="{!! route('a.uedit',['id' => $l->user_id]) !!}" class="a-link" title="編集"><i class="fa fa-pencil" aria-hidden="true"></i></a></td>
                        <td class="">{{ $l->user_id }}</td>
                        <td class="text-center">{{ $l->TypeNM }}</td>
                        <td class="text-center">{!! str_replace(",","",$l->authority_typeNM) !!}</td>
                        <td class="">{{ $l->CUSTNM }}</td>
                        <td class="">{{ $l->family_name }}</td>
                        <td class="">{{ $l->first_name }}</td>
                        <td class="text-center">{{ $l->contact_typeNM }}</td>
                        <td class="">{!! str_replace(",",",<br>", $l->mail_address ) !!}</td>
                        <td class="">{{ $l->ccmail_address }}</td>
                        <td class="">{{ $l->tel_no }}</td>
                        <td class="text-center">
                            @if($l->is_deleted)
                            <span class=" cursor-pointer btnDelete" title="削除" aria-hidden="true" data-rdid="{{ $l->user_id  }}">削除</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    @if(!$list->total())
                    <tr>
                        <td colspan="100%" class="text-center">対象データがありません。</td>
                    </tr>
                    @endif
                </tbody>
            </table>

            <div class="mt-1">
                {{ $list->appends(request()->input())->links(); }}
            </div>
        </div>
    </form>
</div>

@endsection