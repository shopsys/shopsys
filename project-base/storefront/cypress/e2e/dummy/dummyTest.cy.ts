import { initializePersistStoreInLocalStorageToDefaultValues } from '../../support';

describe('Dummy Test for blank others group', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('should always pass', () => {
        expect(1).to.equal(1);
    });
});
