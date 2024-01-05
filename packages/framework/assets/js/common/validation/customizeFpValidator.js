import { VALIDATION_GROUP_DEFAULT } from '../../admin/validation/form/validation';
import CustomizeBundle from './customizeBundle';
import DoubleFormSubmitProtection from '../utils/DoubleFormSubmitProtection';

const FpJsFormValidator = window.FpJsFormValidator;

// Issue in dynamic collections validation that causes duplication of substrings in identifiers
// Issue described in https://github.com/formapro/JsFormValidatorBundle/issues/139
// PR with fix in the original package: https://github.com/formapro/JsFormValidatorBundle/pull/141
FpJsFormValidator._preparePrototype = FpJsFormValidator.preparePrototype;
FpJsFormValidator.preparePrototype = function (prototype, name) {
    if (prototype.data && prototype.data.form && typeof prototype.data.form.groups === 'string') {
        prototype.data.form.groups = prototype.data.form.groups.replace(/__name__/g, name);
    }
    prototype.name = prototype.name.replace(/__name__/g, name);
    prototype.id = prototype.id.replace(/__name__/g, name);

    if (typeof prototype.children === 'object') {
        for (let childName in prototype.children) {
            prototype.children[childName] = this.preparePrototype(prototype.children[childName], name);
        }
    }

    return prototype;
};

FpJsFormValidator.ajax._checkQueue = FpJsFormValidator.ajax.checkQueue;
FpJsFormValidator.ajax.checkQueue = function () {
    if (FpJsFormValidator.ajax.queue === 0) {
        for (let i in this.callbacks) {
            if (typeof this.callbacks[i] === 'function') {
                this.callbacks[i]();
            }
        }
    }
};

FpJsFormValidator.customizeMethods._submitForm = FpJsFormValidator.customizeMethods.submitForm;

// Custom behavior:
// - disable JS validation for forms with class js-no-validate
// - disable JS validation for forms submitted by element with class js-no-validate-button
// - do not submit if custom "on-submit" code is specified
// - do not submit if ajax queue is not empty
// - clears callbacks if ajax queue exists, because while validation is done via ajax,
//   there can be loaded callbacks from last form submit which can cause duplicated form error windows
// (the rest is copy&pasted from original method; eg. ajax validation)
FpJsFormValidator.customizeMethods.submitForm = function (event) {

    if ($(':focus').hasClass('js-no-validate-button')) {
        return;
    }

    $('.js-window-validation-errors').addClass('display-none');
    const $form = $(this);

    if ($form.hasClass('js-no-validate')) {
        return;
    }

    const doubleFormSubmitProtection = new DoubleFormSubmitProtection();
    doubleFormSubmitProtection.protection(event);

    FpJsFormValidator.each(this, function (item) {
        const element = item.jsFormValidator;
        element.validateRecursively();
        element.onValidate.apply(element.domNode, [FpJsFormValidator.getAllErrors(element, {}), event]);
    });

    if (!FpJsFormValidator.ajax.queue) {
        if (!CustomizeBundle.isFormValid(this)) {
            event.preventDefault();
            CustomizeBundle.showFormErrorsWindow(this);
        } else if (CustomizeBundle.isFormValid($form) === true && $form.data('on-submit') !== undefined) {
            $(this).trigger($(this).data('on-submit'));
            event.preventDefault();
        }
    } else {
        event.preventDefault();

        FpJsFormValidator.ajax.callbacks.push(function () {
            FpJsFormValidator.ajax.callbacks = [];

            if (!CustomizeBundle.isFormValid($form)) {
                CustomizeBundle.showFormErrorsWindow($form[0]);
            } else if ($form.data('on-submit') !== undefined) {
                $form.trigger($form.data('on-submit'));
            } else {
                $form.addClass('js-no-validate');
                $form.unbind('submit').submit();
            }
        });
    }
};

// Bind custom events to each element with validator
FpJsFormValidator._attachElement = FpJsFormValidator.attachElement;
FpJsFormValidator.attachElement = function (element) {
    FpJsFormValidator._attachElement(element);
    CustomizeBundle.elementBind(element);
};

FpJsFormValidator._getElementValue = FpJsFormValidator.getElementValue;
FpJsFormValidator.getElementValue = function (element) {
    var i = element.transformers.length;
    var value = this.getInputValue(element);

    if (i && undefined === value) {
        value = this.getMappedValue(element);
    } else if (
        element.type === 'Symfony\\Component\\Form\\Extension\\Core\\Type\\CollectionType'
        || (Object.keys(element.children).length > 0 && element.type !== 'Shopsys\\FrameworkBundle\\Form\\FileUploadType' && element.type !== 'Shopsys\\FrameworkBundle\\Form\\ImageUploadType')
    ) {
        value = {};
        for (var childName in element.children) {
            value[childName] = this.getMappedValue(element.children[childName]);
        }
    } else {
        value = this.getSpecifiedElementTypeValue(element);
    }

    while (i--) {
        value = element.transformers[i].reverseTransform(value, element);
    }

    return value;
};

