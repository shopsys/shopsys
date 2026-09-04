import { Controller } from '@hotwired/stimulus';

/*
 * Drives a single photo card on the product review detail. The card mirrors the "Displayed"
 * switch: while the switch is off, the photo is rejected and the card shows the rejected ribbon,
 * the dimmed thumbnail and the rejection reason field.
 * Used by ProductReviewImagesType.html.twig.
 */
export default class extends Controller {
    static targets = ['checkbox', 'label', 'reasonRow', 'ribbon', 'thumbnail'];
    static values = { displayedLabel: String, hiddenLabel: String };

    connect() {
        this.render();
    }

    render() {
        const isRejected = !this.checkboxTarget.checked;

        this.labelTarget.textContent = isRejected ? this.hiddenLabelValue : this.displayedLabelValue;
        this.ribbonTarget.classList.toggle('d-none', !isRejected);
        this.reasonRowTarget.classList.toggle('d-none', !isRejected);

        if (this.hasThumbnailTarget) {
            this.thumbnailTarget.classList.toggle('opacity-50', isRejected);
        }
    }
}
