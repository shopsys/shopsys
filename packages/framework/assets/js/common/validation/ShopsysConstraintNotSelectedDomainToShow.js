(window => {
    const ShopsysFrameworkBundleFormConstraintsNotSelectedDomainToShow = function () {
        this.message = '';

        this.validate = function (value, _ele) {
            let anyDomainSelected = false;

            for (const i in value) {
                if (value[i] === true) {
                    anyDomainSelected = true;
                    break;
                }
            }

            if (!anyDomainSelected) {
                return this.message;
            } else {
                return [];
            }
        };
    };

    window.ShopsysFrameworkBundleFormConstraintsNotSelectedDomainToShow =
        ShopsysFrameworkBundleFormConstraintsNotSelectedDomainToShow;
})(window);
