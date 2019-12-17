import $ from 'jquery';
import 'select2';
import Ajax from '../common/ajax';
import Register from '../common/register';
import constant from './constant';

export default class AdvancedSearch {

    constructor ($addRuleButton, $rulesContainer, $ruleTemplate) {
        $ruleTemplate.detach().removeClass('display-none').removeAttr('id').find('*[id]').removeAttr('id');
        $ruleTemplate.find('select.select2-hidden-accessible').select2('destroy');

        let newRuleIndexCounter = 0;

        this.updateAllValuesByOperator($rulesContainer);

        $addRuleButton.click(function () {
            AdvancedSearch.addRule($rulesContainer, $ruleTemplate, 'new_' + newRuleIndexCounter);
            newRuleIndexCounter++;
            return false;
        });

        $rulesContainer.on('click', '.js-advanced-search-remove-rule-button', function () {
            $(this).closest('.js-advanced-search-rule').remove();
            return false;
        });

        $rulesContainer.on('change', '.js-advanced-search-rule-subject', function () {
            const $rule = $(this).closest('.js-advanced-search-rule');
            AdvancedSearch.updateRule($rulesContainer, $rule, $(this).val(), 'new_' + newRuleIndexCounter);
            newRuleIndexCounter++;
        });

        $rulesContainer.on('change', '.js-advanced-search-rule-operator', function () {
            const $rule = $(this).closest('.js-advanced-search-rule');
            AdvancedSearch.updateValueByOperator($rulesContainer, $rule, $(this).val());
        });
    };

    static updateRule ($rulesContainer, $rule, filterName, newIndex) {
        $rule.addClass('in-disabled');
        Ajax.ajax({
            loaderElement: '#js-advanced-search-rules-box',
            url: $rulesContainer.data('rule-form-url'),
            type: 'post',
            data: {
                filterName: filterName,
                newIndex: newIndex
            },
            success: function (data) {
                const $newRule = $($.parseHTML(data));
                $rule.replaceWith($newRule);

                (new Register()).registerNewContent($newRule);
            }
        });
    };

    static addRule ($rulesContainer, $ruleTemplate, newIndex) {
        const ruleHtml = $ruleTemplate.clone().wrap('<div>').parent().html().replace(/__template__/g, newIndex);
        const $rule = $($.parseHTML(ruleHtml));
        $rule.appendTo($rulesContainer);

        (new Register()).registerNewContent($rule);
    };

    updateAllValuesByOperator ($rulesContainer) {
        $rulesContainer.find('.js-advanced-search-rule').each(function () {
            const operator = $(this).find('.js-advanced-search-rule-operator').val();
            AdvancedSearch.updateValueByOperator($rulesContainer, $(this), operator);
        });
    };

    static updateValueByOperator ($rulesContainer, $rule, operator) {
        $rule.find('.js-advanced-search-rule-value').toggle(operator !== constant('\\Shopsys\\FrameworkBundle\\Model\\AdvancedSearch\\AdvancedSearchFilterInterface::OPERATOR_NOT_SET'));
    };

    static init ($container) {
        const $addRuleButton = $container.filterAllNodes('#js-advanced-search-add-rule-button');
        const $rulesContainer = $container.filterAllNodes('#js-advanced-search-rules-container');
        const $ruleTemplate = $container.filterAllNodes('#js-advanced-search-rule-template');

        if ($addRuleButton.length > 0 && $rulesContainer.length > 0 && $ruleTemplate.length > 0) {
            // eslint-disable-next-line no-new
            new AdvancedSearch($addRuleButton, $rulesContainer, $ruleTemplate);
        }
    }

}

(new Register()).registerCallback(AdvancedSearch.init);
