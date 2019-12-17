(function ($, w) {

    SymfonyComponentValidatorConstraintsAll = function () {
        this.constraints = null;
        this.groups = null;

        this.validate = function (value, element) {
            const constraints = FpJsFormValidator.parseConstraints(this.constraints);
            const sourceId = 'form-input-error-' + String(element.id).replace(/_/g, '-');

            for (const childName in element.children) {
                const childElement = element.children[childName];
                const childValue = FpJsFormValidator.getElementValue(childElement);
                const errorPath = FpJsFormValidator.getErrorPathElement(childElement);

                const errors = FpJsFormValidator.validateConstraints(
                    childValue,
                    constraints,
                    this.groups,
                    childElement
                );

                FpJsFormValidator.customize(errorPath.domNode, 'showErrors', {
                    errors: errors,
                    sourceId: sourceId
                });
            }

            return [];
        };
    };

    w.SymfonyComponentValidatorConstraintsAll = SymfonyComponentValidatorConstraintsAll;

})(jQuery, window);
