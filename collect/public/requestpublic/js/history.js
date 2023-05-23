$(document).ready(function() {
    //日付フォーマット（main.js関数をCall）
    setDateTimePicker();

    setScroll();
    $(window).resize(function() {
        setScroll();
    });

    function setScroll() {
        scrollheight = $(window).outerHeight() - ($("header").outerHeight() + $(".top-table").outerHeight() + $(".title-header").outerHeight() + $("nav.flex").outerHeight() + 60);
        if ($(".table-history tbody").outerHeight() < scrollheight) {
            $(".table-history").css("overflow-y", "unset");
        }
    }

    // 状態のチェックON/OFFを操作する「全チェック」「全クリア」ボタン
    $(".btnSetSatusCheckBox").click(function () {
        datavalue = $(this).attr("data-value");
        setStatusAllCheckbox(parseInt(datavalue));
    });

    function setStatusAllCheckbox(flag) {
        $('.status-checkbox').prop('checked', flag);
    }
})