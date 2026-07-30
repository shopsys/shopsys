import { Controller } from '@hotwired/stimulus';

/*
 * Shows the rejection reason field only while the rejected status is selected.
 * Used by ProductReviewFormType.
 */
export default class extends Controller {
    static targets = ['status', 'rejectionReason'];
    static values = { rejectedStatus: String };

    connect() {
        this.updateRejectionReasonVisibility();
    }

    updateRejectionReasonVisibility() {
        const isRejected = this.statusTarget.value === this.rejectedStatusValue;

        this.rejectionReasonTarget.classList.toggle('d-none', !isRejected);
    }
}
