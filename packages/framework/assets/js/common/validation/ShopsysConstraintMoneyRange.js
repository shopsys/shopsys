import { parseNumber } from '../utils/number';

(window => {
    const ShopsysFrameworkBundleFormConstraintsMoneyRange = function () {
        this.minMessage = '';
        this.maxMessage = '';
        this.min = null;
        this.max = null;

        this.validate = value => {
            if (!FpJsFormValidator.isValueEmty(value)) {
                const compareValue = parseNumber(value);

                if (this.max !== null && compareValue > parseNumber(this.max.amount)) {
                    return [this.maxMessage.replace('{{ limit }}', this.max.amount)];
                }
                if (this.min !== null && compareValue < parseNumber(this.min.amount)) {
                    return [this.minMessage.replace('{{ limit }}', this.min.amount)];
                }
            }

            return [];
        };
    };

    window.ShopsysFrameworkBundleFormConstraintsMoneyRange = ShopsysFrameworkBundleFormConstraintsMoneyRange;
})(window);
