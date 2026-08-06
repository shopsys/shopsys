import { Controller } from '@hotwired/stimulus';

/*
 * Shows the rejection reason field only while the rejected status is selected
 * and the content change reason field only while the review content differs from its saved values.
 * Used by ProductReviewFormType.
 */
export default class extends Controller {
    static targets = ['status', 'rejectionReason', 'contentField', 'contentChangeReason'];
    static values = { rejectedStatus: String };

    connect() {
        this.updateRejectionReasonVisibility();
        this.updateContentChangeReasonVisibility();
    }

    updateRejectionReasonVisibility() {
        const isRejected = this.statusTarget.value === this.rejectedStatusValue;

        this.rejectionReasonTarget.classList.toggle('d-none', !isRejected);
    }

    updateContentChangeReasonVisibility() {
        const isVisible = this.isContentEdited() || this.hasContentChangeReasonFeedback();

        this.contentChangeReasonTarget.classList.toggle('d-none', !isVisible);
    }

    isContentEdited() {
        return this.contentFieldTargets.some(field => {
            if (field.type === 'checkbox') {
                return field.checked !== field.defaultChecked;
            }

            return field.value !== field.defaultValue;
        });
    }

    /*
     * After a failed submit the changed values become the rendered defaults, so the dirty check alone would hide
     * the field together with its validation error or the already entered reason.
     */
    hasContentChangeReasonFeedback() {
        return (
            this.contentChangeReasonTarget.querySelector('textarea')?.value !== '' ||
            this.contentChangeReasonTarget.querySelector('.invalid-feedback') !== null
        );
    }
}
