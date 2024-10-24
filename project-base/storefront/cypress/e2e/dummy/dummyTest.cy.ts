import { initializePersistStoreInLocalStorageToDefaultValues, takeSnapshotAndCompare } from '../../support';

describe('Dummy Test for blank others group visit tests with screenshots', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Dummy] dummy page visit with screenshot', function () {
        cy.visitAndWaitForStableAndInteractiveDOM('/');
        expect(1).to.equal(1);
        takeSnapshotAndCompare(this.test?.title, 'dummy page', { ensureScrollable: false });
    });
});
