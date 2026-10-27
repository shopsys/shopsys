import { Controller } from '@hotwired/stimulus';
import Register from 'framework/common/utils/Register';
import $ from 'jquery';

export default class extends Controller {
    static targets = ['entries', 'entry', 'empty'];

    static values = {
        prototype: String,
        prototypeName: { type: String, default: '__name__' },
        index: Number,
    };

    connect() {
        this.toggleEmptyMessage();
    }

    add(event) {
        event.preventDefault();

        const index = this.indexValue;
        const html = this.prototypeValue
            .replaceAll(`${this.prototypeNameValue}label__`, index)
            .replaceAll(this.prototypeNameValue, index);

        const template = document.createElement('template');
        template.innerHTML = html.trim();
        const entry = template.content.firstElementChild;

        this.entriesTarget.appendChild(entry);
        this.indexValue = index + 1;

        new Register().registerNewContent($(entry));

        this.dispatch('added', { detail: { entry, index } });
        this.notifyFormChange();
    }

    remove(event) {
        event.preventDefault();

        const entry = this.entryTargets.find(entryTarget => entryTarget.contains(event.currentTarget));
        entry.remove();

        this.dispatch('removed', { detail: { entry } });
        this.notifyFormChange();
    }

    entryTargetConnected() {
        this.toggleEmptyMessage();
    }

    entryTargetDisconnected() {
        this.toggleEmptyMessage();
    }

    toggleEmptyMessage() {
        if (this.hasEmptyTarget) {
            this.emptyTarget.hidden = this.entryTargets.length > 0;
        }
    }

    notifyFormChange() {
        this.element.dispatchEvent(new Event('change', { bubbles: true }));
    }
}
