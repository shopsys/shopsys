import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        domainUrl: String,
        allDomainUrls: Array,
        message: String,
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
        this.messageElement.textContent = this.isCrossDomain(this.element.value) ? this.messageValue : '';
    }

    isCrossDomain(canonicalUrl) {
        if (!canonicalUrl) {
            return false;
        }

        let canonical;

        try {
            canonical = new URL(canonicalUrl);
        } catch {
            return false;
        }

        // Domains may share a host and differ only by a path prefix (e.g. "https://example.com" and
        // "https://example.com/sk"), so the URL belongs to the domain with the longest matching prefix.
        const matchingDomainUrl = this.allDomainUrlsValue
            .filter(domainUrl => this.belongsToDomain(canonical, new URL(domainUrl)))
            .sort((a, b) => b.length - a.length)[0];

        return matchingDomainUrl !== this.domainUrlValue;
    }

    belongsToDomain(canonical, domain) {
        if (canonical.host !== domain.host) {
            return false;
        }

        const domainPath = domain.pathname.replace(/\/$/, '');

        return canonical.pathname === domainPath || canonical.pathname.startsWith(`${domainPath}/`);
    }
}
