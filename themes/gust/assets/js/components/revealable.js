jQuery(function ($) {
  $(".gust-revealable").on("click", ".gust-revealable-trigger", function (e) {
    e.preventDefault();
    var el = $(this);
    // check if this has a parent
    var hasParent = el.closest(".gust-revealable-trigger-iterable");
    var iterator = hasParent.length ? hasParent : el;

    // get the top most parent
    var repeatableParent = el.closest(".gust-revealable");

    // remove the active class name
    repeatableParent.find(".gust-active-item").removeClass("gust-active-item");

    // add the active class to the trigger
    iterator.addClass("gust-active-item");

    // add the active class to the item itself
    repeatableParent
      .find(".gust-revealable-target")
      .eq(iterator.index())
      .addClass("gust-active-item");

    // blur the clicked element to remove focus
    el.blur();
  });
});
