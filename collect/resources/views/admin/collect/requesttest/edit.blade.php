@extends('admin.layouts.layout')
@section("title","ガラスリソーシング：引取管理システム")
@section("css")
<link href="{{ URL::asset('adminpublic/css/rq-edit.css') }}" rel="stylesheet">
@endsection
@section("js")
<script src="{{ URL::asset('adminpublic/js/rq-edit.js') }}"></script>
@endsection
@section('content')
<div class="mg-content w-100">
    <form action="{{ route('a.redit_store') }}" class="formbtn {{ @$classtitle }}" method="POST">
        <input type="hidden" name="_token" value="{!! csrf_token() !!}">
        <input type="hidden" name="request_id" value="{{ @$data->request_id}}">
        <div class="d-inline-block float-right mg-btn">
            <button type="button" class="btn btn-primary pt-0 pb-0 ml-3 w-8em btnTemp new temp dd-none" name="btnOk" value="save">保存</button>
            <a type="button" href="{{ route('a.rsearch') }}" class="btn btn-primary pt-0 pb-0 ml-3 w-8em  " name="btnOk" value="">戻る</a>
        </div>
        <div class="top-table mt-2">
            <table class="w-100 ">
                <tr>
                    <td class="td-1"></td>
                    <td class="td-2 mg-errors ">
                    </td>
                </tr>
                <tr>
                    <td class="td-1">取引先</td>
                    <td class="td-2">
                        <input type="text" readonly name="CUSTNM" value="{{ @$data->CUSTNM}}" class="form-control input-1">
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
                    <td class="td-1">引取／持込<span class="note">（※）</span></td>
                    <td class="td-2">
                        <select name="request_type" class="form-control input-1">
                            @foreach($request_type as $t)
                            <option value="{{ $t->enum_key}}" {{ old("request_type",@$data->request_type) ==$t->enum_key ? " selected ": "" }}>{{ $t->enum_value}}</option>
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
                                            @foreach($listproducts as $p)
                                            <option value="{{ $p->BRNDCD  }}">{{ $p->BRNDNM   }}</option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="request_detail_id[]" value="">
                                    </td>
                                    <td class="text-center position-relative"><input name="hoped_on[]" type="text" autocomplete="off" class="hoped_on text-center datenotsun form-control w-7em pl-1 pr-1"></td>
                                    <td class="text-center position-relative">
                                        <input name="recovered_on[]" value="" class="recovered_on text-center datenotsun form-control w-7em pl-1 pr-1" type="text" autocomplete="off">
                                    </td>
                                    <td class="text-center">
                                        <select name="status[]" class="form-control input-1 status">
                                            <option value=""></option>
                                            @foreach($status as $s)
                                            <option value="{{ $s->status_id  }}">{{ $s->status_name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><textarea class="form-control information" name="information[]" maxlength="1024"></textarea></td>
                                    <td class="text-center"><i class="fa fa-copy cursor-pointer btnCopy" title="行複写" aria-hidden="true"></i></td>
                                    <td class="text-center"><i class="fa fa-trash cursor-pointer btnDelete" title="削除" aria-hidden="true"></i></td>
                                </tr>
                                <tr>
                                    <td>
                                        <select name="BRNDCD[]" class="form-control input-1 BRNDCD">
                                            <option value=""></option>
                                            @foreach($listproducts as $p)
                                            <option value="{{ $p->BRNDCD  }}" {{ old('BRNDCD.1',@$dataRD[0]->BRNDCD) == $p->BRNDCD ? "selected": "" }}>{{ $p->BRNDNM  }}</option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="request_detail_id[]" value="{{ @$dataRD[0]->request_detail_id }}">
                                    </td>
                                    <td class="text-center position-relative">
                                        <input name="hoped_on[]" type="text" autocomplete="off" value="{{ @$dataRD[0]->hoped_on }}" class="hoped_on text-center datenotsun form-control w-7em pl-1 pr-1">
                                    </td>
                                    <td class="text-center position-relative">
                                        <input name="recovered_on[]" value="{{ old('recovered_on.1',@$dataRD[0]->recovered_on) }}" class="recovered_on text-center datenotsun form-control w-7em pl-1 pr-1" type="text" autocomplete="off">
                                    </td>
                                    <td class="text-center">

                                        <select name="status[]" class="form-control input-1 status">
                                            <option value=""></option>
                                            @foreach($status as $s)
                                            <option value="{{ $s->status_id  }}" {{@$dataRD[0]->status_id == $s->status_id ? "selected " : "" }}>{{ $s->status_name  }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><textarea class="form-control information" name="information[]" maxlength="1024">{{ old('information.1',@$dataRD[0]->information) }}</textarea></td>
                                    <td class="text-center"><i class="fa fa-copy cursor-pointer btnCopy" title="行複写" aria-hidden="true"></i></td>
                                    <td class="text-center"></td>
                                </tr>
                                @if(old("BRNDCD") && count(old("BRNDCD"))>2)
                                @foreach(old("BRNDCD") as $k=>$c)
                                @if($k>1)
                                <tr>
                                    <td>
                                        <select name="BRNDCD[]" class="form-control input-1 BRNDCD">
                                            <option value=""></option>
                                            @foreach($listproducts as $p)
                                            <option value="{{ $p->BRNDCD  }}" {{ old('BRNDCD.'.$k) == $p->BRNDCD ? "selected": "" }}>{{ $p->BRNDNM  }}</option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="request_detail_id[]" value="{{  old('BRNDCD.'.$k) }}">
                                    </td>
                                    <td class="text-center position-relative"><input name="hoped_on[]" type="text" autocomplete="off" value="" class="hoped_on text-center datenotsun form-control w-7em pl-1 pr-1"> </td>

                                    <td class="text-center position-relative">
                                        <input name="recovered_on[]" value="{{ old('recovered_on.'.$k) }}" class="recovered_on text-center datenotsun form-control w-7em pl-1 pr-1" type="text" autocomplete="off">
                                    </td>
                                    <td class="text-center">

                                        <select name="status[]" class="form-control input-1 status">
                                            <option value=""></option>
                                            @foreach($status as $s)
                                            <option value="{{ $s->status_id  }}" {{@$dataRD[0]->status_id == $s->status_id ? "selected " : "" }}>{{ $s->status_name  }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><textarea class="form-control information" name="information[]" maxlength="1024"></textarea></td>
                                    <td class="text-center"><i class="fa fa-copy cursor-pointer btnCopy" title="行複写" aria-hidden="true"></i></td>
                                    <td class="text-center"><i class="fa fa-trash cursor-pointer btnDelete" title="削除" aria-hidden="true"></i></td>
                                </tr>
                                @endif
                                @endforeach
                                @else
                                @foreach($dataRD as $key => $rd)
                                @if($key > 0)
                                <tr>
                                    <td>
                                        <select name="BRNDCD[]" class="form-control input-1 BRNDCD">
                                            <option value=""></option>
                                            @foreach($listproducts as $p)
                                            <option value="{{ $p->BRNDCD }}" {{ $rd->BRNDCD == $p->BRNDCD ? "selected": "" }}>{{ $p->BRNDNM  }}</option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="request_detail_id[]" value="{{ @$rd->request_detail_id }}">
                                    </td>
                                    <td class="text-center position-relative"><input name="hoped_on[]" type="text" autocomplete="off" value="{{ $rd->hoped_on }}" class="hoped_on text-center datenotsun form-control w-7em pl-1 pr-1"> </td>

                                    <td class="text-center position-relative">
                                        <input name="recovered_on[]" value="{{ $rd->recovered_on  }}" class="recovered_on text-center datenotsun form-control w-7em pl-1 pr-1" type="text" autocomplete="off">
                                    </td>
                                    <td class="text-center">
                                        <select name="status[]" class="form-control input-1 status">
                                            <option value=""></option>
                                            @foreach($status as $s)
                                            <option value="{{ $s->status_id  }}" {{ $rd->status_id == $s->status_id ? "selected " : "" }}> {{ $s->status_name  }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><textarea class="form-control information" name="information[]" maxlength="1024">{{ $rd->information  }}</textarea></td>
                                    <td class="text-center"><i class="fa fa-copy cursor-pointer btnCopy" title="行複写" aria-hidden="true"></i></td>
                                    <td class="text-center"><i class="fa fa-trash cursor-pointer btnDelete" title="削除" aria-hidden="true" data-rdid="{{ $rd->request_detail_id  }}"></i></td>
                                </tr>
                                @endif
                                @endforeach
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
</div>
<style>
    /* 回収日を太字にする */
    .recovered_on {
        font-weight: bold;
    }
</style>
@endsection