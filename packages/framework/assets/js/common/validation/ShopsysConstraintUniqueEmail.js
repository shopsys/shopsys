(function ($, w) {

    ShopsysFrameworkBundleFormConstraintsUniqueEmail = function () {
        this.message = null;

        this.validate = function (value, element) {
            const self = this;
            const $emailInput = $('#' + element.id);
            const url = $emailInput.data('request-url');

            if (url !== undefined) {
                FpJsFormValidator.ajax.sendRequest(
                    url,
                    { email: value },
                    function (response) {
                        const existsEmail = JSON.parse(response);

                        if (existsEmail) {
                            const sourceId = 'form-error-' + String(element.id).replace(/_/g, '-');
                            const message = self.message.replace('{{ email }}', value);
                            element.showErrors([message], sourceId);
                            $emailInput.addClass('form-input-error');
                        }
                    }
                );
            }

            return [];
        };
    };

    w.ShopsysFrameworkBundleFormConstraintsUniqueEmail = ShopsysFrameworkBundleFormConstraintsUniqueEmail;

})(jQuery, window);
