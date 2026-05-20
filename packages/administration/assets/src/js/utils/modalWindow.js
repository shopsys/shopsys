import Translator from 'bazinga-translator';
import AlertTriangle from 'icons/tabler/alert-triangle.svg';
import Check from 'icons/tabler/check.svg';
import ExclamationCircle from 'icons/tabler/exclamation-circle.svg';
import InfoCircleFilled from 'icons/tabler/info-circle-filled.svg';

const MODAL_SIZES = ['sm', 'md', 'lg', 'xl', 'fullscreen'];
const MODAL_STYLES = [null, 'primary', 'secondary', 'success', 'danger', 'warning', 'info'];

export default class ModalWindow {
    /**
     * @param {Object} options - Configuration options
     * @param {string} options.content - HTML content for modal body
     * @param {string} [options.title] - Optional title
     * @param {string} [options.size='sm'] - Size: sm, md, lg, xl
     * @param {Array} [options.buttons=[]] - Array of button configurations
     * @param {string|null} [options.style] - Style: primary, secondary, success, danger, warning, info
     * @param {boolean} [options.closeOnBackdropAndEscape=true] - Whether the modal can be dismissed by backdrop click or Escape
     */
    constructor(options = {}) {
        if (options.size && !MODAL_SIZES.includes(options.size)) {
            console.warn(`Invalid modal size: ${options.size}. Using default 'sm'.`);
            options.size = 'sm';
        }

        if (options.style && !MODAL_STYLES.includes(options.style)) {
            console.warn(`Invalid modal style: ${options.style}. Style will be ignored.`);
            options.style = null;
        }

        this.options = {
            content: '',
            title: null,
            size: 'sm',
            buttons: [],
            style: null,
            borderless: false,
            closeOnBackdropAndEscape: true,
            ...options,
        };

        this.create();

        this.element.modal('show');

        this.element.on('click', 'a[data-button-index][href="#"]', e => {
            e.preventDefault();
            const buttonIndex = parseInt(e.currentTarget.getAttribute('data-button-index'), 10);
            const button = this.options.buttons[buttonIndex];

            if (button?.event && typeof button.event === 'function') {
                button.event();
            }

            this.element.modal('hide');
        });
    }

    create() {
        const sizeClass = this.options.size !== 'md' ? `modal-${this.options.size}` : '';
        const statusIcon = this.getStatusIcon();

        this.element = $(`
            <div class="modal fade"
                 tabindex="-1"
                 role="dialog"
                 aria-modal="true"
                 ${this.options.closeOnBackdropAndEscape ? '' : 'data-bs-backdrop="static" data-bs-keyboard="false"'}
             >
                <div class="modal-dialog ${sizeClass} modal-dialog-centered" role="document">
                    <div class="modal-content">
                        ${this.options.style ? `<div class="modal-status bg-${this.options.style}"></div>` : ''}
                        <button type="button" class="btn-close${this.options.borderless ? ' visually-hidden' : ''}" data-bs-dismiss="modal" aria-label="${Translator.trans('Close')}"></button>
                        <div class="modal-body text-center${this.options.borderless ? ' p-0' : ''}">
                            ${statusIcon ? `<div class="text-center mb-3" aria-hidden="true">${statusIcon}</div>` : ''}
                            ${this.options.title ? `<h3>${this.options.title}</h3>` : ''}
                            ${this.options.content}
                        </div>
                        ${this.options.buttons.length > 0 ? this.createFooter() : ''}
                    </div>
                </div>
            </div>
        `);

        $('body').append(this.element);

        this.element.on('hidden.bs.modal', () => {
            this.element.remove();
        });
    }

    createFooter() {
        const buttons = this.options.buttons
            .map((btn, index) => {
                const style = btn.style || 'secondary';
                const href = btn.href || '#';
                return `<a href="${href}" class="btn btn-${style}" data-button-index="${index}">${btn.text}</a>`;
            })
            .join('');

        return `<div class="modal-footer"><div class="btn-list w-100 justify-content-around">${buttons}</div>`;
    }

    getStatusIcon() {
        if (!this.options.style) {
            return null;
        }

        const iconClasses = `icon icon-lg text-${this.options.style}`;
        const icon = {
            danger: ExclamationCircle,
            warning: AlertTriangle,
            success: Check,
            info: InfoCircleFilled,
            primary: InfoCircleFilled,
        }[this.options.style];

        return icon ? icon.replace('<svg', `<svg class="${iconClasses}"`) : null;
    }
}
