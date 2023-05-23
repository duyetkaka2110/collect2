$(document).ready(function () {
    //日付フォーマット（main.js関数をCall）
    setDateTimePicker();

    setScroll();
    $(window).resize(function () {
        setScroll();
    });

    function setScroll() {
        scrollheight = $(window).outerHeight() - ($("header").outerHeight() + $(".top-table").outerHeight() + $(".title-header").outerHeight() + $("nav.flex").outerHeight() + 60);
        if ($(".table-history tbody").outerHeight() < scrollheight) {
            $(".table-history").css("overflow-y", "unset");
        }
    }

    // 状態のチェックON/OFFを操作する「全チェック」「全クリア」【受付のみ表示】、【配車のみ表示】ボタン
    $(".btnSetSatusCheckBox").click(function () {
        datavalue = $(this).attr("data-value");
        if ($(this).hasClass("all")) {
            setStatusAllCheckbox(parseInt(datavalue));
        } else {
            // 【受付のみ表示】、【配車のみ表示】ボタン
            // それ以外を未チェックとする
            setStatusAllCheckbox(false);
            // 状態をチェック
            $('.checkbox-' + datavalue).prop('checked', true);
        }
    });

    function setStatusAllCheckbox(flag) {
        $('.status-checkbox').prop('checked', flag);
    }

    // 表示行数
    $('input[name="line_no"]').keyup(function (e) {
        // Filter non-digits from input value.
        this.value = this.value.replace(/[^0-9\.]/g, '');
        if (this.value > 999) {
            this.value = this.value.substring(0, 3);
        }
    });
    _wasPageCleanedUp = Cookies.get('_wasPageCleanedUp');
    if (_wasPageCleanedUp == "true") {
        // 依頼編集画面から展開時のunload
        Cookies.set('_wasPageCleanedUp', null);
        location.reload();
    }
    // 検索条件表示
    arrow = Cookies.get('arrow');
    if (arrow != undefined) {
        hideSearch(arrow);
    }
    $(".btn-icon-search").on("click", function () {
        if ($(this).hasClass("fa-chevron-circle-down")) {
            //非表示
            hideSearch("up");
        } else {
            //表示
            hideSearch("down");
        }
        tableResize();
    })
    $(window).on('resize', tableResize);
    tableResize();

    // 表示設定変更画面を閉じて再表示した場合、最新テーブルの情報で表示される
    $(".btnSetting").on("click", function () {
        $.ajax({
            type: "post",
            url: $(".route-rgetsetting").val(),
            success: function (response) {
                $("input[name=line_no]").val(20);
                $('.user-setting input[type=checkbox]').prop("checked", true);
                if (response) {
                    // 表示行数
                    $("input[name=line_no]").val(response["line_no"] ? response["line_no"] : 20);
                    // 列の表示／非表示
                    $.each(response["hidden_item"], function (key, value) {
                        $('.user-setting input[type=checkbox][value="' + key + '"]').prop("checked", false);
                    });
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.info(jqXHR["responseText"]);
            }
        });
    });

    function hideSearch(arrow) {
        if (arrow == "up") {
            $(".btn-icon-search").removeClass("fa-chevron-circle-down");
            $(".btn-icon-search").addClass("fa-chevron-circle-up");
            $(".mg-search").addClass("p-2");
            $(".search_item").show();
        } else {
            $(".btn-icon-search").removeClass("fa-chevron-circle-up");
            $(".btn-icon-search").addClass("fa-chevron-circle-down");
            $(".mg-search").removeClass("p-2");
            $(".search_item").hide();
        }
        Cookies.set('arrow', arrow);
    }

    function tableResize() {
        h = $(window).height() - ($("header").outerHeight() + $(".top-table").outerHeight() + 90);
        $(".wrapper").css("max-height", h)
    }
})