FpJsFormValidator._getInputValue = FpJsFormValidator.getInputValue;
FpJsFormValidator.getInputValue = function (element) {
    if (element.type === 'FOS\\CKEditorBundle\\Form\\Type\\CKEditorType'
        && CKEDITOR.instances[element.id]
    ) {
        return CKEDITOR.instances[element.id].getData();
    }
    if (element.type === 'Shopsys\\FrameworkBundle\\Form\\FileUploadType' || element.type === 'Shopsys\\FrameworkBundle\\Form\\ImageUploadType') {
        return $(element.domNode).find('.js-file-upload-uploaded-file').toArray();
    }
    if (element.type === 'Shopsys\\FrameworkBundle\\Form\\ProductsType') {
        var value = [];
        $(element.domNode).find('.js-products-picker-item-input').each(function () {
            value.push($(this).val());
        });
        return value;
    }
    return FpJsFormValidator._getInputValue(element);
};

// stop error bubbling, because errors of some collections (eg. admin order items) bubble to main form and mark all inputs as invalid
FpJsFormValidator._getErrorPathElement = FpJsFormValidator.getErrorPathElement;
FpJsFormValidator.getErrorPathElement = function (element) {
    return element;
};

// some forms (eg. frontend order transport and payments) throws "Uncaught TypeError: Cannot read property 'domNode' of null"
// reported as https://github.com/formapro/JsFormValidatorBundle/issues/61
FpJsFormValidator._initModel = FpJsFormValidator.initModel;
FpJsFormValidator.initModel = function (model) {
    var element = this.createElement(model);
    if (!element) {
        return null;
    }
    var form = this.findFormElement(element);
    element.domNode = form;

    this.attachElement(element);
    if (form) {
        this.attachDefaultEvent(element, form);
    }
    CustomizeBundle.ckeditorValidationInit(element);

    return element;
};

// disable JS validation for form fields in element with class js-no-validate
FpJsFormValidator._createElement = FpJsFormValidator.createElement;
FpJsFormValidator.createElement = function (model) {
    var element = this._createElement(model);
    if (!element) {
        return null;
    }
    if ($(element.domNode).closest('.js-no-validate').length > 0) {
        return null;
    }

    return element;
};

// reported as https://github.com/formapro/JsFormValidatorBundle/issues/66
FpJsFormValidator._checkValidationGroups = FpJsFormValidator.checkValidationGroups;
FpJsFormValidator.checkValidationGroups = function (needle, haystack) {
    if (typeof haystack === 'undefined') {
        haystack = [VALIDATION_GROUP_DEFAULT];
    }
    return FpJsFormValidator._checkValidationGroups(needle, haystack);
};

// determine domElement as the closest ancestor of all children
FpJsFormValidator._findDomElement = FpJsFormValidator.findDomElement;
FpJsFormValidator.findDomElement = function (model) {
    return CustomizeBundle.findDomElementRecursive(model, FpJsFormValidator);
};

FpJsFormValidator._isValueEmty = FpJsFormValidator.isValueEmty;
FpJsFormValidator.isValueEmty = function (value, element) {
    if (element instanceof FpJsFormElement) {
        if (CustomizeBundle.isExpandedChoiceFormType(element, value)) {
            return CustomizeBundle.isExpandedChoiceEmpty(value);
        }
    }

    return FpJsFormValidator._isValueEmty(value);
};

const _SymfonyComponentValidatorConstraintsUrl = window.SymfonyComponentValidatorConstraintsUrl; // eslint-disable-line no-unused-vars
const SymfonyComponentValidatorConstraintsUrl = function () {
    this.message = '';

    this.validate = function (value, element) {
        const regexp = /^(https?:\/\/|(?=.*\.))([0-9a-z\u00C0-\u02FF\u0370-\u1EFF](([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,61}[0-9a-z\u00C0-\u02FF\u0370-\u1EFF])?\.)*[a-z\u00C0-\u02FF\u0370-\u1EFF][-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,17}[a-z\u00C0-\u02FF\u0370-\u1EFF]|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}|\[[0-9a-f:]{3,39}\])(:\d{1,5})?(\/\S*)?$/i;
        const errors = [];
        if (!FpJsFormValidator.isValueEmty(value) && !regexp.test(value)) {
            errors.push(this.message.replace('{{ value }}', String('http://' + value)));
        }

        return errors;
    };
};

window.SymfonyComponentValidatorConstraintsUrl = SymfonyComponentValidatorConstraintsUrl;
