import { parseNumber } from '../utils/number';

(function (window) {

    const ShopsysFrameworkBundleFormConstraintsMoneyRange = function () {
        const self = this;
        this.minMessage = '';
        this.maxMessage = '';
        this.min = null;
        this.max = null;

        this.validate = function (value) {
            if (!FpJsFormValidator.isValueEmty(value)) {
                const compareValue = parseNumber(value);

                if (self.max !== null && compareValue > parseNumber(self.max.amount)) {
                    return [self.maxMessage.replace('{{ limit }}', self.max.amount)];
                }
                if (self.min !== null && compareValue < parseNumber(self.min.amount)) {
                    return [self.minMessage.replace('{{ limit }}', self.min.amount)];
                }
            }

            return [];
        };
    };

    window.ShopsysFrameworkBundleFormConstraintsMoneyRange = ShopsysFrameworkBundleFormConstraintsMoneyRange;

})(window);
