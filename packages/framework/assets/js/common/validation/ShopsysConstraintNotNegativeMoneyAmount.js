(function (window) {

    const ShopsysFrameworkBundleFormConstraintsNotNegativeMoneyAmount = function () {
        const self = this;
        this.message = '';

        this.validate = function (value) {
            if (value < 0) {
                return [self.message];
            }

            return [];
        };
    };

    window.ShopsysFrameworkBundleFormConstraintsNotNegativeMoneyAmount = ShopsysFrameworkBundleFormConstraintsNotNegativeMoneyAmount;

})(window);
