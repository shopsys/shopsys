(function (window) {

    const ShopsysFrameworkBundleComponentTransformersProductParameterValueToProductParameterValuesLocalizedTransformer = function () {

        this.transform = function (normData) {
            return normData;
        };

        this.reverseTransform = function (viewData) {
            const normData = [];

            for (const i in viewData) {
                const productParameterValuesLocalized = viewData[i];

                for (const locale in productParameterValuesLocalized.valueText) {
                    const valueText = productParameterValuesLocalized.valueText[locale];

                    if (valueText !== '') {
                        const productParameterValue = {
                            parameter: productParameterValuesLocalized.parameter,
                            locale,
                            valueText
                        };

                        normData.push(productParameterValue);
                    }
                }
            }

            return normData;
        };
    };

    window.ShopsysFrameworkBundleComponentTransformersProductParameterValueToProductParameterValuesLocalizedTransformer = ShopsysFrameworkBundleComponentTransformersProductParameterValueToProductParameterValuesLocalizedTransformer;

})(window);
