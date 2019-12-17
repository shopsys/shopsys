import constant from '../../admin/constant';

(function (w) {

    SymfonyComponentValidatorConstraintsNotNull = function () {
        this.message = '';

        this.validate = function (value, element) {
            const errors = [];
            const f = FpJsFormValidator;

            let isValueNull;

            if (element.type === constant('\\Symfony\\Component\\Form\\Extension\\Core\\Type\\CheckboxType::class')) {
                isValueNull = value === null;
            } else if (element.type === constant('\\Shopsys\\FrameworkBundle\\Form\\SingleCheckboxChoiceType::class')) {
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

    w.SymfonyComponentValidatorConstraintsNotNull = SymfonyComponentValidatorConstraintsNotNull;

})(window);
