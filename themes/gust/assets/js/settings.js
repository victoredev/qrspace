jQuery(function ($) {
  var rebuildState = {
    loading: false,
  };
  const setRebuildLoading = (loading) => {
    var btn = $("#gust-rebuild-css");
    if (loading) {
      rebuildState.loading = true;
      btn.text("Building...");
    } else {
      rebuildState.loading = false;
      btn.text("Done!");
      window.setTimeout(() => {
        btn.text("Rebuild CSS");
      }, 3000);
    }
  };

  const saveCss = (css) => {
    $.ajax({
      type: "post",
      url: window.Gust.adminUrl,
      data: {
        action: "gust_save_css",
        nonce: window.Gust.nonce,
        css,
      },
    })
      .then(() => {
        setRebuildLoading(false);
      })
      .catch((e) => {
        console.log(e);
        setRebuildLoading(false);
        alert("There was an error rebuilding your CSS file");
      });
  };

  $("#gust-rebuild-css").click(function (e) {
    e.preventDefault();
    if (rebuildState.loading) return;
    setRebuildLoading(true);
    window.GustWorker.compile({
      config: window.Gust.config,
      safelist: window.Gust.safelist,
      css: window.Gust.css,
      tailwindVersion: window.Gust.tailwindVersion,
      minify: true,
    })
      .then((css) => {
        saveCss(css);
      })
      .catch((e) => {
        console.log(e);
        setRebuildLoading(false);
        alert("There was an error rebuilding your CSS file");
      });
  });

  $(".gust-confirm-action").click(function (e) {
    e.preventDefault();
    const confirmed = window.confirm("Are you sure?");
    var el = $(this);
    if (confirmed) {
      var originalText = el.text();
      el.text("Processing...");
      $.ajax({
        type: "post",
        url: window.Gust.adminUrl,
        data: {
          action: el.data("gust-action"),
          nonce: window.Gust.nonce,
          payload: el.data("gust-payload"),
        },
      })
        .then(function () {
          el.text(originalText);
        })
        .catch(function (error) {
          console.log(error);
          el.text(originalText);
          window.alert("There was an error, please try again.");
        });
    }
  });
});
