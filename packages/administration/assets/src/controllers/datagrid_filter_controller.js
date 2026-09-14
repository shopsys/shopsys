import { Controller } from '@hotwired/stimulus';
import Register from 'framework/common/utils/Register';

/**
 * The filter of a datagrid: adds and removes groups and rules, and swaps the operation select and the
 * value input of a rule when its filter or operation changes. Everything is cloned from the prototypes
 * the form renders, so no request is needed until the filter is applied.
 */
export default class extends Controller {
    static targets = [
        'groups',
        'group',
        'rules',
        'rule',
        'ruleFields',
        'value',
        'groupTemplate',
        'ruleTemplate',
        'prototype',
        'groupsOperator',
    ];

    static values = {
        groupPrototypeName: { type: String, default: '__group__' },
        rulePrototypeName: { type: String, default: '__rule__' },
        prototypesName: { type: String, default: 'prototypes' },
    };

    connect() {
        this.ruleTargets.forEach(rule => {
            this.markValueArity(rule);
        });
        this.updateGroupsOperatorVisibility();
    }

    addGroup() {
        const groupIndex = this.nextIndex(this.groupTargets, /\[groups\]\[(\d+)\]/);
        const group = this.createElement(
            this.groupTemplateTarget.innerHTML.replaceAll(this.groupPrototypeNameValue, groupIndex),
        );

        this.groupsTarget.appendChild(group);
        this.appendRule(group);
        this.registerContent(group);
        this.updateGroupsOperatorVisibility();
    }

    removeGroup(event) {
        event.currentTarget.closest('[data-datagrid-filter-target~="group"]').remove();
        this.updateGroupsOperatorVisibility();
    }

    addRule(event) {
        const group = event.currentTarget.closest('[data-datagrid-filter-target~="group"]');

        this.registerContent(this.appendRule(group));
    }

    removeRule(event) {
        event.currentTarget.closest('[data-datagrid-filter-target~="rule"]').remove();
    }

    changeFilter(event) {
        const rule = event.currentTarget.closest('[data-datagrid-filter-target~="rule"]');
        const prototype = this.findPrototype(event.currentTarget.value, null);

        if (prototype === null) {
            return;
        }

        const ruleFields = rule.querySelector('[data-datagrid-filter-target~="ruleFields"]');
        ruleFields.innerHTML = this.renamePrototype(prototype, rule);
        this.markValueArity(rule);
        this.registerContent(ruleFields);
    }

    changeOperator(event) {
        const rule = event.currentTarget.closest('[data-datagrid-filter-target~="rule"]');
        const arity = event.currentTarget.selectedOptions[0]?.dataset.arity;
        const value = rule.querySelector('[data-datagrid-filter-target~="value"]');

        if (value.dataset.arity === arity) {
            return;
        }

        const filterName = rule.querySelector('[data-datagrid-filter-target~="filterSelect"]').value;
        const prototype = this.findPrototype(filterName, arity);
        const prototypeValue = this.createElement(`<div>${this.renamePrototype(prototype, rule)}</div>`).querySelector(
            '[data-datagrid-filter-target~="value"]',
        );

        value.replaceWith(prototypeValue);
        this.markValueArity(rule);
        this.registerContent(prototypeValue);
    }

    appendRule(group) {
        const groupIndex = this.indexOf(group, /\[groups\]\[(\d+)\]/);
        const rulesContainer = group.querySelector('[data-datagrid-filter-target~="rules"]');
        const ruleIndex = this.nextIndex(Array.from(rulesContainer.children), /\[rules\]\[(\d+)\]/);
        const html = this.ruleTemplateTarget.innerHTML
            .replaceAll(this.groupPrototypeNameValue, groupIndex)
            .replaceAll(this.rulePrototypeNameValue, ruleIndex);
        const rule = this.createElement(html);

        rulesContainer.appendChild(rule);
        this.markValueArity(rule);

        return rule;
    }

    /**
     * The first prototype of the filter is the one of its first operation, which is what a fresh rule starts with.
     */
    findPrototype(filterName, arity) {
        return (
            this.prototypeTargets.find(
                prototype =>
                    prototype.dataset.filter === filterName && (arity === null || prototype.dataset.arity === arity),
            ) ?? null
        );
    }

    /**
     * A prototype is named `<form>[prototypes][<filter>][<arity>][...]`, a rule `<form>[groups][g][rules][r][...]`.
     */
    renamePrototype(prototype, rule) {
        const filterName = prototype.dataset.filter;
        const arity = prototype.dataset.arity;
        const groupIndex = this.indexOf(rule, /\[groups\]\[(\d+)\]/);
        const ruleIndex = this.indexOf(rule, /\[rules\]\[(\d+)\]/);

        return prototype.innerHTML
            .replaceAll(
                `[${this.prototypesNameValue}][${filterName}][${arity}]`,
                `[groups][${groupIndex}][rules][${ruleIndex}]`,
            )
            .replaceAll(
                `_${this.prototypesNameValue}_${filterName}_${arity}`,
                `_groups_${groupIndex}_rules_${ruleIndex}`,
            );
    }

    markValueArity(rule) {
        const operatorSelect = rule.querySelector('[data-datagrid-filter-target~="operatorSelect"]');
        const value = rule.querySelector('[data-datagrid-filter-target~="value"]');

        if (operatorSelect && value) {
            value.dataset.arity = operatorSelect.selectedOptions[0]?.dataset.arity ?? '';
        }
    }

    indexOf(element, pattern) {
        const named = element.querySelector('[name]');
        const match = named?.getAttribute('name').match(pattern);

        return match ? match[1] : '0';
    }

    nextIndex(elements, pattern) {
        const indexes = elements.map(element => Number(this.indexOf(element, pattern)));

        return indexes.length > 0 ? Math.max(...indexes) + 1 : 0;
    }

    updateGroupsOperatorVisibility() {
        if (this.hasGroupsOperatorTarget) {
            this.groupsOperatorTarget.hidden = this.groupTargets.length < 2;
        }
    }

    createElement(html) {
        const template = document.createElement('template');
        template.innerHTML = html.trim();

        return template.content.firstElementChild;
    }

    /**
     * The legacy components of the administration (the date picker, the product picker) bind themselves
     * to new content through the register.
     */
    registerContent(element) {
        const jquery = window.jQuery ?? window.$;

        if (jquery) {
            new Register().registerNewContent(jquery(element));
        }
    }
}
