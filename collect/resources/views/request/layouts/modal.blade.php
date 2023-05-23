<!-- loading  -->
<div class="position-fixed w-100 h-100 loading d-none">
    <div class="text-center">
        <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
</div>

<div class="btn btn-success position-fixed l-0 r-0" id="success-alert" role="alert">更新が完了しました。</div>
<!-- Modal Confirm -->
<div class="modal fade" id="ConfirmModal" role="dialog" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header modal-header-error bg-danger">
                <h5 class="modal-title text-white">実行確認</h5>
            </div>
            <div class="modal-body">
                <p class="m-0">保存しますか？</p>
            </div>
            <div class="modal-footer text-center">
                <button type="button" class="btn btn-primary close-modal btnPopupOk w-6em" data-btn="1" data-dismiss="modal">はい</button>
                <button type="button" class="btn btn-danger close-modal w-6em" data-dismiss="modal">いいえ</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Error Message -->
<div class="modal fade" id="MessageModal" role="dialog" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header modal-header-error bg-danger p-2">
                <h5 class="modal-title text-white">エラーメッセージ</h5>
            </div>
            <div class="modal-body">
                @foreach ($errors->all() as $error)
                <p>{!! $error !!}</p>
                @endforeach
                {{ @$ErrMsg }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger close-modal" data-dismiss="modal">閉じる</button>
            </div>
        </div>
    </div>
</div>
@if($errors->any() || @$ErrMsg)
<script>
    $(document).ready(function() {
        $("#MessageModal").modal();
    });
</script>
@endif