import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';

export default class extends Controller {

    static targets = ['result'];

    async initialize() {
        this.component = await getComponent(this.element);
        this.currentIndex = -1;
        this.enabledKeys = ['Tab', 'Enter', 'ArrowUp', 'ArrowDown'];

        this.element.querySelector('.js-search-wrapper').addEventListener('keydown', this.onKeydown.bind(this));
    }

    onKeydown(event) {
        console.log(this.currentIndex);
        if (this.resultTargets.length === 0 || !this.enabledKeys.includes(event.key)) {
            return;
        }

        event.preventDefault();

        if (event.key === 'Enter' && this.currentIndex >= 0 && this.currentIndex < this.resultTargets.length) {
            const currentItem = this.resultTargets[this.currentIndex];
            const anchor = currentItem.querySelector('a');
            if (anchor && anchor.href) {
                anchor.click();
            }
        }

        let direction = 0;

        if (event.key === 'Tab') {
            direction = event.shiftKey ? -1 : 1;
        } else {
            direction = event.key === 'ArrowUp' ? -1 : 1;
        }

        if (this.currentIndex === -1 && direction === -1) {
            return;
        }

        if (this.currentIndex === this.resultTargets.length - 1 && direction === 1) {
            this.currentIndex = -1;
        }

        this.currentIndex += direction;

        if (this.currentIndex < 0) {
            this.element.querySelector('.js-search-input').focus();
            return;
        }

        this.resultTargets[this.currentIndex].focus();
    }
}