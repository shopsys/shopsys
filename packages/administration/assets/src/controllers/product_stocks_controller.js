import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['quantity', 'totalQuantity'];

    recalculate() {
        const totalQuantitiesByDomainId = {};

        this.quantityTargets.forEach(input => {
            const quantity = parseInt(input.value, 10) || 0;

            input.dataset.domainIds
                .split(',')
                .filter(domainId => domainId !== '')
                .forEach(domainId => {
                    totalQuantitiesByDomainId[domainId] = (totalQuantitiesByDomainId[domainId] || 0) + quantity;
                });
        });

        this.totalQuantityTargets.forEach(totalQuantity => {
            const quantity = totalQuantitiesByDomainId[totalQuantity.dataset.domainId] || 0;
            const badge = totalQuantity.closest('.badge');

            totalQuantity.textContent = quantity;
            badge.classList.toggle('bg-green-lt', quantity > 0);
            badge.classList.toggle('bg-red-lt', quantity <= 0);
        });
    }
}
