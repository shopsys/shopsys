import {
    checkManualComplaintButtonIsVisible,
    clickManualComplaintButton,
    visitComplaintsListPage,
    visitNewComplaintPage,
} from './complaintsSupport';
import { b2bUrl } from 'fixtures/demodata';
import {
    check403PageIsVisible,
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    loginAsB2bAccountant,
    loginAsB2bCatalogUser,
    loginAsB2bOwner,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 0;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.COMPLAINTS, SUBGROUP_INDEX);

describe('Complaints (B2B) Tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    describe('As B2B Owner', () => {
        beforeEach(() => {
            loginAsB2bOwner();
        });

        it('should display the complaints list page', () => {
            visitComplaintsListPage();
            cy.getByTID([TIDs.complaints_list_create_complaint_manually_button]).should('exist');
            takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'complaints-list', {
                blackout: [
                    { tid: TIDs.complaint_item_image },
                    { tid: TIDs.footer_social_links },
                    { tid: TIDs.footer_payment_images },
                    { tid: TIDs.footer_copyright },
                ],
            });
        });

        it('should navigate to the new complaint page', () => {
            visitNewComplaintPage();
            cy.waitForStableAndInteractiveDOM();
            cy.url().should('contain', b2bUrl.customer.newComplaint);
            takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'new-complaint-page', {
                blackout: [
                    { tid: TIDs.ordered_item_image },
                    { tid: TIDs.footer_social_links },
                    { tid: TIDs.footer_payment_images },
                    { tid: TIDs.footer_copyright },
                ],
            });
        });

        it('should open manual complaint creation popup and show validation errors', () => {
            visitComplaintsListPage();
            checkManualComplaintButtonIsVisible();
            clickManualComplaintButton();
            cy.getByTID([TIDs.complaint_create_submit_button]).scrollIntoView().should('be.visible').click();
            cy.getByTID([TIDs.form_line_error]).should('exist');
            cy.realPress('{esc}');
            cy.getByTID([TIDs.layout_popup]).should('not.exist');
        });
    });

    describe('Access control', () => {
        it('should allow Accountant to view complaints list', () => {
            loginAsB2bAccountant();
            visitComplaintsListPage();
            cy.url().should('contain', b2bUrl.customer.complaints);
            takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'complaints-list-accountant', {
                blackout: [
                    { tid: TIDs.complaint_item_image },
                    { tid: TIDs.footer_social_links },
                    { tid: TIDs.footer_payment_images },
                    { tid: TIDs.footer_copyright },
                ],
            });
        });

        it('should show 403 for Catalog User trying to access complaints page', () => {
            loginAsB2bCatalogUser();
            cy.visitB2bAndWaitForStableAndInteractiveDOM(b2bUrl.customer.complaints, { failOnStatusCode: false });
            check403PageIsVisible();
        });
    });
});
