$(function () {
    $(".bg-menu ul li").click(function () {
        var url = $(this).find("img")[0].src;
        var index = $(this).index();
        $(".header").css("background-position",index * document.body.clientWidth);
        $(".header").css("background-image","url(" + url + ")");
    })
})