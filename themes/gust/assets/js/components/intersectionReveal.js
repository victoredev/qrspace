jQuery(function ($) {
  function intersectionCallback(entries) {
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) return;
      var el = $(entry.target);
      var classToRemove = el
        .data("gust-reveal-class")
        .split(" ")
        .map(function (cname) {
          return cname.trim();
        });
      el.removeClass(classToRemove);
    });
  }
  var observer = new IntersectionObserver(intersectionCallback, {
    threshold: 0.5,
  });
  $("[data-gust-reveal-class]").each(function (_i, el) {
    observer.observe(el);
  });
});
