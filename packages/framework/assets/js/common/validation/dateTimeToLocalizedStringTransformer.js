(function (w) {

    SymfonyComponentFormExtensionCoreDataTransformerDateTimeToLocalizedStringTransformer = function () {
        this.reverseTransform = function (value) {
            if (this.pattern.toLowerCase() === 'dd.mm.yyyy') {
                const regexp = /^(\d{2})\.(\d{2})\.(\d{4})$/;
                const parts = regexp.exec(value);
                if (parts) {
                    value = parts[3] + '-' + parts[2] + '-' + parts[1];
                }
            }
            return value;
        };
    };

    w.SymfonyComponentFormExtensionCoreDataTransformerDateTimeToLocalizedStringTransformer = SymfonyComponentFormExtensionCoreDataTransformerDateTimeToLocalizedStringTransformer;

})(window);
