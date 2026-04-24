import { b2bUrl, staticData } from 'fixtures/demodata';
import { changeElementText } from 'support';
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

export const changeComplaintsListDynamicPartsToStaticDemodata = () => {
    changeElementText(TIDs.complaint_list_item_number, staticData.complaint.number, false);
    changeElementText(TIDs.complaint_list_item_date, staticData.complaint.creationDate, false);
};

export const changeNewComplaintPageDynamicPartsToStaticDemodata = () => {
    changeElementText(TIDs.ordered_item_number, staticData.order.number, false);
    changeElementText(TIDs.ordered_item_date, staticData.order.creationDate, false);
};
