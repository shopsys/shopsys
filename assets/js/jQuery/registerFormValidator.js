export default function registerFormValidator () {
    $.extend($.fn, {
        jsFormValidator: function (method) {
            if (!method) {
                return FpJsFormValidator.customizeMethods.get.apply($.makeArray(this), arguments);
            } else if (typeof method === 'object') {
                return $(FpJsFormValidator.customizeMethods.init.apply($.makeArray(this), arguments));
            } else if (FpJsFormValidator.customizeMethods[method]) {
                return FpJsFormValidator.customizeMethods[method].apply($.makeArray(this), Array.prototype.slice.call(arguments, 1));
            } else {
                $.error('Method ' + method + ' does not exist');
                return this;
            }
        }
    });
}
