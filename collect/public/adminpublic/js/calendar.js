$(document).ready(function () {
    //日付フォーマット（main.js関数をCall）
    setDateTimePicker();

    // ・クリックしたセルの手配日＋車両の手配編集画面に展開する
    $(document).on("dblclick", ".sales-all", function () {
        window.location.href = $(".route-dedit").attr("href") + "/" + $(this).attr("data-id");
    })
    // ・クリックした手配詳細が「依頼内容ID」がNULLの場合、クリックしたセルの手配日＋車両の手配編集画面に展開する
    // ・クリックした手配詳細が「依頼内容ID」がNULL以外の場合、「依頼内容ID」に合致する依頼内容の依頼編集画面に展開する
    $(document).on("dblclick", ".table-drag .event", function () {
        request_detail_id = $(this).attr("request_detail_id");
        if (request_detail_id == "null" || request_detail_id == "") {
            window.location.href = $(".route-dedit").attr("href") + "/" + $(this).attr("data-id");
        }
        if (request_detail_id != "null" && request_detail_id != "") {
            window.location.href = $(".route-redittest").attr("href") + "?request_detail_id=" + request_detail_id;
        }
    })

    // 表示更新ボタン
    $(".btnSearch").on("click", function () {
        ErrMsgCheck = Validate();
        if (ErrMsgCheck.length === 0) {
            $(this).attr("type", "submit");
        } else {
            // ・以下条件のいずれかに該当した場合、エラーメッセージを表示して処理を中止
            $("#MessageModal .modal-body").html(ErrMsgCheck.join("<br>"));
            $("#MessageModal").modal();
        }
    })

    function Validate() {
        Emty = "$が入力されていません。";
        ErrMsg = [];
        if (!$(".datestart").val()) {
            ErrMsg.push(Emty.replace("$", "表示範囲（From）"));
        }
        if (!$(".dateend").val()) {
            ErrMsg.push(Emty.replace("$", "表示範囲（To）"));
        }
        if (moment($(".datestart").val()) > moment($(".dateend").val())) {
            ErrMsg.push("表示範囲が逆転しています。");
        }
        return ErrMsg;
    }
    moment.locale("ja", {
        weekdaysShort: ["日", "月", "火", "水", "木", "金", "土"]
    });
    dragEvents();

    function dragEvents() {
        count = 0;
        // drag and drop all table
        var $tabs = $('.table-drag');
        $(".connectedSortable")
            .sortable({
                connectWith: ".connectedSortable",
                appendTo: $tabs,
                zIndex: 999990,
                cursor: "move",
                items: "> p:not(.stop)",
                placeholder: 'ui-state-highlight',
                stop: function (event, ui) {
                    oldcell = $(this);
                    eventid = ui.item.attr("data-event");
                    oldday = $(this).attr("data-day");
                    newday = ui.item.parent().attr("data-day");
                    oldres = $(this).attr("data-resource");
                    newres = ui.item.parent().attr("data-resource");
                    dataupdate = {}; // this event change data
                    sortupdate = []; // this column sort change
                    // change column
                    if ((oldday != newday) || (oldres != newres)) {
                        // when change -> Ajax update DB
                        dataupdate["id"] = eventid;
                        dataupdate["day"] = newday;
                        dataupdate["resourceID"] = newres;
                        dataupdate["oldday"] = oldday;
                        dataupdate["oldresourceID"] = oldres;

                        //update sort
                        countitem = ui.item.parent().find("p:not(.stop,.p-hide)").length;
                        if (countitem > 1) {
                            ui.item.parent().find("p:not(.stop,.p-hide)").each(function (idx) {
                                // set calendar sort
                                $(this).attr("data-sort", idx + 1)

                                // get data new sort  to update DB 
                                eid = $(this).attr("data-event");
                                if (eid) {
                                    sortupdate[eid] = {};
                                    sortupdate[eid]["id"] = eid;
                                    sortupdate[eid]["sort"] = idx + 1;
                                }
                            });
                        }
                    }
                    // change on one cell
                    if ((oldday == newday) && (oldres == newres)) {
                        // check sort 
                        oldsort = ui.item.attr("data-sort");
                        countitem = ui.item.parent().find("p:not(.stop,.p-hide)").length;
                        if (countitem > 1) {
                            //update sort
                            ui.item.parent().find("p:not(.stop,.p-hide)").each(function (idx) {
                                // set calendar sort
                                $(this).attr("data-sort", idx + 1)

                                // get data new sort  to update DB 
                                eid = $(this).attr("data-event");
                                if (eid) {
                                    sortupdate[eid] = {};
                                    sortupdate[eid]["id"] = eid;
                                    sortupdate[eid]["sort"] = idx + 1;
                                }
                            });
                            newsort = ui.item.attr("data-sort");
                            if (oldsort == newsort) {
                                // when not change -> remove data
                                sortupdate = [];
                            }
                        }
                    }
                    // update event in DB
                    if ("id" in dataupdate || sortupdate.length) {
                        status_id = ui.item.attr("data-status");
                        noweventui = ui;
                        // 状態が「顧客」「確定」の時は、日付（縦軸）が違う場合
                        if ((oldday != newday) && (status_id == $('input[name="RDStatus_CUSTOMER"]').val() || status_id == $('input[name="RDStatus_CONFIRM"]').val())) {
                            nowevent = $(this);
                            $("#ConfirmModal .modal-body p").html("顧客に回収日を確認中（もしくは確定済み）です。　<br>それでも回収日を変更しますか？");
                            $("#ConfirmModal").modal();
                        } else {
                            updateDB(noweventui);
                        }
                    }
                },
            })
            .disableSelection();

        var $tab_items = $(".nav-tabs > li", $tabs).droppable({
            accept: ".connectedSortable p",
            hoverClass: "ui-state-hover",

            drop: function (event, ui) {
                return false;
            }
        });
    }
    $.event.special.rightclick = {
        bindType: "contextmenu",
        delegateType: "contextmenu"
    };

    $("#ConfirmModal .modal-footer .btnPopupOk").on("click", function () {
        updateDB(noweventui);
    })
    $("#ConfirmModal .modal-footer .btn-danger").on("click", function () {
        nowevent.sortable('cancel');
    })
    $(document).on("click", "#MessageModal .modal-footer .btn-reload",function () {
        location.reload();
    })
    
    //　イベント情報を更新する
    function updateDB(ui) {
        $.ajax({
            type: "post",
            data: {
                dataupdate: dataupdate,
                sortupdate: sortupdate
            },
            url: $(".route-dupdate").attr("href"),
            success: function (response) {
                if (response != "" && response != "sales") {
                    $("#MessageModal .modal-body").html(response);
                    $("#MessageModal .modal-footer button").addClass("btn-reload");
                    $("#MessageModal").modal();
                    $(".loading").addClass("d-none");
                    nowevent.sortable('cancel');
                } else {
                    ui.item.parent().find(".sales-all").addClass(response);
                    ui.item.parent().addClass("td-sale");
                    ui.item.parent().addClass("td_rightclick");
                    oldcell.find(".sales-all").addClass("sales");
                    countitem = oldcell.find("p.event").length;
                    if (countitem == 0) {
                        oldcell.find(".sales").removeClass("sales");
                    }
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.info(jqXHR["responseText"]);
            }
        });
    }
    // カレンダー一覧：セル		右クリック
    $(document).on("rightclick", ".td_rightclick:not(.no-drag) .sales-all", function (event) {
        $(".rightclick").css({
            left: event.clientX,
            top: event.clientY,
            display: 'block'
        });
        $("#ConfirmModal2 .select-date").html($(this).parent().attr("data-day"));
        $("#ConfirmModal2 .new-resource").val("");
        $("#ConfirmModal2 .select_resource").val($(this).parent().attr("data-resource"));
        $("#ConfirmModal2 .select-data-id").val($(this).attr("data-id"));
        return false;
    });
    $(".rightclick li").on("click", function () {
        // 日付
        date = moment($("#ConfirmModal2 .select-date").html()).format("YYYY年MM月DD日 (ddd)");
        $("#ConfirmModal2 .select-date").html(date);
        // 元
        resourceid = $("#ConfirmModal2 .select_resource").val();
        $("#ConfirmModal2 .select-title").html($("." + resourceid).html());

    })
    // 手配移動
    $(".rightclick .move").on("click", function () {
        prefix = "移動";
        $(".rightclick").hide();
        $(".error-show").html("");
        $("#ConfirmModal2 .modal-title").html("手配" + prefix);
        $("#ConfirmModal2 .btnPopup").html("手配を" + prefix);
        $("#ConfirmModal2 .btnPopupOk").val("move");
        $("#ConfirmModal2 .tdprv").html(prefix + "元");
        $("#ConfirmModal2 .tdnext").html(prefix + "先");
        $("#ConfirmModal2 .arrowclass").removeClass("fa-exchange");
        $("#ConfirmModal2 .arrowclass").addClass("fa-long-arrow-down");
        $("#ConfirmModal2").modal();
    })
    // 手配入替
    $(".rightclick .replace").on("click", function () {
        prefix = "入替";
        $(".rightclick").hide();
        $(".error-show").html("");
        $("#ConfirmModal2 .modal-title").html("手配" + prefix);
        $("#ConfirmModal2 .btnPopup").html("手配を" + prefix);
        $("#ConfirmModal2 .btnPopupOk").val("replace");
        $("#ConfirmModal2 .tdprv").html(prefix + "元");
        $("#ConfirmModal2 .tdnext").html(prefix + "先");
        $("#ConfirmModal2 .arrowclass").addClass("fa-exchange");
        $("#ConfirmModal2 .arrowclass").removeClass("fa-long-arrow-down");
        $("#ConfirmModal2").modal();
    })
    $(".btnPopup").on("click", function () {
        $.ajax({
            type: "post",
            data: $("#form-move-replace").serialize(),
            url: $(".route-dvali").attr("href"),
            success: function (response) {
                if (!response) {
                    $(".btnPopupOk").click();
                } else {
                    $(".error-show").html(response);
                }
            },
        });
    })
})