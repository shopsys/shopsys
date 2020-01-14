(function (window) {

    const ShopsysFrameworkBundleFormConstraintsContains = function () {
        const self = this;
        this.message = '';
        this.needle = null;

        this.validate = function (value) {
            const result = [];

            if (value.indexOf(self.needle) === -1) {
                result.push(FpJsBaseConstraint.prepareMessage(
                    self.message,
                    {
                        '{{ value }}': '"' + value + '"',
                        '{{ needle }}': '"' + self.needle + '"'
                    }
                ));
            }

            return result;
        };
    };

    window.ShopsysFrameworkBundleFormConstraintsContains = ShopsysFrameworkBundleFormConstraintsContains;

})(window);
