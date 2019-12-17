import { parseNumber } from '../number';

(function (w) {

    SymfonyComponentValidatorConstraintsLessThan = function () {
        this.message = '';
        this.value = null;

        this.validate = function (value) {

            const f = FpJsFormValidator;
            const compareValue = parseNumber(value);

            if (f.isValueEmty(value) || (compareValue !== null && compareValue < this.value)) {
                return [];
            } else {
                return [
                    this.message
                        .replace('{{ value }}', String(value))
                        .replace('{{ compared_value }}', String(this.value))
                ];
            }
        };
    };

    w.SymfonyComponentValidatorConstraintsLessThan = SymfonyComponentValidatorConstraintsLessThan;

})(window);
