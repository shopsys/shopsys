import { B2B_FOOTER_BLACKOUTS, loginAndVisitB2bPage, skipIfB2bNotConfigured } from './b2bSupport';
import { b2bUrl } from 'fixtures/demodata';
import {
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 1;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.B2B, SUBGROUP_INDEX);

const FORM_PREFIX = '#customer-change-profile-form-';

describe('Edit Profile B2B Restrictions', () => {
    skipIfB2bNotConfigured();

    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    describe('As B2B Owner (can manage company data)', () => {
        beforeEach(() => {
            loginAndVisitB2bPage('owner', b2bUrl.customer.editProfile);
        });

        it('should show the save profile button', () => {
            cy.getByTID([TIDs.edit_profile_save_button]).should('exist').and('be.visible');
            takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'edit-profile-owner', {
                blackout: B2B_FOOTER_BLACKOUTS,
            });
        });

        it('should have enabled company data fields', () => {
            cy.get(FORM_PREFIX + 'street').should('not.be.disabled');
            cy.get(FORM_PREFIX + 'city').should('not.be.disabled');
            cy.get(FORM_PREFIX + 'postcode').should('not.be.disabled');
        });
    });

    describe('As B2B User (cannot manage company data)', () => {
        beforeEach(() => {
            loginAndVisitB2bPage('user', b2bUrl.customer.editProfile);
        });

        it('should show the save profile button (can manage personal data)', () => {
            cy.getByTID([TIDs.edit_profile_save_button]).should('exist').and('be.visible');
        });

        it('should have disabled company data fields', () => {
            cy.get(FORM_PREFIX + 'street').should('be.disabled');
            cy.get(FORM_PREFIX + 'city').should('be.disabled');
            cy.get(FORM_PREFIX + 'postcode').should('be.disabled');
            takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'edit-profile-user-disabled-fields', {
                blackout: B2B_FOOTER_BLACKOUTS,
            });
        });
    });

    describe('As B2B Limited User (can manage personal data, cannot manage company data)', () => {
        beforeEach(() => {
            loginAndVisitB2bPage('limitedUser', b2bUrl.customer.editProfile);
        });

        it('should show the save profile button', () => {
            cy.getByTID([TIDs.edit_profile_save_button]).should('exist').and('be.visible');
            takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'edit-profile-limited-user', {
                blackout: B2B_FOOTER_BLACKOUTS,
            });
        });

        it('should have disabled company data fields', () => {
            cy.get(FORM_PREFIX + 'street').should('be.disabled');
            cy.get(FORM_PREFIX + 'city').should('be.disabled');
            cy.get(FORM_PREFIX + 'postcode').should('be.disabled');
        });
    });
});
