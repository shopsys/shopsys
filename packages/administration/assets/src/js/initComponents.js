import { Dropdown, Modal, Popover, Tab, Tooltip } from '@tabler/core';
import Translator from 'bazinga-translator';
import Register from 'framework/common/utils/Register';
import TomSelect from 'tom-select';
import registerTooltip from './utils/registerTooltip';

function initSelect($container) {
    $container.filterAllNodes('select').each((_key, el) => {
        const modalContent = el.closest('.modal-content');
        const settings = {
            allowEmptyOption: true,
            maxOptions: null,
            dropdownParent: modalContent ?? 'body',
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

        if (modalContent) {
            positionModalSelectDropdown(ts, modalContent);
        }

        ts.control_input.addEventListener('keydown', evt => {
            if (evt.key === 'Tab') {
                evt.preventDefault();
                focusNextTabableElement(ts.wrapper, evt.shiftKey);
            }
        });
    });
}

function positionModalSelectDropdown(ts, modalContent) {
    const listenerOptions = { capture: true, passive: true };
    const scrollTargets = new Set(
        [
            modalContent.querySelector('.modal-body'),
            modalContent.closest('.modal'),
            window,
            window.visualViewport,
        ].filter(Boolean),
    );
    const closeDropdownOnPageScroll = event => {
        if (event.target instanceof Node && ts.dropdown.contains(event.target)) {
            return;
        }

        if (ts.isOpen) {
            ts.close();
        }
    };
    const addDropdownCloseListeners = () => {
        scrollTargets.forEach(scrollTarget => {
            scrollTarget.addEventListener('scroll', closeDropdownOnPageScroll, listenerOptions);
        });
        window.addEventListener('touchmove', closeDropdownOnPageScroll, listenerOptions);
        window.addEventListener('wheel', closeDropdownOnPageScroll, listenerOptions);
    };
    const removeDropdownCloseListeners = () => {
        scrollTargets.forEach(scrollTarget => {
            scrollTarget.removeEventListener('scroll', closeDropdownOnPageScroll, listenerOptions);
        });
        window.removeEventListener('touchmove', closeDropdownOnPageScroll, listenerOptions);
        window.removeEventListener('wheel', closeDropdownOnPageScroll, listenerOptions);
    };
    const destroy = ts.destroy.bind(ts);

    ts.positionDropdown = () => {
        const controlRect = ts.control.getBoundingClientRect();
        const viewportPadding = 8;
        const dropdownHeight = ts.dropdown.offsetHeight;
        const spaceBelow = window.innerHeight - controlRect.bottom - viewportPadding;
        const spaceAbove = controlRect.top - viewportPadding;
        const shouldOpenUp = dropdownHeight > spaceBelow && spaceAbove > spaceBelow;
        const top = shouldOpenUp
            ? Math.max(viewportPadding, controlRect.top - Math.min(dropdownHeight, spaceAbove))
            : controlRect.bottom;

        Object.assign(ts.dropdown.style, {
            position: 'fixed',
            width: `${controlRect.width}px`,
            left: `${controlRect.left}px`,
            top: `${top}px`,
        });
    };

    ts.on('dropdown_open', addDropdownCloseListeners);
    ts.on('dropdown_close', removeDropdownCloseListeners);
    ts.destroy = () => {
        removeDropdownCloseListeners();
        destroy();
    };
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

        if (isTouchDevice && originalTrigger?.includes('hover')) {
            this.setAttribute('data-bs-trigger', 'click');
        }

        const popover = new Popover(this, {
            allowList: {
                ...Popover.Default.allowList,
                span: ['class', 'title', 'data-bs-toggle'],
            },
        });

        this.addEventListener('shown.bs.popover', () => {
            if (popover.tip) {
                popover.tip.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                    new Tooltip(el);
                });
            }
        });
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

export function initComponents($container) {
    initSelect($container);
    initTooltip($container);
    initPopover($container);
    initDropdown($container);
    initAutosize($container);
    initModal($container);
    initTab($container);
    registerTooltip();
}

new Register().registerCallback(initComponents, 'initComponents');
