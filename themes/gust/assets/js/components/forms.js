jQuery(function ($) {
  $("form[data-gust-form]").on("submit", function (e) {
    e.preventDefault();

    // hide both success and error messages initially
    const successMessage = $(".gust-form-success", this);
    const errorMessage = $(".gust-form-error", this);
    successMessage.hide();
    errorMessage.hide();

    if (!window.GustForms) {
      errorMessage.show();
      return;
    }

    // show loading state
    const submitButtons = $('button[type="submit"]', this);
    submitButtons.each(function (_i, btnEl) {
      const btn = $(btnEl);
      btn.data("originalText", btn.text());
      btn.text("Loading...");
    });

    // get values
    const inputs = $(this).serializeArray();
    const formEl = this;

    // remove duplicates
    const inputMap = inputs.reduce(function (accumulator, input) {
      const inputValue = accumulator[input.name]
        ? `${accumulator[input.name]}, ${input.value}`
        : input.value;
      return {
        ...accumulator,
        [input.name]: inputValue,
      };
    }, {});
    const finalInputs = Object.entries(inputMap).map(function ([name, value]) {
      return { name, value };
    });

    // send form to server
    $.post(window.GustForms.adminUrl, {
      action: window.GustForms.action,
      nonce: window.GustForms.nonce,
      gust_data: finalInputs,
    })
      .done(function () {
        formEl.reset();
        successMessage.show();
      })
      .fail(function () {
        errorMessage.show();
      })
      .always(function () {
        submitButtons.each(function (_i, btnEl) {
          const btn = $(btnEl);
          btn.text(btn.data("originalText"));
        });
      });
  });
});
