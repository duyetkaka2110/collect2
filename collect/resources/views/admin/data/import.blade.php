@extends('admin.layouts.layout')
@section("title","ガラスリソーシング：引取管理システム")
@section("css")
<link href="{{ URL::asset('adminpublic/css/dimport.css') }}" rel="stylesheet">
@endsection
@section("js")
<script>
    // 先頭ファイル名一覧
    listfilename = <?php echo json_encode($listfilename) ?>;
</script>
<script src="{{ URL::asset('adminpublic/js/dimport.js') }}"></script>
@endsection
@section('content')
<div class="mg-content w-100">
    <form action="{{ route('a.dimport') }}" class="formbtn " method="POST" enctype='multipart/form-data'>
        <input type="hidden" name="_token" value="{!! csrf_token() !!}">
        <input type="hidden" name="request_id" value="{{ @$data->request_id}}">
        <div class="d-inline-block float-right mg-btn">
            <button type="button" class="btn btn-primary pt-0 pb-0 ml-3 w-8em btnOk " name="btnOk" value="save">取込</button>
        </div>
        <div class="top-table mt-2">
            <table class="w-100 ">
                <tr>
                    <td class="td-1"></td>
                    <td class="td-2 mg-errors ">
                    </td>
                </tr>
                <tr>
                    <td class="td-1" colspan="2">
                        <label>取込ファイル</label>
                        <div class="input-group mb-3 mg-input">
                            <input type="text" class="form-control filename" value="" name="" readonly>
                            <div class="input-group-append">
                                <label class="m-0">
                                    <input type="file" class="form-control dd-none" accept=".csv" id="selectfile" value="" name="file">
                                    <span class="input-group-text h-100 cursor-pointer"><i class="fa fa-upload" aria-hidden="true"></i></span>
                                </label>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </form>
</div>

@endsection