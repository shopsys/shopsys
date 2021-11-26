import 'framework/common/components';

(function ($, window) {

    window.AppFormConstraintsUniqueProductCatnum = function () {
        this.message = null;

        this.validate = (value, element) => {
            const $catnumInput = $('#' + element.id);

            const url = $catnumInput.data('unique-catnum-url');
            const currentProductCatnum = $catnumInput.data('current-product-catnum');

            if (url === undefined) {
                return [];
            }

            FpJsFormValidator.ajax.sendRequest(
                url,
                {
                    catnum: value,
                    currentProductCatnum
                },
                (response) => {
                    const catnumExists = JSON.parse(response);

                    if (catnumExists) {
                        $catnumInput.jsFormValidator('showErrors', {
                            errors: [this.message],
                            sourceId: 'form-error-' + String(element.id).replace(/_/g, '-')
                        });
                    }
                }
            );

            return [];
        };
    };

})(jQuery, window);
