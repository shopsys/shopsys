(($, window) => {
    const ShopsysFrameworkBundleFormConstraintsUniqueCollection = function () {
        const self = this;
        this.message = '';
        this.fields = null;
        this.allowEmpty = false;

        /**
         * This method is required
         * Should return an error message or an array of messages
         */
        this.validate = value => {
            const result = new Set();

            $.each(value, (key1, value1) => {
                $.each(value, (key2, value2) => {
                    if (key1 !== key2 && areValuesEqual(value1, value2)) {
                        result.add(self.message);
                    }
                });
            });

            // convert Set to array
            return [...result];
        };

        function areValuesEqual(value1, value2) {
            if (self.allowEmpty) {
                if (value1 === null || value1 === '' || value2 === null || value2 === '') {
                    return false;
                }
            }

            if (self.fields === null) {
                return value1 === value2;
            } else {
                return areValuesEqualInFields(value1, value2);
            }
        }

        function areValuesEqualInFields(value1, value2) {
            for (let i = 0; i < self.fields.length; i++) {
                const field = self.fields[i];
                if (!areValuesSame(value1[field], value2[field])) {
                    return false;
                }
            }

            return true;
        }

        function areValuesSame(value1, value2) {
            if (Array.isArray(value1) && Array.isArray(value2)) {
                return value1.length === value2.length && value1.every((element, index) => element === value2[index]);
            } else {
                return value1 === value2;
            }
        }
    };

    window.ShopsysFrameworkBundleFormConstraintsUniqueCollection =
        ShopsysFrameworkBundleFormConstraintsUniqueCollection;
})(jQuery, window);
