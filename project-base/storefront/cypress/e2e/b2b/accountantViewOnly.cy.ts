import {
    B2B_FOOTER_BLACKOUTS,
    expectB2bPageForbiddenForRole,
    loginAndVisitB2bPage,
    skipIfB2bNotConfigured,
} from './b2bSupport';
import { b2bUrl } from 'fixtures/demodata';
import {
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 2;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.B2B, SUBGROUP_INDEX);

describe('Accountant View-Only (B2B) Tests', () => {
    skipIfB2bNotConfigured();

    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    describe('Order viewing', () => {
        it('should be able to view the orders list page', () => {
            loginAndVisitB2bPage('accountant', b2bUrl.customer.orders);
            cy.url().should('contain', b2bUrl.customer.orders);
            takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'accountant-orders-list', {
                blackout: [
                    ...B2B_FOOTER_BLACKOUTS,
                    { tid: TIDs.ordered_item_image },
                    { tid: TIDs.order_list_product_image },
                    { tid: TIDs.order_list_transport_and_payment_image },
                ],
            });
        });
    });

    describe('Complaint viewing', () => {
        it('should be able to view the complaints list page', () => {
            loginAndVisitB2bPage('accountant', b2bUrl.customer.complaints);
            cy.url().should('contain', b2bUrl.customer.complaints);
            takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'accountant-complaints-list', {
                blackout: [...B2B_FOOTER_BLACKOUTS, { tid: TIDs.complaint_item_image }],
            });
        });

        it('should NOT see the create complaint manually button', () => {
            loginAndVisitB2bPage('accountant', b2bUrl.customer.complaints);
            cy.getByTID([TIDs.complaints_list_create_complaint_manually_button]).should('not.exist');
        });
    });

    describe('Cart and order creation restrictions', () => {
        it('should not show add-to-cart buttons on the B2B homepage', () => {
            loginAndVisitB2bPage('accountant', '/');
            cy.getByTID([TIDs.blocks_product_addtocart]).should('not.exist');
        });
    });

    describe('User management restrictions', () => {
        it('should NOT be able to access customer users page', () => {
            expectB2bPageForbiddenForRole('accountant', b2bUrl.customer.users);
        });
    });
});
