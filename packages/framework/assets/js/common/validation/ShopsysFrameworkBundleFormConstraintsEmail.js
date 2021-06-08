(function (window) {

    const ShopsysFrameworkBundleFormConstraintsEmail = function () {
        this.message = '';

        this.validate = function (value) {
            const regexp = /^("([ !#-[\]-~]|\\[ -~])+"|[-a-z0-9!#$%&'*+/=?^_`{|}~]+(\.[-a-z0-9!#$%&'*+/=?^_`{|}~]+)*)@([0-9a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,61}[0-9a-z\u00C0-\u02FF\u0370-\u1EFF])?\.)+[a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,17}[a-z\u00C0-\u02FF\u0370-\u1EFF])?$/i;

            const errors = [];
            const f = FpJsFormValidator;

            if (!f.isValueEmty(value) && !regexp.test(value)) {
                errors.push(this.message.replace('{{ value }}', String(value)));
            }

            return errors;
        };
    };

    window.ShopsysFrameworkBundleFormConstraintsEmail = ShopsysFrameworkBundleFormConstraintsEmail;

})(window);
