$(document).ready(function() {
    Emty = "$が入力されていません。";
    // 保存ボタン
    $(".btnOk").on("click", function() {
        ErrMsgCheck = Validate();
        if (ErrMsgCheck.length === 0) {
            $("#ConfirmModal .modal-body").html("保存しますか？");
            $("#ConfirmModal").modal();
        } else {
            $("#MessageModal .modal-body").html(ErrMsgCheck.join("<br>"));
            $("#MessageModal").modal();
        }
    })
    $(".btnPopupOk").on("click", function() {
        $(".btnOk").attr("type", "submit");
        $(".btnOk").click();
    })
    // メールアドレスのフォーマットに合致しない場合、フォーカスアウト時に変更前の値に戻すよう
    $(document).on('focusin', 'input[type="email"]', function () {
        $(this).data('val', $(this).val());
    }).on('change', 'input[type="email"]', function () {
        var current = $(this).val();
        if (!validateEmail(current)) {
            var prev = $(this).data('val');
            $(this).val(prev);
        }
    });

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

    function Validate() {
        ErrMsg = [];
        if (!$("input[name='family_name']").val()) {
            ErrMsg.push(Emty.replace("$", "※ ご担当者名（姓）"));
        }
        if (!$("input[name='password']").val()) {
            ErrMsg.push(Emty.replace("$", "※ パスワード"));
        }

        if ($('input[name=contact_type]:checked').val() === "1") {
            if (!$("input[name='mail_address']").val() && !$("input[name='ccmail_address']").val()) {
                ErrMsg.push(Emty.replace("$", "※ メールアドレスとCCメールアドレスの双方"));
            }
        } else {
            if (!$("input[name='tel_no']").val()) {
                ErrMsg.push(Emty.replace("$", "※ 電話番号"));
            }
        }
        return ErrMsg;
    }
})