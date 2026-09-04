import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['typeSelect', 'carrierSchedule', 'pickupScheduleInfo'];
    static values = { personalPickupType: String };

    connect() {
        this.updateScheduleFieldsVisibility();
    }

    updateScheduleFieldsVisibility() {
        const isPersonalPickup = this.typeSelectTarget.value === this.personalPickupTypeValue;

        this.carrierScheduleTargets.forEach(row => {
            row.classList.toggle('d-none', isPersonalPickup);
        });
        this.pickupScheduleInfoTargets.forEach(row => {
            row.classList.toggle('d-none', !isPersonalPickup);
        });
    }
}
