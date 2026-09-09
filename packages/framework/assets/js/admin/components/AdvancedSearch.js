import $ from 'jquery';
import Ajax from '../../common/utils/Ajax';
import Register from '../../common/utils/Register';

export default class AdvancedSearch {
    constructor($addRuleButton, $rulesContainer, $ruleTemplate) {
        $ruleTemplate.detach().removeClass('d-none').removeAttr('id').find('*[id]').removeAttr('id');

        let newRuleIndexCounter = 0;

        this.updateAllValuesByOperator($rulesContainer);

        $addRuleButton.click(() => {
            AdvancedSearch.addRule($rulesContainer, $ruleTemplate, `new_${newRuleIndexCounter}`);
            newRuleIndexCounter++;
            return false;
        });

        $rulesContainer.on('click', '.js-advanced-search-remove-rule-button', function () {
            $(this).closest('.js-advanced-search-rule').remove();
            return false;
        });

        $rulesContainer.on('change', 'select.js-advanced-search-rule-subject', function () {
            const $rule = $(this).closest('.js-advanced-search-rule');
            AdvancedSearch.updateRule($rulesContainer, $rule, $(this).val(), `new_${newRuleIndexCounter}`);
            newRuleIndexCounter++;
        });

        $rulesContainer.on('change', '.js-advanced-search-rule-operator', function () {
            const $rule = $(this).closest('.js-advanced-search-rule');
            AdvancedSearch.updateValueByOperator($rulesContainer, $rule, $(this).val());
        });
    }

    static updateRule($rulesContainer, $rule, filterName, newIndex) {
        Ajax.ajax({
            url: $rulesContainer.data('rule-form-url'),
            type: 'post',
            data: {
                filterName: filterName,
                newIndex: newIndex,
            },
            success: data => {
                const $newRule = $($.parseHTML(data));
                $rule.replaceWith($newRule);

                new Register().registerNewContent($newRule);
            },
        });
    }

    static addRule($rulesContainer, $ruleTemplate, newIndex) {
        const ruleHtml = $ruleTemplate
            .clone()
            .wrap('<div>')
            .parent()
            .html()
            .replace(/__template__/g, newIndex);
        const $rule = $($.parseHTML(ruleHtml));
        $rule.appendTo($rulesContainer);

        new Register().registerNewContent($rule);
    }

    updateAllValuesByOperator($rulesContainer) {
        $rulesContainer.find('.js-advanced-search-rule').each(function () {
            const operator = $(this).find('.js-advanced-search-rule-operator').val();
            AdvancedSearch.updateValueByOperator($rulesContainer, $(this), operator);
        });
    }

    static updateValueByOperator(_$rulesContainer, $rule, operator) {
        $rule.find('.js-advanced-search-rule-value').toggle(operator !== 'notSet' && operator !== 'notRegistered');
    }

    static init($container) {
        const $addRuleButton = $container.filterAllNodes('#js-advanced-search-add-rule-button');
        const $rulesContainer = $container.filterAllNodes('#js-advanced-search-rules-container');
        const $ruleTemplate = $container.filterAllNodes('#js-advanced-search-rule-template');

        if ($addRuleButton.length > 0 && $rulesContainer.length > 0 && $ruleTemplate.length > 0) {
            // eslint-disable-next-line no-new
            new AdvancedSearch($addRuleButton, $rulesContainer, $ruleTemplate);
        }
    }
}

new Register().registerCallback(AdvancedSearch.init, 'AdvancedSearch.init');
