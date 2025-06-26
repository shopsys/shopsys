(window => {
    const ShopsysFrameworkBundleFormConstraintsContains = function () {
        this.message = '';
        this.needle = null;

        this.validate = value => {
            const result = [];

            if (value.indexOf(this.needle) === -1) {
                result.push(
                    FpJsBaseConstraint.prepareMessage(this.message, {
                        '{{ value }}': `"${value}"`,
                        '{{ needle }}': `"${this.needle}"`,
                    }),
                );
            }

            return result;
        };
    };

    window.ShopsysFrameworkBundleFormConstraintsContains = ShopsysFrameworkBundleFormConstraintsContains;
})(window);
