import { Controller } from '@hotwired/stimulus';
import Translator from 'bazinga-translator';
import Register from 'framework/common/utils/Register';

const TEMPLATE_INDEX_PATTERN = /__template__/g;
const NEW_INDEX_PATTERN = /new_(\d+)/g;

export default class extends Controller {
    static targets = ['rulesContainer', 'ruleTemplate', 'rule', 'value'];
    static values = {
        ruleFormUrl: String,
        valuelessOperators: Array,
    };

    connect() {
        this.newIndexCounter = this.computeInitialIndexCounter();
        this.updateAllValueVisibility();
    }

    addRule() {
        const ruleHtml = this.ruleTemplateTarget.innerHTML.replace(TEMPLATE_INDEX_PATTERN, this.nextIndex());
        const newRule = this.createRuleElement(ruleHtml);
        this.rulesContainerTarget.appendChild(newRule);
        this.initializeRuleContent(newRule);
    }

    removeRule(event) {
        this.findRule(event.target).remove();
    }

    changeOperator() {
        this.updateAllValueVisibility();
    }

    changeSubject(event) {
        const rule = this.findRule(event.target);
        const body = new URLSearchParams({
            filterName: event.target.value,
            newIndex: this.nextIndex(),
        });

        fetch(this.ruleFormUrlValue, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body,
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Unable to load the advanced search rule form: ${response.status}`);
                }

                return response.text();
            })
            .then(html => {
                const newRule = this.createRuleElement(html);
                rule.replaceWith(newRule);
                this.initializeRuleContent(newRule);
            })
            .catch(() => {
                rule.insertAdjacentHTML(
                    'beforebegin',
                    `<div class="alert alert-danger">${Translator.trans('The search rule could not be loaded.')}</div>`
                );
                rule.remove();
            });
    }

    createRuleElement(html) {
        const template = document.createElement('template');
        template.innerHTML = html.trim();

        return template.content.firstElementChild;
    }

    initializeRuleContent(rule) {
        // dynamically added content must be registered so components like TomSelect initialize on it
        new Register().registerNewContent($(rule));
        this.updateAllValueVisibility();
    }

    updateAllValueVisibility() {
        this.ruleTargets.forEach(rule => {
            const operator = rule.querySelector('select[name*="[operator]"]')?.value;
            const valueElement = rule.querySelector('[data-advanced-search-target~="value"]');

            valueElement?.classList.toggle('d-none', this.valuelessOperatorsValue.includes(operator));
        });
    }

    findRule(element) {
        return element.closest('[data-advanced-search-target~="rule"]');
    }

    nextIndex() {
        return `new_${this.newIndexCounter++}`;
    }

    computeInitialIndexCounter() {
        let maxUsedIndex = -1;

        for (const match of this.rulesContainerTarget.innerHTML.matchAll(NEW_INDEX_PATTERN)) {
            maxUsedIndex = Math.max(maxUsedIndex, Number.parseInt(match[1], 10));
        }

        return maxUsedIndex + 1;
    }
}
