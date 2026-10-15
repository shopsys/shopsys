import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';
import Register from 'framework/common/utils/Register';
import $ from 'jquery';
import collectProductIds from '../js/contentEditor/collectProductIds';

const DEBOUNCE_MS = 100;

/*
 * The products picker is plain DOM managed by jQuery, so the Live Component does not see its changes.
 * This controller pushes the chosen product ids into the Live model of the field (form/type/ProductIdsType.html.twig).
 */
export default class extends Controller {
    static values = { model: String };

    connect() {
        // the picker is a legacy jQuery component, so a widget inserted after page load must be initialised here
        new Register().registerNewContent($(this.element));

        this.lastIds = JSON.stringify(collectProductIds(this.element));
        this.timeout = null;
        this.observer = new MutationObserver(() => this.scheduleSync());
        this.observer.observe(this.element, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['name', 'value'],
        });
    }

    disconnect() {
        this.observer.disconnect();
        window.clearTimeout(this.timeout);
    }

    // the picker changes several nodes at once, so the changes are collected for a moment
    scheduleSync() {
        window.clearTimeout(this.timeout);
        this.timeout = window.setTimeout(() => this.sync(), DEBOUNCE_MS);
    }

    async sync() {
        const ids = collectProductIds(this.element);

        if (JSON.stringify(ids) === this.lastIds) {
            return;
        }

        this.lastIds = JSON.stringify(ids);
        const component = await getComponent(this.element.closest('[data-controller~="live"]'));

        component.set(this.modelValue, ids, true);
    }
}
