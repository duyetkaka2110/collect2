$(document).ready(function () {
    $(".btnOk").on("click", function () {
        $("#ConfirmModal .modal-body").html("保存しますか？");
        $("#ConfirmModal").modal();
    })
    $(".btnPopupOk").on("click", function () {
        $(".formbtn").attr("action", "");
        $(".btnOk").attr("type", "submit");
        $(".btnOk").click();
    })
    $(".btnSearch2").on("click", function () {
        $(".page_no").val("")
    })
})