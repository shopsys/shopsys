(function (w) {

    ShopsysFrameworkBundleFormConstraintsNotSelectedDomainToShow = function () {
        this.message = '';

        this.validate = function (value, ele) {
            let anyDomainSelected = false;

            for (var i in value) {
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

    w.ShopsysFrameworkBundleFormConstraintsNotSelectedDomainToShow = ShopsysFrameworkBundleFormConstraintsNotSelectedDomainToShow;

})(window);
