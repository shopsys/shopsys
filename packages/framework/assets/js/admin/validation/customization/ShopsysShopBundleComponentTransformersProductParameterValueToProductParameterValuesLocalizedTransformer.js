(window => {
    const ShopsysFrameworkBundleComponentTransformersProductParameterValueToProductParameterValuesLocalizedTransformer =
        function () {
            this.transform = normData => normData;

            this.reverseTransform = viewData => {
                const normData = [];

                for (const i in viewData) {
                    const productParameterValuesLocalized = viewData[i];

                    for (const locale in productParameterValuesLocalized.valueText) {
                        const valueText = productParameterValuesLocalized.valueText[locale];

                        if (valueText !== '') {
                            const productParameterValue = {
                                parameter: productParameterValuesLocalized.parameter,
                                locale: locale,
                                valueText: valueText,
                            };

                            normData.push(productParameterValue);
                        }
                    }
                }

                return normData;
            };
        };

    window.ShopsysFrameworkBundleComponentTransformersProductParameterValueToProductParameterValuesLocalizedTransformer =
        ShopsysFrameworkBundleComponentTransformersProductParameterValueToProductParameterValuesLocalizedTransformer;
})(window);
