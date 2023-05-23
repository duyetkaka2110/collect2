$(document).ready(function () {
    oldtitle = $(".title-header").html();

    $(".form-show input:not([type='hidden'])").attr("disabled", true);
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
        if (clickcol.find('.is_nohoped').prop('checked')) {
            addcol.find('.hoped_on').prop("readonly", true);
            addcol.find('input:hidden[name="is_nohoped[]"]').val("1");
            addcol.find('.is_nohoped').prop('checked', true);
        } else {
            addcol.find('.hoped_on').val(clickcol.find('.hoped_on').val());
            addcol.find('input:hidden[name="is_nohoped[]"]').val("0");
            addcol.find('.is_nohoped').prop('checked', false);
        }
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

    $("input").on("keydown", function (e) {
        // エンターキーだったら無効にする
        if (e.key === 'Enter') {
            e.preventDefault();
            return false;
        }
    });
    $(document).on("click", ".btnDelete", function () {
        $(this).closest("tr").remove();
    })

    // 一時保存
    $(".btnTemp").on("click", function () {
        ErrMsgCheck = ValidateTemp();
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
    // 依頼申込
    $(".btnApp").on("click", function () {
        ErrMsgCheck = Validate();
        if (ErrMsgCheck.length === 0) {
            $("#ConfirmModal .modal-body").html("依頼申込を行いますか？");
            $("#ConfirmModal .btnPopupOk").attr("data-btn", "btnApp");
            $("#ConfirmModal").modal();
        } else {
            $("#MessageModal .modal-body").html(ErrMsgCheck.join("<br>"));
            $("#MessageModal").modal();
        }
    })
    // 取引先選択更新
    $(".CUSTCD_UserID").on("change", function () {
        $(".BRNDCD").html("");
        label = $(".CUSTCD_UserID").val();
        id = $("#CUSTCD_UserID option[data-label='" + label + "']").attr("data-value");
        if (id != undefined) {
            dataArr = id.split("-");
            $('.CUSTCD').val(dataArr[0])
            $('.user_id').val(dataArr[1])
            $.ajax({
                type: "post",
                data: {
                    CUSTCD: dataArr[0],
                    user_id: dataArr[1],
                },
                url: $(".route-getCUSTCDInfo").attr("href"),
                success: function (response) {
                    $(".mg-contact-type").addClass("d-none");
                    $(".contact_type" + response["user"]["contact_type"]).removeClass("d-none");
                    $(".CUSTNMtxt").html(response["user"]["CUSTNM"]);
                    $('input[name="manager_name"]').val(response["user"]["manager_name"])
                    $('input[name="tel_no"]').val(response["user"]["tel_no"])
                    $('input[name="mail_address"]').val(response["user"]["mail_address"])
                    $('input[name="ccmail_address"]').val(response["user"]["ccmail_address"])
                    $(".BRNDCD").html(response["listproducts"])
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.info(jqXHR["responseText"]);
                }
            });
        } else {
            $('.CUSTCD').val("");
            $('.user_id').val("");
            $(".CUSTNMtxt").html("");
            $('input[name="manager_name"]').val("");
            $('input[name="tel_no"]').val("");
            $('input[name="mail_address"]').val("");
            $('input[name="ccmail_address"]').val("");
        }
    })
    $(".btnPopupOk").on("click", function () {
        var strbtn = $(this).attr("data-btn");
        if (strbtn == "cancel") {
            window.location.href = $(".route-history").attr("href");
        } else if (strbtn == "delete") {
            $(".btnDelelesubmit").click();
        } else if (strbtn == "confirme") {
            $('.btnConfSubmit').click();
        } else if (strbtn == "btnApp") {
            $(".btnApp").attr("type", "submit");
            $(".btnApp").click();
        }
    });

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

        if (!$(".CUSTCD").val() && !$(".user_id").val() ) {
            ErrMsg.push(Emty.replace("$", "取引先選択"));
        }
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

})

function form_show() {
    $(".form-show input:not([type='hidden'])").attr("disabled", true);
    $(".form-show select").attr("disabled", true);
    $(".form-show textarea").attr("disabled", true);
    $(".form-show .btnDelete").hide();
}

function Validate() {
    ErrMsg = [];
    var BRNDCD = $('.BRNDCD').map(function () {
        return this.value;
    }).get();
    var hoped_on = $('.hoped_on').map(function () {
        return this.value;
    }).get();
    var is_nohoped = $('.is_nohoped').map(function () {
        var retval = 0;
        if ($(this).is(':checked')) retval = 1;
        return retval;
    });
    var information = $('.information').map(function () {
        return this.value;
    }).get();
    flgBRNDCD = false;
    flgHOPED = false;
    flgINFO = false;
    $.each(BRNDCD, function (index) {
        if ((!BRNDCD[index]) && index > 0) {
            flgBRNDCD = true;
        }
        if ((is_nohoped[index] != 1) && (!hoped_on[index]) && index > 0) {
            flgHOPED = true;
        }
        if ((is_nohoped[index] == 1) && (!information[index]) && index > 0) {
            flgINFO = true;
        }
    })
    if (!$(".CUSTCD").val() && !$(".user_id").val() ) {
        ErrMsg.push(Emty.replace("$", "取引先選択"));
    }
    if (flgBRNDCD) {
        ErrMsg.push(Emty.replace("$", "依頼内容の銘柄"));
    }
    if (flgHOPED) {
        ErrMsg.push(Emty.replace("$", "依頼内容の希望日"));
    }
    if (flgINFO) {
        ErrMsg.push(Emty.replace("$", "依頼内容の連絡事項"));
    }

    $.each(hoped_on, function (index) {
        if (hoped_on[index]) {
            if (moment(new Date(hoped_on[index])).format('YYYY/MM/DD') < moment().format('YYYY/MM/DD')) {
                ErrMsg.push("希望日には未来の日付を入力してください。</p>");
                return false;
            }
        }
    })

    return ErrMsg;
}