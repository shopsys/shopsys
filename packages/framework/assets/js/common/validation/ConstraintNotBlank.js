(function (window) {

    const SymfonyComponentValidatorConstraintsNotBlank = function () {
        this.message = '';

        this.validate = function (value, element) {
            const errors = [];
            const f = FpJsFormValidator;

            if (f.isValueEmty(value, element)) {
                errors.push(this.message.replace('{{ value }}', String(value)));
            }

            return errors;
        };
    };

    window.SymfonyComponentValidatorConstraintsNotBlank = SymfonyComponentValidatorConstraintsNotBlank;

})(window);
