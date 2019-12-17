import { parseNumber } from '../number';

(function (w) {

    SymfonyComponentValidatorConstraintsRange = function () {
        this.maxMessage = '';
        this.minMessage = '';
        this.invalidMessage = '';
        this.max = null;
        this.min = null;

        this.validate = function (value) {

            const f = FpJsFormValidator;
            const compareValue = parseNumber(value);

            if (f.isValueEmty(value) || (compareValue !== null && compareValue >= this.min && compareValue <= this.max)) {
                return [];
            } else if (compareValue < this.min) {
                return [
                    this.minMessage.replace('{{ limit }}', String(this.min))
                ];
            } else if (compareValue > this.max) {
                return [
                    this.maxMessage.replace('{{ limit }}', String(this.max))
                ];
            } else {
                return [
                    this.invalidMessage
                ];
            }
        };
    };

    w.SymfonyComponentValidatorConstraintsRange = SymfonyComponentValidatorConstraintsRange;

})(window);
