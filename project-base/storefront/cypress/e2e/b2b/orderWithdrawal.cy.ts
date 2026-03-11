import { B2B_FOOTER_BLACKOUTS, loginAndVisitB2bPage, skipIfB2bNotConfigured } from './b2bSupport';
import { b2bUrl } from 'fixtures/demodata';
import {
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    loginAsB2bOwner,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 3;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.B2B, SUBGROUP_INDEX);

describe('Order Withdrawal (B2B) Tests', () => {
    skipIfB2bNotConfigured();

    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
        loginAsB2bOwner();
    });

    it('[Withdrawal Form] should display withdrawal form with pre-filled data', () => {
        cy.createB2bOrderForTest().then(({ urlHash }) => {
            cy.visitB2bAndWaitForStableAndInteractiveDOM(b2bUrl.order.orderWithdrawal + '/' + urlHash);
            cy.getByTID([TIDs.order_withdrawal_form]).should('exist').and('be.visible');

            cy.get('#order-withdrawal-form-email').should('not.have.value', '');
            cy.get('#order-withdrawal-form-firstName').should('not.have.value', '');
            cy.get('#order-withdrawal-form-lastName').should('not.have.value', '');

            takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'withdrawal-form-prefilled', {
                blackout: B2B_FOOTER_BLACKOUTS,
            });
        });
    });

    it('[Withdrawal Submit] should submit and redirect to success page', () => {
        cy.createB2bOrderForTest().then(({ urlHash }) => {
            cy.visitB2bAndWaitForStableAndInteractiveDOM(b2bUrl.order.orderWithdrawal + '/' + urlHash);
            cy.getByTID([TIDs.order_withdrawal_submit_button]).should('be.visible').click();
            cy.url().should('contain', b2bUrl.order.orderWithdrawalSuccess);

            takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'withdrawal-success', {
                blackout: B2B_FOOTER_BLACKOUTS,
            });
        });
    });

    it('[Withdrawal Access] should deny CatalogUser access to withdrawal page', () => {
        cy.createB2bOrderForTest().then(({ urlHash }) => {
            loginAndVisitB2bPage('catalogUser', b2bUrl.order.orderWithdrawal + '/' + urlHash, {
                failOnStatusCode: false,
            });
            cy.getByTID([TIDs.order_withdrawal_form]).should('not.exist');
        });
    });
});
