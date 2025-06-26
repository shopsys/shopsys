(($, window) => {
    const ShopsysFrameworkBundleFormConstraintsUniqueEmail = function () {
        this.message = null;

        this.validate = function (value, element) {
            const $emailInput = $(`#${element.id}`);
            const url = $emailInput.data('request-url');

            if (url !== undefined) {
                FpJsFormValidator.ajax.sendRequest(url, { email: value }, response => {
                    const existsEmail = JSON.parse(response);

                    if (existsEmail) {
                        const sourceId = `form-error-${String(element.id).replace(/_/g, '-')}`;
                        const message = this.message.replace('{{ email }}', value);
                        element.showErrors([message], sourceId);
                        $emailInput.addClass('form-input-error');
                    }
                });
            }

            return [];
        };
    };

    window.ShopsysFrameworkBundleFormConstraintsUniqueEmail = ShopsysFrameworkBundleFormConstraintsUniqueEmail;
})(jQuery, window);
