import { highlightSubmitButtons, findElementsToHighlight } from './validationHelpers';
import constant from '../../admin/utils/constant';
import Timeout from '../utils/Timeout';
import Window from '../../admin/utils/Window';
import Translator from 'bazinga-translator';

export default class CustomizeBundle {

    static isFormValid (form) {
        return $(form).find('.js-validation-errors-message').length === 0;
    }

    static getErrorListClass (elementName) {
        return elementName.replace(/-/g, '_')
            .replace('form_error_', 'js-validation-error-list-')
            .replace('value_to_duplicates_', 'js-validation-error-list-'); // defined in function SymfonyComponentFormExtensionCoreDataTransformerValueToDuplicatesTransformer()
    }

    static ckeditorValidationInit (element) {
        $.each(element.children, function (index, childElement) {
            if (childElement.type === constant('\\FOS\\CKEditorBundle\\Form\\Type\\CKEditorType::class')
                && CKEDITOR.instances[childElement.id]
            ) {
                CKEDITOR.instances[childElement.id].on('change', function () {
                    $(childElement.domNode).jsFormValidator('validate');
                });
            }
            if (Object.keys(childElement.children).length > 0) {
                CustomizeBundle.ckeditorValidationInit(childElement);
            }
        });
    }

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
    static elementBind (element) {
        if (!element.domNode) {
            return;
        }

        const $domNode = $(element.domNode);

        if ($domNode.closest('.js-no-validate').length > 0) {
            return;
        }

        let isJsFileUpload = false;
        if ($domNode.hasClass('js-validation-no-file-upload') === false) {
            isJsFileUpload = $domNode.closest('.js-file-upload').length > 0;
        }

        $domNode
            .bind('blur change', function (event) {
                if (this.jsFormValidator.id !== event.target.id) {
                    return;
                }

                if (this.jsFormValidator) {
                    event.preventDefault();

                    if (isJsFileUpload !== true) {
                        CustomizeBundle.validateWithParentsDelayed(this.jsFormValidator);
                    }
                }
            })
            .focus(function () {
                if (this.jsFormValidator) {
                    CustomizeBundle.removeDelayedValidationWithParents(this.jsFormValidator);
                }
                $(this).closest('.form-input-error').removeClass('form-input-error');
            })
            .jsFormValidator({
                'showErrors': CustomizeBundle.showErrors
            });
    }

    static validateWithParentsDelayed (jsFormValidator) {
        do {
            CustomizeBundle.delayedValidators[jsFormValidator.id] = jsFormValidator;
            jsFormValidator = jsFormValidator.parent;
        } while (jsFormValidator);

        Timeout.setTimeoutAndClearPrevious('Shopsys.validation.validateWithParentsDelayed', this.executeDelayedValidators, 100);
    }

    static executeDelayedValidators () {
        const validators = CustomizeBundle.delayedValidators;

        $.each(validators, function () {
            this.validate();
        });
    }

    static removeDelayedValidationWithParents (jsFormValidator) {
        do {
            delete CustomizeBundle.delayedValidators[jsFormValidator.id];
            jsFormValidator = jsFormValidator.parent;
        } while (jsFormValidator);
    }

    static findDomElementRecursive (model, fpJsFormValidator) {
        const domElement = fpJsFormValidator._findDomElement(model);

        if (domElement !== null) {
            return domElement;
        }

        const childDomElements = [];
        for (let i in model.children) {
            const child = model.children[i];
            const childDomElement = CustomizeBundle.findDomElementRecursive(child, fpJsFormValidator);

            if (childDomElement !== null) {
                childDomElements.push(childDomElement);
            }
        }

        return CustomizeBundle.findClosestCommonAncestor(childDomElements);
    }

