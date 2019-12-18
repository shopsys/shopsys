import FormChangeInfo from '../../admin/formChangeInfo';
import { highlightSubmitButtons, findElementsToHighlight } from './validation';
import constant from '../../admin/constant';
import Timeout from '../components/timeout';
import Window from '../../admin/window';
import Translator from 'bazinga-translator';

export const isFormValid = (form) => $(form).find('.js-validation-errors-message').length === 0;

export function addNewItemToCollection (collectionSelector, itemIndex) {
    $($(collectionSelector)).jsFormValidator('addPrototype', itemIndex);
    FormChangeInfo.showInfo();
}

export function removeItemFromCollection (collectionSelector, itemIndex) {
    if (itemIndex === undefined) {
        throw Error('ItemIndex is undefined while remove item from collections');
    }
    const $collection = $(collectionSelector);
    $($collection).jsFormValidator('delPrototype', itemIndex);
    highlightSubmitButtons($collection.closest('form'));
    $collection.jsFormValidator('validate');
    FormChangeInfo.showInfo();
}

const getErrorListClass = (elementName) => {
    return elementName.replace(/-/g, '_')
        .replace('form_error_', 'js-validation-error-list-')
        .replace('value_to_duplicates_', 'js-validation-error-list-'); // defined in function SymfonyComponentFormExtensionCoreDataTransformerValueToDuplicatesTransformer()
};

const ckeditorValidationInit = (element) => {
    $.each(element.children, function (index, childElement) {
        if (childElement.type === constant('\\FOS\\CKEditorBundle\\Form\\Type\\CKEditorType::class')) {
            CKEDITOR.instances[childElement.id].on('change', function () {
                $(childElement.domNode).jsFormValidator('validate');
            });
        }
        if (Object.keys(childElement.children).length > 0) {
            ckeditorValidationInit(childElement);
        }
    });
};

/**
 * Delayed validation on blur event
 *
 * We customized JS validation to validate form elements on blur event.
 * The standard validation that is done on submit event validates all form elements recursively (from root
 * to children elements).
 * On blur event, we want to validate the blurred element itself and all its ancestral elements (because
 * constraints of ancestral elements can validate children data).
 * However, if user leaves one element and focuses on a sibling element in short time we do not want to run
 * validation on thier common ancestral elements because user presumably did not finish filling-in
 * the ancestral form yet.
 * Therefore we delay the validation on blur event and if user focuses another element in short time
 * we suppress the validation of common ancestral elements. So the validation happens after user leaves
 * the whole form (or sub-form).
 *
 * This prevents from showing "passwords are not the same" error when user fills-in the first password
 * and focuses the second password field (before even starting to fill-in the second password field).
 */
const elementBind = (element) => {
    if (!element.domNode) {
        return;
    }

    const $domNode = $(element.domNode);

    if ($domNode.closest('.js-no-validate').length > 0) {
        return;
    }

    const isJsFileUpload = $domNode.closest('.js-file-upload').length > 0;

    $domNode
        .bind('blur change', function (event) {
            if (this.jsFormValidator) {
                event.preventDefault();

                if (isJsFileUpload !== true) {
                    validateWithParentsDelayed(this.jsFormValidator);
                }
            }
        })
        .focus(function () {
            if (this.jsFormValidator) {
                removeDelayedValidationWithParents(this.jsFormValidator);
            }

            $(this).closest('.form-input-error').removeClass('form-input-error');
        })
        .jsFormValidator({
            'showErrors': showErrors
        });
};

let delayedValidators = {};

const executeDelayedValidators = function () {
    const validators = delayedValidators;
    delayedValidators = {};

    $.each(validators, function () {
        this.validate();
    });
};

const validateWithParentsDelayed = (jsFormValidator) => {
    do {
        delayedValidators[jsFormValidator.id] = jsFormValidator;
        jsFormValidator = jsFormValidator.parent;
    } while (jsFormValidator);

    Timeout.setTimeoutAndClearPrevious('Shopsys.validation.validateWithParentsDelayed', executeDelayedValidators, 100);
};

const findDomElementRecursive = (model) => {
    const domElement = FpJsFormValidator._findDomElement(model);

    if (domElement !== null) {
        return domElement;
    }

    const childDomElements = [];
    for (let i in model.children) {
        const child = model.children[i];
        const childDomElement = findDomElementRecursive(child);

        if (childDomElement !== null) {
            childDomElements.push(childDomElement);
        }
    }

    return findClosestCommonAncestor(childDomElements);
};

const findClosestCommonAncestor = (domElements) => {
    if (domElements.length === 0) {
        return null;
    }

    const domElementsAncestors = [];

    for (let i in domElements) {
        const domElement = domElements[i];
        const $domElementParents = $(domElement).parents();

        const domElementAncestors = reverseCollectionToArray($domElementParents);

        domElementsAncestors.push(domElementAncestors);
    }

    const firstDomElementAncestors = domElementsAncestors[0];

    let closestCommonAncestor = null;
    for (let ancestorLevel = 0; ancestorLevel < firstDomElementAncestors.length; ancestorLevel++) {
        if (firstDomElementAncestors[ancestorLevel].tagName.toLowerCase() !== 'form') {
            for (let i = 1; i < domElementsAncestors.length; i++) {
                if (domElementsAncestors[i][ancestorLevel] !== firstDomElementAncestors[ancestorLevel]) {
                    return closestCommonAncestor;
                }
            }

            closestCommonAncestor = firstDomElementAncestors[ancestorLevel];
        }
    }

    return closestCommonAncestor;
};

