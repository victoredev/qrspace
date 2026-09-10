jQuery(function ($) {
  if (typeof Swiper !== "undefined") {
    new Swiper(".gust-slider", {
      loop: true,
      pagination: {
        el: ".swiper-pagination",
      },
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
    });
  }
});
