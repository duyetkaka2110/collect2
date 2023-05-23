$(document).ready(function() {
    oldtitle = $(".title-header").html();

    $(".form-show input:not([type='hidden'])").attr("disabled", true);
    $(".form-show select").attr("disabled", true);
    $(".form-show textarea").attr("disabled", true);
    $(".form-show .btnCopy").hide();
    $(".form-show .btnDelete").hide();

    setDateTimePicker();    //日付フォーマット（main.js関数をCall）
    setnohope();

    Emty = "$が入力されていません。";
    $('input[name=contact_type]').change(function() {
        $("." + "contact_type").attr("readonly", true);
        $("." + "contact_type_" + $('input[name=contact_type]:checked').val()).attr("readonly", false);
    });
    //行を追加する
    $(".btnAdd").on("click", function() {
        trhmlt = "<tr>" + $(".tr-default").html() + "</tr>";
        $(".tb-detail tbody").append(trhmlt);
        setDateTimePicker();    //日付フォーマット（main.js関数をCall）
        setnohope();
    })

    //行をコピーする
    $(document).on("click", ".btnCopy", function() {
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
    $(".btnEdit").on("click", function() {
        $(".title-header").html(oldtitle);
        $(".mg-errors").html("");
        $(".form-show input").attr("disabled", false);
        $(".form-show select").attr("disabled", false);
        $(".form-show textarea").attr("disabled", false);
        $(".form-show .btnDelete").show();
        $(".formbtn").removeClass("form-check");
        $(".formbtn").removeClass("form-show");

    })
    $(".btnAppOk").on("click", function() {
        $(".form-show input").attr("disabled", false);
        $(".form-show select").attr("disabled", false);
        $(".form-show textarea").attr("disabled", false);
        $(this).attr("type", "submit");
    })
    form_show();

    $("input").on("keydown",function(e){
        // エンターキーだったら無効にする
        if (e.key === 'Enter') {
            e.preventDefault();
            return false;
        }
    });
    $(document).on("click", ".btnDelete", function() {
        $(this).closest("tr").remove();
    })

    // 一時保存
    $(".btnTemp").on("click", function() {
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
    $(".btnCancel").on("click", function() {
            $("#ConfirmModal .modal-body").html("キャンセルしますか？");
            $("#ConfirmModal .btnPopupOk").attr("data-btn", "cancel");
            $("#ConfirmModal").modal();
        })
    // 【削除】ボタン
    $(".btnDelele").on("click", function() {
        $("#ConfirmModal .modal-body").html("削除しますか？");
        $("#ConfirmModal .btnPopupOk").attr("data-btn", "delete");
        $("#ConfirmModal").modal();
    })
    // 【確認】ボタン
    $(document).on("click", ".btnConf", function() {
        var clickcol = $(this).closest('tr');
        var msg = "銘柄：#1の回収日は#2でよろしいですか？";
        
        var parm1 = clickcol.find('input:text[name="BRNDNM[]"]').val();
        var parm2 = clickcol.find('input:hidden[name="recovered_on[]"]').val();
        msg = msg.replace('#2', parm2);
        msg = msg.replace('#1', parm1);
        $('input:hidden[name="request_detail_id"]').val($(this).attr("data-rdid"));     //画面の隠し領域にクリックした行の依頼内容IDをセット
        $('input:hidden[name="store_recovered_on"]').val(parm2);                        //画面の隠し領域にクリックした行の回収日をセット
        $("#ConfirmModal .modal-body").html(msg);
        $("#ConfirmModal .btnPopupOk").attr("data-btn", "confirme");
        $("#ConfirmModal").modal();
        return false;
    });
    $(".btnPopupOk").on("click", function() {
        var strbtn = $(this).attr("data-btn");
        if (strbtn == "cancel") {
            window.location.href = $(".route-history").attr("href");
        } else if (strbtn == "delete") {
            $(".btnDelelesubmit").click();
        } else if (strbtn == "confirme") {
            $('.btnConfSubmit').click();
        }
    });

    //希望日なしのチェックボックスを変更
    function setnohope() {
        $(".is_nohoped").change(function() {
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
        var BRNDCD = $('.BRNDCD').map(function() {
            return this.value;
        }).get();
        $.each(BRNDCD, function(index) {
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
    var BRNDCD = $('.BRNDCD').map(function() {
        return this.value;
    }).get();
    var hoped_on = $('.hoped_on').map(function() {
        return this.value;
    }).get();
    var is_nohoped = $('.is_nohoped').map(function() {
        var retval = 0;
        if ($(this).is(':checked')) retval = 1;
        return retval;
    });
    var information = $('.information').map(function() {
        return this.value;
    }).get();
    flgBRNDCD = false;
    flgHOPED = false;
    flgINFO = false;
    $.each(BRNDCD, function(index) {
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
    if (flgBRNDCD) {
        ErrMsg.push(Emty.replace("$", "依頼内容の銘柄"));
    }
    if (flgHOPED) {
        ErrMsg.push(Emty.replace("$", "依頼内容の希望日"));
    }
    if (flgINFO) {
        ErrMsg.push(Emty.replace("$", "依頼内容の連絡事項"));
    }

    $.each(hoped_on, function(index) {
        if (hoped_on[index]) {
            if (moment(new Date(hoped_on[index])).format('YYYY/MM/DD') < moment().format('YYYY/MM/DD')) {
                ErrMsg.push("希望日には未来の日付を入力してください。</p>");
                return false;
            }
        }
    })

    return ErrMsg;
}