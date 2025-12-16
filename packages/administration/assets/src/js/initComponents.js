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
            dropdownParent: 'body',
            plugins: {
                dropdown_input: {},
                no_backspace_delete: {},
                no_active_items: {},
            },
        };

        if (el.hasAttribute('multiple')) {
            settings.plugins.remove_button = { title: Translator.trans('Remove') };
        }

        new TomSelect(el, settings);
    });
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
