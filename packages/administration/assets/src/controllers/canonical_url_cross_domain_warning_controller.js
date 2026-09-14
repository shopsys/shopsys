import { Controller } from '@hotwired/stimulus';
import Translator from 'bazinga-translator';

export default class extends Controller {
    static values = {
        domainUrl: String,
    };

    connect() {
        this.messageElement = document.createElement('span');
        this.messageElement.classList.add('text-warning', 'small', 'ms-3');

        const inputGroup = this.element.closest('.input-group');
        (inputGroup ?? this.element).after(this.messageElement);

        this.updateMessage();
    }

    disconnect() {
        this.messageElement?.remove();
    }

    updateMessage() {
        this.messageElement.textContent = this.isCrossDomain(this.element.value)
            ? Translator.trans(
                  'This is a cross-domain canonical URL – it points to a different domain than the one being edited.',
              )
            : '';
    }

    isCrossDomain(canonicalUrl) {
        if (!canonicalUrl) {
            return false;
        }

        try {
            return new URL(canonicalUrl).host !== new URL(this.domainUrlValue).host;
        } catch {
            return false;
        }
    }
}
