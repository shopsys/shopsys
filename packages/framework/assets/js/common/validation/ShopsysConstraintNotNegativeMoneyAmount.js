(function (w) {

    ShopsysFrameworkBundleFormConstraintsNotNegativeMoneyAmount = function () {
        const self = this;
        this.message = '';

        this.validate = function (value) {
            if (value < 0) {
                return [self.message];
            }

            return [];
        };
    };

    w.ShopsysFrameworkBundleFormConstraintsNotNegativeMoneyAmount = ShopsysFrameworkBundleFormConstraintsNotNegativeMoneyAmount;

})(window);
