$(document).ready(function() {
    $(".btn-bars").on("click", function() {
        $("nav.menu-list").addClass("active");
        $(this).hide();
        $(".btn-close").show();
    })
    $(".btn-close").on("click", function() {
        $("nav.menu-list").removeClass("active");
        $(this).hide();
        $(".btn-bars").show();
    })
    $(document).on('mousedown', 'body', function(e) {
        var target = $(e.target);
        if (!target.is('nav.menu-list') && !target.is('i.fa.fa-bars') && !target.is('label.btn-bars.cursor-pointer.m-0') && !target.is('nav.menu-list li') && !target.is('nav.menu-list li span')) {
            $(".btn-bars").show();
            $(".btn-close").hide();
            $("nav.menu-list").removeClass("active");
        }
        if (!target.is('.rightclick li')) {
            $(".rightclick").hide();
        }
    });

})