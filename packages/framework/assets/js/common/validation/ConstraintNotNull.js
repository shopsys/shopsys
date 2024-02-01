(function (window) {

    const SymfonyComponentValidatorConstraintsNotNull = function () {
        this.message = '';

        this.validate = function (value, element) {
            const errors = [];
            const f = FpJsFormValidator;

            let isValueNull;

            if (element.type === 'Symfony\\Component\\Form\\Extension\\Core\\Type\\CheckboxType') {
                isValueNull = value === null;
            } else if (element.type === 'Shopsys\\FrameworkBundle\\Form\\SingleCheckboxChoiceType') {
                isValueNull = true;
                for (const i in value) {
                    if (value.hasOwnProperty(i) && value[i] === true) {
                        isValueNull = false;
                        break;
                    }
                }
            } else {
                isValueNull = f.isValueEmty(value);
            }

            if (isValueNull) {
                errors.push(this.message.replace('{{ value }}', String(value)));
            }

            return errors;
        };
    };

    window.SymfonyComponentValidatorConstraintsNotNull = SymfonyComponentValidatorConstraintsNotNull;

})(window);
