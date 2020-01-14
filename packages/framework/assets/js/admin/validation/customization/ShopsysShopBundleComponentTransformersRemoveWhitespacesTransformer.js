(function (window) {

    const ShopsysFrameworkBundleComponentTransformersRemoveWhitespacesTransformer = function () {
        this.reverseTransform = function (value, ele) {
            return value.replace(/\s/g, '');
        };
    };

    window.ShopsysFrameworkBundleComponentTransformersRemoveWhitespacesTransformer = ShopsysFrameworkBundleComponentTransformersRemoveWhitespacesTransformer;

})(window);
