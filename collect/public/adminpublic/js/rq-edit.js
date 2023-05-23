$(document).ready(function () {
    $(".form-show input").attr("disabled", true);
    $(".form-show select").attr("disabled", true);
    $(".form-show textarea").attr("disabled", true);
    $(".form-show .btnCopy").hide();
    $(".form-show .btnDelete").hide();

    setDateTimePicker();    //日付フォーマット（main.js関数をCall）
    setnohope();

    Emty = "$が入力されていません。";
    $('input[name=contact_type]').change(function () {
        $("." + "contact_type").attr("readonly", true);
        $("." + "contact_type_" + $('input[name=contact_type]:checked').val()).attr("readonly", false);
    });
    //行を追加する
    $(".btnAdd").on("click", function () {
        trhmlt = "<tr>" + $(".tr-default").html() + "</tr>";
        $(".tb-detail tbody").append(trhmlt);
        setDateTimePicker();    //日付フォーマット（main.js関数をCall）
        setnohope();
    })

    //行をコピーする
    $(document).on("click", ".btnCopy", function () {
        trhmlt = "<tr>" + $(".tr-default").html() + "</tr>";
        $(this).closest('tr').after(trhmlt);
        var clickcol = $(this).closest('tr');
        var addcol = clickcol.next();
        addcol.find('.BRNDCD').val(clickcol.find('.BRNDCD').val());
        addcol.find('.recovered_on').val(clickcol.find('.recovered_on').val());
        addcol.find('.hoped_on').val(clickcol.find('.hoped_on').val());
        addcol.find('.status').val(clickcol.find('.status').val());
        addcol.find('.information').val(clickcol.find('.information').val());
        setDateTimePicker();    //日付フォーマット（main.js関数をCall）
        setnohope();
    })

    //編集画面に戻る
    $(".btnEdit").on("click", function () {
        $(".title-header").html(oldtitle);
        $(".mg-errors").html("");
        $(".form-show input").attr("disabled", false);
        $(".form-show select").attr("disabled", false);
        $(".form-show textarea").attr("disabled", false);
        $(".form-show .btnDelete").show();
        $(".formbtn").removeClass("form-check");
        $(".formbtn").removeClass("form-show");

    })
    $(".btnAppOk").on("click", function () {
        $(".form-show input").attr("disabled", false);
        $(".form-show select").attr("disabled", false);
        $(".form-show textarea").attr("disabled", false);
        $(this).attr("type", "submit");
    })
    form_show();

    function form_show() {
        $(".form-show input:not([type='hidden'])").attr("disabled", true);
        $(".form-show select").attr("disabled", true);
        $(".form-show textarea").attr("disabled", true);
        $(".form-show .btnDelete").hide();
    }
    $("input").on("keydown",function(e){
        // エンターキーだったら無効にする
        if (e.key === 'Enter') {
            e.preventDefault();
            return false;
        }
    });
    $(document).on("click", ".btnDelete", function () {
        $(this).closest("tr").remove();
    })

    // 保存
    $(".btnTemp").on("click", function () {
        ErrMsgCheck = Validate();
        if (ErrMsgCheck.length === 0) {
            $(this).attr("type", "submit");
            $(this).submit();
        } else {
            $("#MessageModal .modal-body").html(ErrMsgCheck.join("<br>"));
            $("#MessageModal").modal();
        }
    })
    // キャンセル
    $(".btnCancel").on("click", function () {
        $("#ConfirmModal .modal-body").html("キャンセルしますか？");
        $("#ConfirmModal .btnPopupOk").attr("data-btn", "cancel");
        $("#ConfirmModal").modal();
    })
    // 【削除】ボタン
    $(".btnDelele").on("click", function () {
        $("#ConfirmModal .modal-body").html("削除しますか？");
        $("#ConfirmModal .btnPopupOk").attr("data-btn", "delete");
        $("#ConfirmModal").modal();
    })
    $(".btnPopupOk").on("click", function () {
        if ($(this).attr("data-btn") == "app") {
            $(".btnApp").click();
        }
        if ($(this).attr("data-btn") == "cancel") {
            window.location.href = $(".route-history").attr("href");
        }
        if ($(this).attr("data-btn") == "delete") {
            $(".btnDelelesubmit").click();
        }
    })

    //希望日なしのチェックボックスを変更
    function setnohope() {
        $(".is_nohoped").change(function () {
            if ($(this).prop('checked')) {
                var elm = $(this).closest('tr').find('input:hidden[name="is_nohoped[]"]');
                elm.val("1");
                //チェックされたら希望日を入力不可にする
                var elm = $(this).closest('tr').find('.hoped_on');
                elm.val("");
                elm.prop("readonly", true);
            } else {
                var elm = $(this).closest('tr').find('input:hidden[name="is_nohoped[]"]');
                elm.val("0");
                //チェックされたら希望日を入力可にする
                var elm = $(this).closest('tr').find('.hoped_on');
                elm.prop("readonly", false);
            }
        })
    }

    function ValidateTemp() {
        ErrMsg = [];
        var BRNDCD = $('.BRNDCD').map(function () {
            return this.value;
        }).get();
        $.each(BRNDCD, function (index) {
            if (!BRNDCD[index] && index > 0) {
                ErrMsg.push(Emty.replace("$", "依頼内容の銘柄"));
                return false;
            }
        })

        return ErrMsg;
    }

    function Validate() {
        ErrMsg = [];
        var BRNDCD = $('.BRNDCD').map(function () {
            return this.value;
        }).get();
        var status = $('.status').map(function () {
            return this.value;
        }).get();
        flgBRNDCD = false;
        flgstatus = false;
        $.each(BRNDCD, function (index) {
            if ((!BRNDCD[index]) && index > 0) {
                flgBRNDCD = true;
            }
            if ((!status[index]) && index > 0) {
                flgstatus = true;
            }
        })
        if (flgBRNDCD) {
            ErrMsg.push(Emty.replace("$", "依頼内容の銘柄"));
        }
        if (flgstatus) {
            ErrMsg.push(Emty.replace("$", "依頼内容の状態"));
        }


        return ErrMsg;
    }
})