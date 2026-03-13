import { b2bUrl } from 'fixtures/demodata';
import { TIDs } from 'tids';

export const visitComplaintsListPage = () => {
    cy.visitB2bAndWaitForStableAndInteractiveDOM(b2bUrl.customer.complaints);
};

export const visitNewComplaintPage = () => {
    cy.visitB2bAndWaitForStableAndInteractiveDOM(b2bUrl.customer.newComplaint);
};

export const checkManualComplaintButtonIsVisible = () => {
    cy.getByTID([TIDs.complaints_list_create_complaint_manually_button]).should('be.visible');
};

export const clickManualComplaintButton = () => {
    cy.getByTID([TIDs.complaints_list_create_complaint_manually_button]).should('be.visible').click();
    cy.waitForStableAndInteractiveDOM();
};
