import { Dropdown, Modal, Popover, Tab, Toast, Tooltip } from '@tabler/core';
import Translator from 'bazinga-translator';
import Register from 'framework/common/utils/Register';
import TomSelect from 'tom-select';
import registerTooltip from './utils/registerTooltip';

function initSelect($container) {
    $container.filterAllNodes('select').each((_key, el) => {
        const settings = {
            allowEmptyOption: true,
            maxOptions: null,
            dropdownParent: el.closest('.modal') ? null : 'body',
            plugins: {
                dropdown_input: {},
                no_backspace_delete: {},
                no_active_items: {},
            },
        };

        if (el.hasAttribute('multiple')) {
            settings.plugins.remove_button = { title: Translator.trans('Remove') };
        }

        const ts = new TomSelect(el, settings);

        ts.control_input.addEventListener('keydown', evt => {
            if (evt.key === 'Tab') {
                evt.preventDefault();
                focusNextTabableElement(ts.wrapper, evt.shiftKey);
            }
        });
    });
}

function focusNextTabableElement(referenceElement, reverse) {
    const selector =
        'a[href]:not([tabindex="-1"]), button:not(:disabled):not([tabindex="-1"]), input:not(:disabled):not([type="hidden"]):not([tabindex="-1"]), select:not(:disabled):not([tabindex="-1"]), textarea:not(:disabled):not([tabindex="-1"]), [tabindex]:not([tabindex="-1"]):not(:disabled)';

    const tabbable = [...document.querySelectorAll(selector)].filter(el => {
        if (!el.offsetParent && getComputedStyle(el).position !== 'fixed') {
            return false;
        }
        if (el.closest('.ts-dropdown')) {
            return false;
        }
        const tsWrapper = el.closest('.ts-wrapper');
        if (tsWrapper && !el.classList.contains('ts-control')) {
            return false;
        }
        return true;
    });

    const flag = reverse ? Node.DOCUMENT_POSITION_PRECEDING : Node.DOCUMENT_POSITION_FOLLOWING;

    if (reverse) {
        for (let i = tabbable.length - 1; i >= 0; i--) {
            if (referenceElement.compareDocumentPosition(tabbable[i]) & flag) {
                tabbable[i].focus();
                return;
            }
        }
    } else {
        for (const el of tabbable) {
            if (referenceElement.compareDocumentPosition(el) & flag) {
                el.focus();
                return;
            }
        }
    }
}

function initTooltip($container) {
    $container.filterAllNodes('[data-bs-toggle="tooltip"]').each(function () {
        new Tooltip(this);
    });
}

function initPopover($container) {
    const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

    $container.filterAllNodes('[data-bs-toggle="popover"]').each(function () {
        const originalTrigger = this.getAttribute('data-bs-trigger');

        if (isTouchDevice && originalTrigger && originalTrigger.includes('hover')) {
            this.setAttribute('data-bs-trigger', 'click');
        }

        new Popover(this);
    });
}

function initDropdown($container) {
    $container.filterAllNodes('[data-bs-toggle="dropdown"]').each(function () {
        new Dropdown(this, {
            popperConfig: {
                strategy: 'fixed',
            },
        });
    });
}

function initAutosize($container) {
    $container.filterAllNodes('[data-bs-toggle="autosize"]').each(function () {
        window.autosize?.(this);
    });
}

function initModal($container) {
    $container.filterAllNodes('[data-bs-toggle="modal"]').each(function () {
        new Modal(this);
    });
}

function initTab($container) {
    $container
        .filterAllNodes('[data-bs-toggle="tab"], [data-bs-toggle="pill"], [data-bs-toggle="list"]')
        .each(function () {
            new Tab(this);
        });
}

function initToast($container) {
    $container.filterAllNodes('[data-bs-toggle="toast"]').each(function () {
        new Toast(this);
    });
}

export function initComponents($container) {
    initSelect($container);
    initTooltip($container);
    initPopover($container);
    initDropdown($container);
    initAutosize($container);
    initModal($container);
    initTab($container);
    initToast($container);
    registerTooltip();
}

new Register().registerCallback(initComponents, 'initComponents');
