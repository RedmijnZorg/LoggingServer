$(function () {
    function handleResponsive() {
        if (window.matchMedia("(max-width: 991px)").matches) {
            // Your jQuery logic for small screens
            $(".sub_menu_drop > a").off().on("click", function (e) {
                e.preventDefault();
                $(this).next(".submenu").slideToggle();
            });

            $(".submenu .close").off().on("click", function (e) {
                e.stopPropagation();
                $(this).closest(".submenu").slideUp();
            });

            $(".header__hamburger").off().on("click", function () {
                $("nav").toggleClass("active");
            });

            $(".close").off().on("click", function () {
                $("nav").removeClass("active");
            });
        }
    }

    // Run on page load
    handleResponsive();

    // Run on window resize
    $(window).on("resize", handleResponsive);
});