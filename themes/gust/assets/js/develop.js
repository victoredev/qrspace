jQuery(function ($) {
  // set up a mutation observer
  // to watch for changes to class names etc
  // but only change when class names change
  var config = {
    attributes: true,
    attributeFilter: ["class"],
    childList: true,
    subtree: true,
  };

  var lastProcessed = "";
  var observerTo = 0;

  function observer() {
    window.clearTimeout(observerTo);
    observerTo = window.setTimeout(function () {
      var docHTML = document.documentElement.outerHTML;
      if (lastProcessed === docHTML) {
        return;
      }
      lastProcessed = docHTML;

      window.GustWorker.compile({
        config: window.Gust.config,
        safelist: window.Gust.safelist,
        css: window.Gust.css,
        tailwindVersion: window.Gust.tailwindVersion,
        defaultHTML: docHTML,
      })
        .then((css) => {
          const style = $("#site-gust-dev-css");
          const newStyle = document.createElement("style");
          newStyle.id = "site-gust-dev-css";
          newStyle.innerHTML = css;
          style.replaceWith(newStyle);
        })
        .catch((e) => {
          console.log(e);
          alert("There was an error building your CSS file");
        });
    }, 500);
  }

  new MutationObserver(observer).observe(document.documentElement, config);

  observer();
});
