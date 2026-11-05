import { Controller } from '@hotwired/stimulus';
import { Dropdown } from '@tabler/core';
import {
    clearFlashMessageHistory,
    flashMessageHistoryChangedEvent,
    getFlashMessageHistory,
} from '../js/utils/flashMessageHistory';

export default class extends Controller {
    static targets = ['toggle', 'list', 'itemTemplate'];

    connect() {
        this.render = this.render.bind(this);
        document.addEventListener(flashMessageHistoryChangedEvent, this.render);
        this.render();
    }

    disconnect() {
        document.removeEventListener(flashMessageHistoryChangedEvent, this.render);
    }

    clear() {
        clearFlashMessageHistory();
    }

    render() {
        const entries = getFlashMessageHistory();
        const timeFormatter = new Intl.DateTimeFormat(document.documentElement.lang || undefined, {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
        });

        // a disabled toggle cannot be hidden by Bootstrap anymore, so the open menu must be closed first
        if (entries.length === 0) {
            Dropdown.getInstance(this.toggleTarget)?.hide();
        }

        this.toggleTarget.classList.toggle('disabled', entries.length === 0);
        this.toggleTarget.setAttribute('aria-disabled', entries.length === 0 ? 'true' : 'false');

        this.listTarget.replaceChildren(
            ...entries.map(entry => {
                const item = this.itemTemplateTarget.content.firstElementChild.cloneNode(true);

                item.querySelector('[data-history-status]').classList.add(entry.bgClass ?? 'bg-secondary');
                item.querySelector('[data-history-time]').textContent = timeFormatter.format(entry.createdAt);
                item.querySelector('[data-history-message]').innerHTML = entry.messageHtml;

                return item;
            }),
        );
    }
}
