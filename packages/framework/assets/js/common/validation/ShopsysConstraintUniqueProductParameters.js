(function (window) {

    const ShopsysFrameworkBundleFormConstraintsUniqueProductParameters = function () {
        this.message = '';

        /**
         * This method is required
         * Should return an error message or an array of messages
         */
        this.validate = function (value) {
            const uniqueCollectionValidator = new ShopsysFrameworkBundleFormConstraintsUniqueCollection();
            uniqueCollectionValidator.message = this.message.replace('{{ parameterName }}', '');
            uniqueCollectionValidator.fields = ['parameter', 'locale'];
            uniqueCollectionValidator.allowEmpty = false;

            return uniqueCollectionValidator.validate(value);
        };

    };

    window.ShopsysFrameworkBundleFormConstraintsUniqueProductParameters = ShopsysFrameworkBundleFormConstraintsUniqueProductParameters;

})(window);