const reverseCollectionToArray = ($collection) => {
    const result = [];

    for (let i = $collection.length - 1; i >= 0; i--) {
        result.push($collection[i]);
    }

    return result;
};

const isExpandedChoiceFormType = (element, value) =>
    element.type === constant('\\Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType::class') && !$.isArray(value);

const isExpandedChoiceEmpty = (value) => {
    let isEmpty = true;

    $.each(value, function (key, value) {
        if (value !== false) {
            isEmpty = false;
            return false;
        }
    });

    return isEmpty;
};

const showErrors = (errors, sourceId) => {
    const $errorList = findOrCreateErrorList($(this), sourceId);
    const $errorListUl = $errorList.find('ul:first');
    const $elementsToHighlight = findElementsToHighlight($(this));

    const errorSourceClass = 'js-error-source-id-' + sourceId;
    $errorListUl.find('li.' + errorSourceClass).remove();

    $.each(errors, function (key, message) {
        $errorListUl.append(
            $('<li/>')
                .addClass('js-validation-errors-message')
                .addClass(errorSourceClass)
                .text(message)
        );
    });

    const hasErrors = $errorListUl.find('li').length > 0;
    $elementsToHighlight.toggleClass('form-input-error', hasErrors);
    $errorList.toggle(hasErrors);

    highlightSubmitButtons($(this).closest('form'));
};

const findOrCreateErrorList = ($formInput, elementName) => {
    const errorListClass = getErrorListClass(elementName);
    let $errorList = $('.' + errorListClass);
    if ($errorList.length === 0) {
        $errorList = $($.parseHTML(
            '<div class="in-message in-message--danger js-validation-errors-list ' + errorListClass + '">\
                <ul class="in-message__list"></ul>\
            </div>'
        ));
        $errorList.insertBefore($formInput);
    }

    return $errorList;
};

const showFormErrorsWindow = (container) => {
    const $formattedFormErrors = getFormattedFormErrors(container);
    const $window = $('#js-window');

    const $errorListHtml = '<div class="text-left">'
        + Translator.trans('Please check the entered values.<br>')
        + $formattedFormErrors[0].outerHTML
        + '</div>';

    if ($window.length === 0) {
        // eslint-disable-next-line no-new
        new Window({
            content: $errorListHtml
        });
    } else {
        $window.filterAllNodes('.js-window-validation-errors')
            .html($errorListHtml)
            .removeClass('display-none');
    }
};

export const getFormattedFormErrors = (container) => {
    const errorsByLabel = getFormErrorsIndexedByLabel(container);
    const $formattedFormErrors = $('<ul/>');
    for (let label in errorsByLabel) {
        const $errorsUl = $('<ul/>');
        for (let i in errorsByLabel[label]) {
            $errorsUl.append($('<li/>').text(errorsByLabel[label][i]));
        }
        $formattedFormErrors.append($('<li/>').text(label).append($errorsUl));
    }

    return $formattedFormErrors;
};

const getInputIdByErrorList = ($errorList) => {
    const inputIdMatch = $errorList.attr('class').match(/js-validation-error-list-([^\s]+)/);
    if (inputIdMatch) {
        return inputIdMatch[1];
    }

    return undefined;
};

const getFormErrorsIndexedByLabel = (container) => {
    let errorsByLabel = {};

    $(container).find('.js-validation-errors-list li').each(function () {
        const $errorList = $(this).closest('.js-validation-errors-list');
        const errorMessage = $(this).text();
        const inputId = getInputIdByErrorList($errorList);

        if (inputId !== undefined) {
            const $label = findLabelByInputId(inputId);
            if ($label.length > 0) {
                errorsByLabel = addLabelError(errorsByLabel, $label.text(), errorMessage);
            }
        }
    });

    return errorsByLabel;
};

const findLabelByInputId = (inputId) => {
    let $label = $('label[for="' + inputId + '"]');
    let $input = $('#' + inputId);

    if ($label.length === 0) {
        $label = $('#js-label-' + inputId);
    }
    if ($label.length === 0) {
        $label = getClosestLabel($input, '.js-validation-label');
    }
    if ($label.length === 0) {
        $label = getClosestLabel($input, 'label');
    }
    if ($label.length === 0) {
        $label = getClosestLabel($input, '.form-full__title');
    }

    return $label;
};

const getClosestLabel = ($input, selector) => {
    const $formLine = $input.closest('.form-line:has(' + selector + '), .js-form-group:has(' + selector + '), .form-full:has(' + selector + ')');
    return $formLine.find(selector).filter(':first');
};

