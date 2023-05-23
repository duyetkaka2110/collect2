$(document).ready(function() {
    if ($(".form").hasClass("form-disabled")) {
        $(".form-disabled input").attr("disabled", true);
    } else {
        dragEvents();
        btnno = 0;
        $(".btnAdd").on("click", function() {
            btnno++;
            tr = '<tr class="tr-input">'
            tr += '<td class="text-center">'
            tr += '<i class="fa fa-th-list cursor-move font-17px pr-1" aria-hidden="true"></i>'
            tr += ' </td>'
            tr += '<td colspan="2">'
            tr += ' <input type="hidden" value="0" name="dispatch_detail_id[' + btnno + ']" />'
            tr += ' <input type="hidden" value="0" class="sort_no" name="sort_no[' + btnno + ']"/>'
            tr += '    <input name="instruct[' + btnno + ']" value="" maxlength="1024" type="text" class="form-control instruct input-custom" />'
            tr += '</td>'
            tr += '<td class="text-center"><i class="fa fa-trash cursor-pointer font-17px btnDel" title="削除" aria-hidden="true"></i></td>'
            tr += '</tr>';
            $(".tr-input:last").after(tr)
        })
        $(document).on("click", ".btnDel", function() {
            $(this).closest('tr').remove();
        })
    }
    $(".btnCancel").on("click", function() {
        $(".btnPopupOk").attr("data-btn", "cancel");
        $("#ConfirmModal .modal-body").html("キャンセルしますか？");
        $("#ConfirmModal").modal();
    })
    $(".btnOk").on("click", function() {
        flag = true;
        $(".instruct").each(function(idx, val) {
            if (!$(this).val()) {
                flag = false;
                return;
            }
        });
        if (!flag) {
            $("#MessageModal .modal-body").html("指示が入力されていない行が存在します。");
            $("#MessageModal").modal();
        } else {
            $(".btnPopupOk").attr("data-btn", "save");
            $("#ConfirmModal .modal-body").html("保存しますか？");
            $("#ConfirmModal").modal();
            $(".sort_no").each(function(idx) {
                // set calendar sort
                $(this).val(idx + 1)
            });
        }
    })
    $(".btnPopupOk").on("click", function() {
        if ($(".btnPopupOk").attr("data-btn") == "save") {
            $(".btnOk").attr("type", "submit");
            $(".btnOk").click();
        }
        if ($(".btnPopupOk").attr("data-btn") == "cancel") {
            window.location.href = $(".btnBack").attr("href");
        }
    })

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
                items: "tr.tr-input",
                placeholder: 'ui-state-highlight',
                stop: function(event, ui) {
                    //update sort
                    countitem = ui.item.parent().find("tr.tr-input").length;
                    if (countitem > 1) {
                        ui.item.parent().find(".sort_no").each(function(idx) {
                            // set calendar sort
                            $(this).val(idx + 1)
                        });
                    }
                },
            })
            .disableSelection();

        var $tab_items = $(".nav-tabs > li", $tabs).droppable({
            accept: ".connectedSortable p",
            hoverClass: "ui-state-hover",

            drop: function(event, ui) {
                return false;
            }
        });
    }
})