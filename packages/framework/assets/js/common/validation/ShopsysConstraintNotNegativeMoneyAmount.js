(window => {
    const ShopsysFrameworkBundleFormConstraintsNotNegativeMoneyAmount = function () {
        this.message = '';

        this.validate = value => {
            if (value < 0) {
                return [this.message];
            }

            return [];
        };
    };

    window.ShopsysFrameworkBundleFormConstraintsNotNegativeMoneyAmount =
        ShopsysFrameworkBundleFormConstraintsNotNegativeMoneyAmount;
})(window);
