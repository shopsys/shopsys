export default function registerFormValidator() {
    $.extend($.fn, {
        jsFormValidator: function (...args) {
            const method = args[0];
            if (!method) {
                return FpJsFormValidator.customizeMethods.get.apply($.makeArray(this), args);
            } else if (typeof method === 'object') {
                return $(FpJsFormValidator.customizeMethods.init.apply($.makeArray(this), args));
            } else if (FpJsFormValidator.customizeMethods[method]) {
                return FpJsFormValidator.customizeMethods[method].apply($.makeArray(this), args.slice(1));
            } else {
                $.error(`Method ${method} does not exist`);
                return this;
            }
        },
    });
}
