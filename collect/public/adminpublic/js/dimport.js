$(document).ready(function () {
    $('#selectfile').on('change', function (e) {
        var file = $('#selectfile')[0].files[0]
        filename = $(".filename");
        if (file) {
            filename.val(e.target.files[0].name)
        } else {
            filename.val("")
        }
    });
    // 保存ボタン
    $(".btnOk").on("click", function () {
        ErrMsg = "";
        var file = $('#selectfile')[0].files[0]
        if (file) {
            if (file.type != "text/csv") {
                ErrMsg = "取込ファイルのファイル形式が異なります。";
            } else {
                firstname = file.name.substr(0, 12).toUpperCase();
                if (!(firstname in listfilename)) {
                    ErrMsg = "取込可能なファイル名ではありません。";
                }
            }
        } else {
            ErrMsg = "取込ファイルが選択されていません。";
        }
        if (ErrMsg) {
            $("#MessageModal .modal-body").html(ErrMsg);
            $("#MessageModal").modal();
        } else {
            $("#ConfirmModal .modal-body").html(listfilename[firstname]["name"] + "ファイルの取込を行いますか？");
            $("#ConfirmModal").modal();
        }
    })
    // 保存OKボタン
    $(".btnPopupOk").on("click", function () {
        $(".btnOk").attr("type", "submit");
        $(".btnOk").click();
    })
})