$(document).ready(function () {

    // 依頼内容：銘柄
    // ・値変更時は物流会社、車両種別、車両No：運転手の選択値をクリアし、物流会社の選択リストを再取得する
    $(document).on("change", ".BRNDCD", function () {
        CUSTCD = $("input[name=CUSTCD]").val();
        BRNDCD = $(this).val();
        PDCPCD = $(this).closest('tr').find(".PDCPCD");
        $(this).closest('tr').find(".car_type").html("");
        $(this).closest('tr').find(".car_detail").html("");
        $.ajax({
            type: "post",
            data: "BRNDCD=" + BRNDCD + "&CUSTCD=" + CUSTCD,
            url: $(".route-getListSmst").attr("href"),
            success: function (response) {
                html = "<option></option>";
                $.each(response, function (key, value) {
                    html += '<option value="' + value["CUSTCD"] + '">' + value["CUSTNM"] + '</option>';
                });
                PDCPCD.html(html);
            },
        });
    })

    // 依頼内容：物流会社
    // ・値変更時は車両種別、車両No：運転手の選択値をクリアし、車両種別、車両No：運転手の選択リストを再取得する
    $(document).on("change", ".PDCPCD", function () {
        CUSTCD = $("input[name=CUSTCD]").val();
        PDCPCD = $(this).val();
        BRNDCD = $(this).closest('tr').find(".BRNDCD").val();
        car_type = $(this).closest('tr').find(".car_type");
        car_type.html("");
        car_detail = $(this).closest('tr').find(".car_detail");
        car_detail.html("");
        recovered_on = $(this).closest('tr').find(".recovered_on").val();
        $.ajax({
            type: "post",
            data: "BRNDCD=" + BRNDCD + "&CUSTCD=" + CUSTCD + "&PDCPCD=" + PDCPCD + "&recovered_on=" + recovered_on,
            url: $(".route-getListCarTypeDetail").attr("href"),
            success: function (response) {
                html = "<option></option>";
                $.each(response["ListCar"], function (key, value) {
                    html += '<option value="' + value["id"] + '">' + value["title"] + '</option>';
                });
                car_detail.html(html);

                html = "<option></option>";
                $.each(response["ListCarType"], function (key, value) {
                    html += '<option value="' + value["enum_key"] + '">' + value["enum_value"] + '</option>';
                });
                car_type.html(html);
            },
        });
    })


    // 引取依頼書出力ボタン
    $(".btnPopupExport").on("click", function () {
        if (!$(".PDCPCDEExport").val()) {
            $(".mg-errors-popup").html("物流会社が入力されていません。");
            _wasPageCleanedUp = false;
        } else {
            $(".mg-errors-popup").html("");
            //画面の再表示
            const key = 'downloaded';
            var cnt = 0;
            const DownLoadCookie = setInterval(function () {
                //downloadedのCookie取得
                let cookieArr = document.cookie.split('; ');
                cookieArr.forEach(function (value) {
                    var content = value.split('=');
                    var name = content[0];
                    if (name == key) {
                        //downloadedのCookie削除
                        document.cookie = key + "=''; max-age=0";
                        clearInterval(DownLoadCookie);
                        window.location.replace($("input[name=redit]").val());
                    }
                });
                if (cnt > 20) {
                    clearInterval(DownLoadCookie);
                    window.location.replace($("input[name=redit]").val());
                };
                cnt++;
            }, 1000);
            _wasPageCleanedUp = true;
            $(this).attr("type", "submit");
            $(this).submit();
            $("#ExportModal").modal("hide");
        }
    })
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
        mg_tb_detail();
    })

    //行をコピーする
    $(document).on("click", ".btnCopy", function () {
        CUSTCD = $("input[name=CUSTCD]").val();
        trhmlt = "<tr>" + $(".tr-default").html() + "</tr>";
        $(this).closest('tr').after(trhmlt);
        var clickcol = $(this).closest('tr');
        var addcol = clickcol.next();
        BRNDCD = clickcol.find('.BRNDCD').val();
        addcol.find('.BRNDCD').val(BRNDCD);
        addcol.find('.recovered_on').val(clickcol.find('.recovered_on').val());
        addcol.find('.hoped_on').val(clickcol.find('.hoped_on').val());
        addcol.find('.information').val(clickcol.find('.information').val());
        // 物流会社の選択リストを再取得する
        $.ajax({
            type: "post",
            data: "BRNDCD=" + BRNDCD + "&CUSTCD=" + CUSTCD,
            url: $(".route-getListSmst").attr("href"),
            success: function (response) {
                html = "<option></option>";
                $.each(response, function (key, value) {
                    html += '<option value="' + value["CUSTCD"] + '">' + value["CUSTNM"] + '</option>';
                });
                addcol.find('.PDCPCD').html(html);
            },
        });
        setDateTimePicker();    //日付フォーマット（main.js関数をCall）
        setnohope();
        mg_tb_detail();
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
    form_disabled();
    function form_disabled() {
        $(".form-diabled input:not([type='hidden'])").attr("disabled", true);
        $(".form-diabled select").attr("disabled", true);
        $(".form-diabled textarea").attr("disabled", true);
        $(".form-diabled .temp").remove();
        $(".form-diabled .btnAdd").remove();
        $(".form-diabled .tb-detail .fa").remove();
        $(".form-diabled .tb-detail button").remove();
    }
    $("input").on("keydown", function (e) {
        // エンターキーだったら無効にする
        if (e.key === 'Enter') {
            event.preventDefault();
            return false;
        }
    });
    $(document).on("click", ".btnDelete", function () {
        $(this).closest("tr").remove();
        mg_tb_detail();
    })

    // 保存
    $(".btnTemp").on("click", function () {
        ErrMsgCheck = Validate(0);
        if (ErrMsgCheck.length === 0) {
            $(".mg-form").attr("action", $("input[name=redit_store]").val());
            $(this).attr("type", "submit");
            $(this).submit();
        } else {
            $("#MessageModal .modal-body").html(ErrMsgCheck.join("<br>"));
            $("#MessageModal").modal();
        }
    })

    // 引取依頼書出力
    $(".btnExport").on("click", function () {
        ErrMsgCheck = Validate(1);
        if (ErrMsgCheck.length === 0) {
            $(".mg-form").attr("action", $("input[name=redit_store]").val());
            $(this).attr("type", "submit");
            $(this).submit();
        } else {
            $("#MessageModal .modal-body").html(ErrMsgCheck.join("<br>"));
            $("#MessageModal").modal();
        }
    })
    // 受付ボタン
    $(document).on("click", ".btnRECEPT", function () {
        request_type = $("input[name=request_type]").val();
        k = $(this).closest('tr').index();
        ErrMsg = [];
        if (!$(this).closest('tr').find(".BRNDCD").val()) {
            ErrMsg.push(Emty.replace("$", "依頼内容の銘柄"));
        }
        PDCPCD = $(this).closest('tr').find(".PDCPCD").val();
        if (!PDCPCD) {
            ErrMsg.push(Emty.replace("$", "依頼内容の物流会社"));
        }
        // 持込の時に「受付」を行った場合、回収日が未入力なら以下エラーメッセージを表示する
        if (request_type == 1 && !$(this).closest('tr').find(".recovered_on").val()) {
            ErrMsg.push(Emty.replace("$", "依頼内容の回収日"));
        }
        if (ErrMsg.length === 0) {
            // 依頼種別が1(持込)か物流会社が自社の場合、「受付を行いますか？」と表示
            if (PDCPCD in listCarWherePDCPCDList || request_type == 1) {
                Msg = "受付を行いますか？";
            } else {
                Msg = "庸車ですが引取依頼書の出力を行わずに、受付を行いますか？";
            }
            $("#ConfirmModal .modal-body").html(Msg);
            $("#ConfirmModal .btnPopupOk").attr("data-btn-action", "ajax");
            $("#ConfirmModal .btnPopupOk").attr("data-btn", "RECEPT");
            $("#ConfirmModal .btnPopupOk").attr("data-btn-k", k);
            $("#ConfirmModal").modal();
        } else {
            $("#MessageModal .modal-body").html(ErrMsg.join("<br>"));
            $("#MessageModal").modal();
        }
    })

    // －以下条件の全てに合致した場合「依頼内容の車両No：運転手が入力されていません。」と表示	
    // ・表示している依頼データの依頼種別が0（引取）
    // ・クリックした行の物流会社が自社（※１）
    // ・クリックした行の車両No：運転手が未選択
    function checkCarDetail(car_detail, PDCPCD) {
        if ($("input[name=request_type]").val() == 0 && (PDCPCD in listCarWherePDCPCDList) && !car_detail) {
            return true;
        }
        return false;
    }
    var CarDetailErrMsg = "依頼内容の車両No：運転手";
    // 配車ボタン
    $(document).on("click", ".btnDISP", function () {
        k = $(this).closest('tr').index();
        ErrMsg = [];
        PDCPCD = $(this).closest('tr').find(".PDCPCD").val();
        if (!PDCPCD) {
            ErrMsg.push(Emty.replace("$", "依頼内容の物流会社"));
        }
        if (!$(this).closest('tr').find(".recovered_on").val()) {
            ErrMsg.push(Emty.replace("$", "依頼内容の回収日"));
        }
        if (checkCarDetail($(this).closest('tr').find(".car_detail").val(), PDCPCD)) {
            ErrMsg.push(Emty.replace("$", CarDetailErrMsg));
        }
        if (ErrMsg.length === 0) {
            Msg = "配車を行いますか？";
            $("#ConfirmModal .modal-body").html(Msg);
            $("#ConfirmModal .btnPopupOk").attr("data-btn-action", "ajax");
            $("#ConfirmModal .btnPopupOk").attr("data-btn", "DISP");
            $("#ConfirmModal .btnPopupOk").attr("data-btn-k", k);
            $("#ConfirmModal").modal();
        } else {
            $("#MessageModal .modal-body").html(ErrMsg.join("<br>"));
            $("#MessageModal").modal();
        }
    })

    // 確認ボタン
    $(document).on("click", ".btnCONFIRM", function () {
        k = $(this).closest('tr').index();
        ErrMsg = [];
        PDCPCD = $(this).closest('tr').find(".PDCPCD").val();
        if (!PDCPCD) {
            ErrMsg.push(Emty.replace("$", "依頼内容の物流会社"));
        }
        // －クリックした行の物流会社が自社（※１）の時に車両No：運転手が未選択：「依頼内容の車両No：運転手が入力されていません。」と表示
        if (checkCarDetail($(this).closest('tr').find(".car_detail").val(), PDCPCD)) {
            ErrMsg.push(Emty.replace("$", CarDetailErrMsg));
        }
        if (ErrMsg.length === 0) {
            Msg = "確認を行いますか";
            $("#ConfirmModal .modal-body").html(Msg);
            $("#ConfirmModal .btnPopupOk").attr("data-btn-action", "ajax");
            $("#ConfirmModal .btnPopupOk").attr("data-btn", "CONFIRM");
            $("#ConfirmModal .btnPopupOk").attr("data-btn-k", k);
            $("#ConfirmModal").modal();
        } else {
            $("#MessageModal .modal-body").html(ErrMsg.join("<br>"));
            $("#MessageModal").modal();
        }
    })

    // 受入ボタン
    $(document).on("click", ".btnACCEPT", function () {
        k = $(this).closest('tr').index();
        ErrMsg = [];
        PDCPCD = $(this).closest('tr').find(".PDCPCD").val();
        if (!PDCPCD) {
            ErrMsg.push(Emty.replace("$", "依頼内容の物流会社"));
        }
        if (checkCarDetail($(this).closest('tr').find(".car_detail").val(), PDCPCD)) {
            ErrMsg.push(Emty.replace("$", CarDetailErrMsg));
        }
        if (ErrMsg.length === 0) {
            Msg = "受入を行いますか";
            $("#ConfirmModal .modal-body").html(Msg);
            $("#ConfirmModal .btnPopupOk").attr("data-btn-action", "ajax");
            $("#ConfirmModal .btnPopupOk").attr("data-btn", "ACCEPT");
            $("#ConfirmModal .btnPopupOk").attr("data-btn-k", k);
            $("#ConfirmModal").modal();
        } else {
            $("#MessageModal .modal-body").html(ErrMsg.join("<br>"));
            $("#MessageModal").modal();
        }
    })
    // 取差戻ボタン
    $(document).on("click", ".btnREVERT", function () {
        k = $(this).closest('tr').index();
        ErrMsg = [];
        PDCPCD = $(this).closest('tr').find(".PDCPCD").val();
        if (!PDCPCD) {
            ErrMsg.push(Emty.replace("$", "依頼内容の物流会社"));
        }
        if (checkCarDetail($(this).closest('tr').find(".car_detail").val(), PDCPCD)) {
            ErrMsg.push(Emty.replace("$", CarDetailErrMsg));
        }
        if (ErrMsg.length === 0) {
            Msg = "取差戻を行いますか";
            $("#ConfirmModal .modal-body").html(Msg);
            $("#ConfirmModal .btnPopupOk").attr("data-btn-action", "ajax");
            $("#ConfirmModal .btnPopupOk").attr("data-btn", "REVERT");
            $("#ConfirmModal .btnPopupOk").attr("data-btn-k", k);
            $("#ConfirmModal").modal();
        } else {
            $("#MessageModal .modal-body").html(ErrMsg.join("<br>"));
            $("#MessageModal").modal();
        }
    })

    // 戻るボタン
    $(".btnBack").on("click", function () {
        $("#ConfirmModal .modal-body").html("依頼検索画面に戻りますか？");
        $("#ConfirmModal .btnPopupOk").attr("data-btn", "back");
        $("#ConfirmModal").modal();
    })
    // 【削除】ボタン
    $(".btnDelele").on("click", function () {
        $("#ConfirmModal .modal-body").html("削除しますか？");
        $("#ConfirmModal .btnPopupOk").attr("data-btn", "delete");
        $("#ConfirmModal").modal();
    })
    $(".btnPopupOk").on("click", function () {
        if ($(this).attr("data-btn") == "back") {
            _wasPageCleanedUp = false;
            window.location.href = $(".btnBack").attr("href");
        }
        if ($(this).attr("data-btn-action") == "ajax") {
            _wasPageCleanedUp = true;
            // はいボタン
            $(".mg-form").attr("action", $("input[name=redit_ajax]").val());
            $(".btnAjax").val($(this).attr("data-btn"));
            $(".btnAjaxRow").val($(this).attr("data-btn-k"));
            $(".mg-form").submit();
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


    // 引取依頼書出力ボタン：check = 1
    function Validate(check) {
        ErrMsg = [];
        var BRNDCD = $('.BRNDCD').map(function () {
            return this.value;
        }).get();
        var status = $('.status').map(function () {
            return this.value;
        }).get();
        var PDCPCD = $('.PDCPCD').map(function () {
            return this.value;
        }).get();
        var car_detail = $('.car_detail').map(function () {
            return this.value;
        }).get();
        flgBRNDCD = false;
        flgPDCPCD = false;
        flgCarWherePDCPCDList = false;
        flgCheckCarWherePDCPCDList = true;

        $.each(BRNDCD, function (index) {
            if (index > 0) {
                if (PDCPCD[index] == "" || !PDCPCD[index]) {
                    flgPDCPCD = true;
                }
                if (!BRNDCD[index]) {
                    flgBRNDCD = true;
                }
                if (check == 1) {
                    if (PDCPCD[index] && !(PDCPCD[index] in listCarWherePDCPCDList)) {
                        // 画面上の物流会社に自社（※１）以外が存在しない場合：「出力対象が存在しません。」と表示
                        flgCheckCarWherePDCPCDList = false;
                    }
                }
                // －依頼内容の物流会社が自社（※１）で状態が一時、受付以外の場合に車両が未選択：「自社の場合は車両を選択して下さい。」と表示
                if (status[index] != $("input[name=TEMP]").val() && status[index] != $("input[name=RECEPT]").val()) {
                    if (checkCarDetail(car_detail[index], PDCPCD[index])) {
                        flgCarWherePDCPCDList = true;
                    }
                }
            }
        })

        if (flgBRNDCD) {
            ErrMsg.push(Emty.replace("$", "依頼内容の銘柄"));
        }
        if (flgPDCPCD) {
            ErrMsg.push(Emty.replace("$", "依頼内容の物流会社"));
        }
        if (flgCarWherePDCPCDList) {
            ErrMsg.push("自社の場合は車両を選択して下さい。");
        }
        if (BRNDCD.length == 1) {
            ErrMsg.push("依頼内容が存在しません。");
        }
        if (flgCheckCarWherePDCPCDList && check == 1) {
            ErrMsg.push("出力対象が存在しません。");
        }
        return ErrMsg;
    }


    // 依頼内容：回収日
    // ・値変更時は車両No：運転手の選択値をクリアし、車両No：運転手の選択リストを再取得する
    $(document).on("dp.change", ".recovered_on", function () {
        CUSTCD = $("input[name=CUSTCD]").val();
        PDCPCD = $(this).closest('tr').find(".PDCPCD").val();
        BRNDCD = $(this).closest('tr').find(".BRNDCD").val();
        car_detail = $(this).closest('tr').find(".car_detail");
        car_detail.html("");
        recovered_on = $(this).val();
        if (recovered_on) {
            $.ajax({
                type: "post",
                data: "BRNDCD=" + BRNDCD + "&CUSTCD=" + CUSTCD + "&PDCPCD=" + PDCPCD + "&recovered_on=" + recovered_on,
                url: $(".route-getListCarTypeDetail").attr("href"),
                success: function (response) {
                    html = "<option></option>";
                    $.each(response["ListCar"], function (key, value) {
                        html += '<option value="' + value["id"] + '">' + value["title"] + '</option>';
                    });
                    car_detail.html(html);
                },
            });
        }
    })
    mg_tb_detail();
    function mg_tb_detail() {
        $(".mg-tb-detail").height($(".tb-detail").outerHeight() + 30);
    }

    // 依頼内容のカレンダーの全体が表示する
    // 希望日	
    $(document).on('focusin', '.hoped_on:not([readonly])', function () {
        $('.mg-tb-detail').css('overflow-x', "visible");
        mg_tb_detail();
    }).on('focusout', '.hoped_on:not([readonly])', function () {
        $('.mg-tb-detail').css('overflow-x', "auto");
        mg_tb_detail();
    });
    // 回収日
    $(document).on('focusin', '.recovered_on:not([readonly])', function () {
        $('.mg-tb-detail').css('overflow-x', "visible");
        mg_tb_detail();
    }).on('focusout', '.recovered_on:not([readonly])', function () {
        $('.mg-tb-detail').css('overflow-x', "auto");
        mg_tb_detail();
    });

    // 画面解放時
    var _wasPageCleanedUp = false;
    $(".btnlock").on("click", function () {
        _wasPageCleanedUp = true;
    })

    function pageCleanup() {
        if (!_wasPageCleanedUp && !$(".mg-form").hasClass("form-diabled")) {
            _wasPageCleanedUp = true;
            // 依頼検索画面展開時のunload
            Cookies.set('_wasPageCleanedUp', true);

            url = $("input[name=rlockuser]").val() + "?id=" + $("input[name=request_id]").val();
            $.get(url, function () { });
        }
    }

    $(window).bind('beforeunload', function () {
        //this will work only for Chrome
        pageCleanup();
    });

    $(window).bind("unload", function () {
        //this will work for other browsers
        pageCleanup();
    });
    $(".modal .btn-danger").on("click", function () {
        _wasPageCleanedUp = false;
    })
})