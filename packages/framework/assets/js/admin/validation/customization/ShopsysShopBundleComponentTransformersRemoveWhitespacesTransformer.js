(window => {
    const ShopsysFrameworkBundleComponentTransformersRemoveWhitespacesTransformer = function () {
        this.reverseTransform = (value, _ele) => value.replace(/\s/g, '');
    };

    window.ShopsysFrameworkBundleComponentTransformersRemoveWhitespacesTransformer =
        ShopsysFrameworkBundleComponentTransformersRemoveWhitespacesTransformer;
})(window);
