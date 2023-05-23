$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    beforeSend: function () {
        $('.loading').removeClass('d-none');
    },
    complete: function () {
        $('.loading').addClass('d-none');
    },
    error: function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR.status);
        console.log(textStatus);
        if (jqXHR.status == 419) {
            location.reload();
        }
    }
});

//日付入力項目のセットアップ
//　日付項目の存在する各画面用のJavaScriptでCall
function setDateTimePicker() {
    //日付フォーマット
    $('.datetimepicker').datetimepicker({
        format: 'YYYY/MM/DD',
        dayViewHeaderFormat: 'YYYY年MM月'
    });

    //日付フォーマット（日曜選択不可）
    $('.datenotsun').datetimepicker({
        format: 'YYYY/MM/DD',
        dayViewHeaderFormat: 'YYYY年MM月',
        useCurrent: false, //daysOfWeekDisabledを設定すると入力域に現在日付が自動的セットされてしまう為、本オプションを追加
        daysOfWeekDisabled: [0]
    });

    //日曜日が入力された場合、元の値に戻る
    $(".datenotsun").on("focusin", function (e) {
        //変更前の値を保持
        $(this).data('val', $(this).val());
    })
    $(".datenotsun").on("dp.change", function (e) {
        var oldval = $(this).data('val');
        var chkstr = $(this).val();
        if (chkstr.length > 0) {
            var dateval = new Date(chkstr);
            if (dateval.getDay() == 0) {
                //日曜なら元の値に戻す
                $(this).val(oldval);
            }
        }
    })
}

$(document).on("click", "nav a.relative", function (e) {
    e.preventDefault();
    url = $(this).attr("href");
    page = getUrlParameter(url, "page");
    if (!$('.page_no').length) {
        $(".formbtn").append("<input class='page_no' name='page' value='" + page + "' type='hidden' >");
    } else {
        $(".page_no").val(page);
    }
    $(".btnSearch").click();
}) 
// ページ番号を取得する
function getUrlParameter(url, sParam) {
    var sPageURL = url.split('?')[1],
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
    return 0;
};