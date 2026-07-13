import ConfirmWindow from '@shopsys/administration/src/js/utils/confirmWindow';
import { getComponent } from '@symfony/ux-live-component';
import { Tooltip } from '@tabler/core';
import Translator from 'bazinga-translator';
import { escapeHtml } from '../../common/utils/escapeHtml';
import Register from '../../common/utils/Register';
import FormChangeInfo from './FormChangeInfo';
import SinglePicker from './SinglePicker';

export default class OrderItems {
    static savedEventBound = false;
    static liveComponentsWithRenderHook = new WeakSet();

    constructor($container) {
        this.$container = $container;
        this.$card = $container.filterAllNodes('.js-order-items-card');
        this.$form = this.$card.closest('form');
        this.$liveComponent = this.$card.closest('[data-controller~="live"]');
        this.liveComponentPromise = null;
        this.pendingAction = false;

        if (this.$liveComponent.length === 0) {
            return;
        }

        this.bindEventHandlers();
        this.initializeProductPickers();
        this.registerLiveRenderHook();
        OrderItems.initializeDynamicElements(this.$card);
    }

    bindEventHandlers() {
        this.$form.off('change.orderItems').on('change.orderItems', event => this.onFormChange(event));
        this.$card
            .off('click.orderItems', '.js-order-items-add-item')
            .on('click.orderItems', '.js-order-items-add-item', event => this.addItem(event));
        this.$card
            .off('click.orderItems', '.js-order-item-remove')
            .on('click.orderItems', '.js-order-item-remove', event => this.onRemoveItemClick(event));
        this.$card
            .off('change.orderItems', '.js-set-prices-manually')
            .on('change.orderItems', '.js-set-prices-manually', event => {
                OrderItems.onPriceCalculationChange($(event.currentTarget).closest('.js-order-item-any'));
            });
        this.$card
            .off('change.orderItems', '.js-order-transport-row select')
            .on('change.orderItems', '.js-order-transport-row select', event => this.prefillTransport(event));
        this.$card
            .off('change.orderItems', '.js-order-payment-row select')
            .on('change.orderItems', '.js-order-payment-row select', event => this.prefillPayment(event));
    }

    onFormChange(event) {
        const $target = $(event.target);

        if ($target.is('.js-order-transport-row select, .js-order-payment-row select')) {
            return;
        }

        FormChangeInfo.showInfo();
    }

    initializeProductPickers() {
        this.$card.find('.js-order-items-add-product').each((_, element) => {
            if (element.dataset.orderItemsProductPickerInitialized === '1') {
                return;
            }

            element.dataset.orderItemsProductPickerInitialized = '1';
            // eslint-disable-next-line no-new
            new SinglePicker($(element), async productId => {
                if (await this.runLiveAction('addProduct', { productId: Number(productId) })) {
                    FormChangeInfo.showInfo();
                }
            });
        });
    }

    async addItem(event) {
        event.preventDefault();

        if (await this.runLiveAction('addItem')) {
            FormChangeInfo.showInfo();
        }
    }

    async prefillTransport(event) {
        if (await this.runLiveAction('prefillTransport', { transportId: Number($(event.currentTarget).val()) })) {
            FormChangeInfo.showInfo();
        }
    }

    async prefillPayment(event) {
        if (await this.runLiveAction('prefillPayment', { paymentId: Number($(event.currentTarget).val()) })) {
            FormChangeInfo.showInfo();
        }
    }

    onRemoveItemClick(event) {
        event.preventDefault();

        const $removeButton = $(event.currentTarget);

        if (this.pendingAction || $removeButton.hasClass('link-disabled') || $removeButton.hasClass('disabled')) {
            return;
        }

        const itemName = escapeHtml($removeButton.closest('.js-order-item').find('.js-order-item-name').val());

        ConfirmWindow.show({
            content: Translator.trans('Do you really want to remove item "<i>%itemName%</i>" from the order?', {
                itemName: itemName,
            }),
            continueEvent: async () => {
                if (
                    !(await this.runLiveAction('removeItem', {
                        itemIndex: $removeButton.data('order-item-index').toString(),
                    }))
                ) {
                    return;
                }

                FormChangeInfo.showInfo();
            },
        });
    }

    async runLiveAction(actionName, actionArgs = {}) {
        if (this.pendingAction) {
            return false;
        }

        this.pendingAction = true;

        try {
            const component = await this.getLiveComponent();

            await component.action(actionName, actionArgs);

            return true;
        } finally {
            this.pendingAction = false;
        }
    }

    getLiveComponent() {
        if (this.liveComponentPromise === null) {
            this.liveComponentPromise = getComponent(this.$liveComponent[0]);
        }

        return this.liveComponentPromise;
    }

    async registerLiveRenderHook() {
        const component = await this.getLiveComponent();

        if (OrderItems.liveComponentsWithRenderHook.has(component.element)) {
            return;
        }

        component.on('render:started', () => {
            const $orderItemsCard = $(component.element).find('.js-order-items-card');
            OrderItems.disposeSelects($orderItemsCard);
            OrderItems.disposeTooltips($orderItemsCard);
        });
        component.on('render:finished', () => {
            new Register().registerNewContent($(component.element));
        });
        OrderItems.liveComponentsWithRenderHook.add(component.element);
    }

    static onPriceCalculationChange($orderItem) {
        const setPricesManually = $orderItem.find('.js-set-prices-manually').is(':checked');
        const $settingPricesManuallyWarning = $orderItem.find('.js-setting-prices-manually-warning');

        $orderItem.find('.js-calculable-price').prop('readonly', !setPricesManually);

        if (setPricesManually) {
            $settingPricesManuallyWarning.removeClass('d-none');
            OrderItems.initializeTooltips($settingPricesManuallyWarning);

            return;
        }

        OrderItems.disposeTooltips($settingPricesManuallyWarning);
        $settingPricesManuallyWarning.addClass('d-none');
    }

    static disposeTooltips($container) {
        $container.filterAllNodes('[data-bs-toggle="tooltip"]').each(function () {
            Tooltip.getInstance(this)?.dispose();
        });
    }

    static disposeSelects($container) {
        $container.filterAllNodes('select').each(function () {
            this.tomselect?.destroy();
        });
    }

    static initializeTooltips($container) {
        $container.filterAllNodes('[data-bs-toggle="tooltip"]').each(function () {
            Tooltip.getInstance(this)?.dispose();

            const originalTitle = this.getAttribute('data-bs-original-title');

            if (!this.getAttribute('title') && originalTitle) {
                this.setAttribute('title', originalTitle);
            }

            new Tooltip(this);
        });
    }

    static initializeDynamicElements($container) {
        OrderItems.initializeTooltips($container);

        $container.filterAllNodes('.js-order-item-any').each(function () {
            OrderItems.onPriceCalculationChange($(this));
        });
    }

    static bindSavedEvent() {
        if (OrderItems.savedEventBound) {
            return;
        }

        window.addEventListener('order-detail-items:saved', () => {
            FormChangeInfo.removeInfo();
        });
        window.addEventListener('order-detail-items:cancelled', () => {
            FormChangeInfo.removeInfo();
        });
        OrderItems.savedEventBound = true;
    }

    static init($container) {
        OrderItems.bindSavedEvent();

        if ($container.filterAllNodes('.js-order-items-card').length === 0) {
            return;
        }

        // eslint-disable-next-line no-new
        new OrderItems($container);
    }
}

new Register().registerCallback(OrderItems.init, 'OrderItems.init');
