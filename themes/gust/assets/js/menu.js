jQuery(function ($) {
  let searchOpen = false;

  function toggleMenu() {
    $(".gust-mobile-nav").toggleClass(
      "gust-mobile-nav--open gust-mobile-nav--closed"
    );
  }

  function toggleSearch() {
    $(".gust-search").toggleClass("gust-search--open gust-search--closed");
    searchOpen = !searchOpen;
    if (searchOpen) {
      $(".gust-search [name='s']").focus();
    }
  }

  $(".toggle-m-nav").on("click", function () {
    toggleMenu();
  });

  $(".gust-toggle-search").on("click", function () {
    toggleSearch();
  });

  $(document).on("keyup", function (e) {
    if (e.key === "Escape" && searchOpen) {
      toggleSearch();
    }
  });
});
