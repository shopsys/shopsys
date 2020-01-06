(function (window) {

    const SymfonyComponentValidatorConstraintsEmail = function () {
        this.message = '';

        this.validate = function (value) {
            const regexp = /^.+@\S+\.\S+$/i;
            const errors = [];
            const f = FpJsFormValidator;

            if (!f.isValueEmty(value) && !regexp.test(value)) {
                errors.push(this.message.replace('{{ value }}', String(value)));
            }

            return errors;
        };
    };

    window.SymfonyComponentValidatorConstraintsEmail = SymfonyComponentValidatorConstraintsEmail;

})(window);