    static findClosestCommonAncestor (domElements) {
        if (domElements.length === 0) {
            return null;
        }

        const domElementsAncestors = [];

        for (let i in domElements) {
            const domElement = domElements[i];
            const $domElementParents = $(domElement).parents();

            const domElementAncestors = CustomizeBundle.reverseCollectionToArray($domElementParents);

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
    }

    static reverseCollectionToArray ($collection) {
        const result = [];

        for (let i = $collection.length - 1; i >= 0; i--) {
            result.push($collection[i]);
        }

        return result;
    }

    static isExpandedChoiceFormType (element, value) {
        return element.type === constant('\\Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType::class') && $.isArray(value);
    }

    static isExpandedChoiceEmpty (value) {
        let isEmpty = true;

        $.each(value, function (key, element) {
            if (element !== false) {
                isEmpty = false;
                return false;
            }
        });

        return isEmpty;
    }

    static getFormattedFormErrors (container) {
        const errorsByLabel = CustomizeBundle.getFormErrorsIndexedByLabel(container);
        const $formattedFormErrors = $('<ul/>');
        for (let label in errorsByLabel) {
            const $errorsUl = $('<ul/>');
            for (let i in errorsByLabel[label]) {
                $errorsUl.append($('<li/>').text(errorsByLabel[label][i]));
            }
            $formattedFormErrors.append($('<li/>').text(label).append($errorsUl));
        }

        return $formattedFormErrors;
    }

    static getFormErrorsIndexedByLabel (container) {
        let errorsByLabel = {};

        $(container).find('.js-validation-errors-list li').each(function () {
            const $errorList = $(this).closest('.js-validation-errors-list');
            const errorMessage = $(this).text();
            const inputId = CustomizeBundle.getInputIdByErrorList($errorList);

            if (inputId !== undefined) {
                const $label = CustomizeBundle.findLabelByInputId(inputId);
                if ($label.length > 0) {
                    errorsByLabel = CustomizeBundle.addLabelError(errorsByLabel, $label.text(), errorMessage);
                }
            }
        });

        return errorsByLabel;
    }

    static getInputIdByErrorList ($errorList) {
        const inputIdMatch = $errorList.attr('class').match(/js-validation-error-list-([^\s]+)/);
        if (inputIdMatch) {
            return inputIdMatch[1];
        }

        return undefined;
    }

    static findLabelByInputId (inputId) {
        let $label = $('label[for="' + inputId + '"]');
        let $input = $('#' + inputId);

        if ($label.length === 0) {
            $label = $('#js-label-' + inputId);
        }
        if ($label.length === 0) {
            $label = CustomizeBundle.getClosestLabel($input, '.js-validation-label');
        }
        if ($label.length === 0) {
            $label = CustomizeBundle.getClosestLabel($input, 'label');
        }
        if ($label.length === 0) {
            $label = CustomizeBundle.getClosestLabel($input, '.form-full__title');
        }

        return $label;
    }

    static getClosestLabel ($input, selector) {
        const $formLine = $input.closest('.form-line:has(' + selector + '), .js-form-group:has(' + selector + '), .form-full:has(' + selector + ')');
        return $formLine.find(selector).filter(':first');
    }

    static addLabelError (errorsByLabel, labelText, errorMessage) {
        labelText = CustomizeBundle.normalizeLabelText(labelText);

        if (errorsByLabel[labelText] === undefined) {
            errorsByLabel[labelText] = [];
        }
        if (errorsByLabel[labelText].indexOf(errorMessage) === -1) {
            errorsByLabel[labelText].push(errorMessage);
        }

        return errorsByLabel;
    }

    static normalizeLabelText (labelText) {
        return labelText.replace(/^\s*(.*)[\s:*]*$/, '$1');
    }

    static showFormErrorsWindow (container) {
        const $formattedFormErrors = CustomizeBundle.getFormattedFormErrors(container);
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
    }

    static showErrors (errors, sourceId) {
        const $errorList = CustomizeBundle.findOrCreateErrorList($(this), sourceId);
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
    }

    static findOrCreateErrorList ($formInput, elementName) {
        const errorListClass = CustomizeBundle.getErrorListClass(elementName);
        let $errorList = $('.' + errorListClass);
        if ($errorList.length === 0) {
            $errorList = $($.parseHTML(
                '<div class="in-message in-message--danger js-validation-errors-list ' + errorListClass + '">'
                + '<ul class="in-message__list"></ul>'
            + '</div>'
            ));
            $errorList.insertBefore($formInput);
        }

        return $errorList;
    }
}

CustomizeBundle.delayedValidators = {};