const addLabelError = (errorsByLabel, labelText, errorMessage) => {
    labelText = normalizeLabelText(labelText);

    if (errorsByLabel[labelText] === undefined) {
        errorsByLabel[labelText] = [];
    }
    if (errorsByLabel[labelText].indexOf(errorMessage) === -1) {
        errorsByLabel[labelText].push(errorMessage);
    }

    return errorsByLabel;
};

const normalizeLabelText = (labelText) => labelText.replace(/^\s*(.*)[\s:*]*$/, '$1');

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
            prototype[childName] = this.preparePrototype(prototype.children[childName], name);
        }
    }

    return prototype;
};

const removeDelayedValidationWithParents = (jsFormValidator) => {
    do {
        delete delayedValidators[jsFormValidator.id];
        jsFormValidator = jsFormValidator.parent;
    } while (jsFormValidator);
};

FpJsFormValidator.customizeMethods._submitForm = FpJsFormValidator.customizeMethods.submitForm;

// Custom behavior:
// - disable JS validation for forms with class js-no-validate
// - do not submit if custom "on-submit" code is specified
// - do not submit if ajax queue is not empty
// - clears callbacks if ajax queue exists, because while validation is done via ajax,
//   there can be loaded callbacks from last form submit which can cause duplicated form error windows
// (the rest is copy&pasted from original method; eg. ajax validation)
FpJsFormValidator.customizeMethods.submitForm = function (event) {
    $('.js-window-validation-errors').addClass('display-none');
    var $form = $(this);
    if (!$form.hasClass('js-no-validate')) {
        FpJsFormValidator.each(this, function (item) {
            var element = item.jsFormValidator;
            element.validateRecursively();
            element.onValidate.apply(element.domNode, [FpJsFormValidator.getAllErrors(element, {}), event]);
        });

        if (!FpJsFormValidator.ajax.queue) {
            if (!isFormValid(this)) {
                event.preventDefault();
                showFormErrorsWindow(this);
            } else if (isFormValid($form) === true && $form.data('on-submit') !== undefined) {
                $(this).trigger($(this).data('on-submit'));
                event.preventDefault();
            }
        } else {
            event.preventDefault();

            FpJsFormValidator.ajax.callbacks.push(function () {
                FpJsFormValidator.ajax.callbacks = [];

                if (!isFormValid($form)) {
                    showFormErrorsWindow($form[0]);
                } else if ($form.data('on-submit') !== undefined) {
                    $form.trigger($form.data('on-submit'));
                } else {
                    $form.addClass('js-no-validate');
                    $form.unbind('submit').submit();
                }
            });
        }
    }
};

// Bind custom events to each element with validator
FpJsFormValidator._attachElement = FpJsFormValidator.attachElement;
FpJsFormValidator.attachElement = function (element) {
    FpJsFormValidator._attachElement(element);
    elementBind(element);
};

FpJsFormValidator._getElementValue = FpJsFormValidator.getElementValue;
FpJsFormValidator.getElementValue = function (element) {
    var i = element.transformers.length;
    var value = this.getInputValue(element);

    if (i && undefined === value) {
        value = this.getMappedValue(element);
    } else if (
        element.type === constant('\\Symfony\\Component\\Form\\Extension\\Core\\Type\\CollectionType::class')
        || (Object.keys(element.children).length > 0 && element.type !== constant('\\Shopsys\\FrameworkBundle\\Form\\FileUploadType::class') && element.type !== constant('\\Shopsys\\FrameworkBundle\\Form\\ImageUploadType::class'))
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
    if (element.type === constant('\\FOS\\CKEditorBundle\\Form\\Type\\CKEditorType::class')) {
        return CKEDITOR.instances[element.id].getData();
    }
    if (element.type === constant('\\Shopsys\\FrameworkBundle\\Form\\FileUploadType::class') || element.type === constant('\\Shopsys\\FrameworkBundle\\Form\\ImageUploadType::class')) {
        return $(element.domNode).find('.js-file-upload-uploaded-file').toArray();
    }
    if (element.type === constant('\\Shopsys\\FrameworkBundle\\Form\\ProductsType::class')) {
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
    ckeditorValidationInit(element);

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
        haystack = [constant('\\Symfony\\Component\\Validator\\Constraint::DEFAULT_GROUP')];
    }
    return FpJsFormValidator._checkValidationGroups(needle, haystack);
};

// determine domElement as the closest ancestor of all children
FpJsFormValidator._findDomElement = FpJsFormValidator.findDomElement;
FpJsFormValidator.findDomElement = function (model) {
    return findDomElementRecursive(model);
};

FpJsFormValidator._isValueEmty = FpJsFormValidator.isValueEmty;
FpJsFormValidator.isValueEmty = function (value, element) {
    if (element instanceof FpJsFormElement) {
        if (isExpandedChoiceFormType(element, value)) {
            return isExpandedChoiceEmpty(value);
        }
    }

    return FpJsFormValidator._isValueEmty(value);
};

const _SymfonyComponentValidatorConstraintsUrl = SymfonyComponentValidatorConstraintsUrl; // eslint-disable-line no-unused-vars
SymfonyComponentValidatorConstraintsUrl = function () {
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
