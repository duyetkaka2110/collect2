$(document).ready(function () {
    // ユーザ種別による初期設定
    setDisabled();
    //削除済みの場合、編集不可
    $(".form-show input").attr("disabled", true);
    $(".form-show select").attr("disabled", true);
    $(".form-show textarea").attr("disabled", true);

    Emty = "$が入力されていません。";
    // 保存ボタン
    $(".btnOk").on("click", function () {
        if ($(this).attr("type") != "submit") {
            ErrMsgCheck = Validate();
            if (ErrMsgCheck.length === 0) {
                if ($(".form-search").hasClass("form-new")) {
                    // （新規作成）
                    $.ajax({
                        type: "post",
                        data: {
                            user_id: $("input[name='user_id']").val(),
                        },
                        url: $(".route-checkUserID").attr("href"),
                        success: function (response) {
                            if (response) {
                                $("#MessageModal .modal-body").html(response);
                                $("#MessageModal").modal();
                            } else {
                                savePopup();
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            console.info(jqXHR["responseText"]);
                        }
                    });
                } else {
                    savePopup();
                }
            } else {
                $("#MessageModal .modal-body").html(ErrMsgCheck.join("<br>"));
                $("#MessageModal").modal();
            }
        }
    })
    // 削除ボタン
    $(".btnDel").on("click", function () {
        $("#ConfirmModal .modal-body").html("削除しますか？");
        $("#ConfirmModal .btnPopupOk").attr("data-btn", "delete");
        $("#ConfirmModal").modal();
    })
    $(".btnPopupOk").on("click", function () {
        data_btn = $(this).attr("data-btn");
        if (data_btn == "save") {
            $(".btnOk").attr("type", "submit");
            $(".btnOk").click();
        } else if (data_btn == "delete") {
            $(".btnDel").attr("type", "submit");
            $(".btnDel").click();
        }
    })
    // ユーザ種別更新
    $(".user_type").on("change", function () {
        setDisabled();
    })
    // お取引先更新
    $(".CUSTCD").on("change", function () {
        $.ajax({
            type: "post",
            data: {
                CUSTCD: $(".CUSTCD").val(),
            },
            url: $(".route-getContactHtml").attr("href"),
            success: function (response) {
                $(".TCSTNO").html(response);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.info(jqXHR["responseText"]);
            }
        });
    })
    // お取引先担当者更新
    $(document).on("change", ".TCSTNO", function () {
        $.ajax({
            type: "post",
            data: {
                CUSTCD: $(".CUSTCD").val(),
                TCSTNO: $(".TCSTNO").val(),
            },
            url: $(".route-getContactValue").attr("href"),
            success: function (response) {
                if (response) {
                    $('input[name="family_name"]').val(response["TCSIKJ"]);
                    $('input[name="first_name"]').val(response["TCMEKJ"]);
                    $('input[name="mail_address"]').val(response["MLADRS"]);
                    $('input[name="tel_no"]').val(response["RTELNO"]);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.info(jqXHR["responseText"]);
            }
        });
    })


    // メールアドレスのフォーマットに合致しない場合、フォーカスアウト時に変更前の値に戻すよう
    $(document).on('focusin', '.mail_address', function () {
        $(this).data('val', $(this).val());
    }).on('change', '.mail_address', function () {
        var current = $(this).val();
        if (!validateEmail(current)) {
            var prev = $(this).data('val');
            $(this).val(prev);
        }
    });
    $(document).on('focusin', '.ccmail_address', function () {
        $(this).data('val', $(this).val());
    }).on('change', '.ccmail_address', function () {
        var current = $(this).val();
        if (!validateEmail(current)) {
            var prev = $(this).data('val');
            $(this).val(prev);
        }
    });

    function savePopup() {
        $("#ConfirmModal .modal-body").html("保存しますか？");
        $("#ConfirmModal .btnPopupOk").attr("data-btn", "save");
        $("#ConfirmModal").modal();
    }
    function validateEmail(email) {
        var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
        flag = true;
        emailArr = email.split(",");
        $.each(emailArr, function (key, e) {
            if (!emailReg.test(e)) {
                flag = false;
                return false;
            }
        });
        return flag;
    }
    function setDisabled() {
        if ($(".user_type:checked").val() == 1) {
            $(".authority_type").prop("disabled", true);
            $(".CUSTCD").prop("disabled", false);
            $(".TCSTNO").prop("disabled", false);
        } else {
            $(".authority_type").prop("disabled", false);
            $(".CUSTCD").prop("disabled", true);
            $(".TCSTNO").prop("disabled", true);
        }
    }

    function Validate() {
        ErrMsg = [];
        if (!$("input[name='user_id']").val()) {
            ErrMsg.push(Emty.replace("$", "※ ユーザーID"));
        }
        // 引取依頼用  
        if ($(".user_type:checked").val() == 1) {
            if (!$(".CUSTCD").val()) {
                ErrMsg.push(Emty.replace("$", "※ お取引先"));
            }
        }
        if (!$("input[name='password']").val()) {
            ErrMsg.push(Emty.replace("$", "※ パスワード"));
        }
        if (!$("input[name='family_name']").val()) {
            ErrMsg.push(Emty.replace("$", "※ 氏名（姓）"));
        }
        // 引取依頼用  
        if ($(".user_type:checked").val() == 1) {
            if ($('input[name=contact_type]:checked').val() === "1") {
                if (!$("input[name='mail_address']").val() && !$("input[name='ccmail_address']").val()) {
                    ErrMsg.push(Emty.replace("$", "※ メールアドレスとCCメールアドレスの双方"));
                }
            } else {
                if (!$("input[name='tel_no']").val()) {
                    ErrMsg.push(Emty.replace("$", "※ 電話番号"));
                }
            }
        }
        return ErrMsg;
    }
})