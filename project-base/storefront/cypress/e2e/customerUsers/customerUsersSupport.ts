import { b2bUrl } from 'fixtures/demodata';
import { TIDs } from 'tids';

export const visitCustomerUsersPage = () => {
    cy.visitB2bAndWaitForStableAndInteractiveDOM(b2bUrl.customer.users);
};

export const checkUsersTableIsVisible = () => {
    cy.getByTID([TIDs.customer_users_table]).should('exist').and('be.visible');
};

export const clickAddNewUserButton = () => {
    cy.getByTID([TIDs.customer_users_add_button]).should('be.visible').click();
    cy.waitForStableAndInteractiveDOM();
};

export const clickEditUserButton = () => {
    cy.getByTID([TIDs.customer_users_edit_button]).first().should('be.visible').click();
    cy.waitForStableAndInteractiveDOM();
};

export const clickEditUserButtonByEmail = (email: string) => {
    cy.getByTID([TIDs.customer_users_table])
        .contains(email)
        .closest('tr')
        .find(`[data-tid="${TIDs.customer_users_edit_button}"]`)
        .should('be.visible')
        .click();
    cy.waitForStableAndInteractiveDOM();
};

export const clickDeleteUserButton = () => {
    cy.getByTID([TIDs.customer_users_delete_button]).first().should('be.visible').click();
    cy.waitForStableAndInteractiveDOM();
};

export const clickDeleteUserButtonByEmail = (email: string) => {
    cy.getByTID([TIDs.customer_users_table])
        .contains(email)
        .closest('tr')
        .find(`[data-tid="${TIDs.customer_users_delete_button}"]`)
        .should('be.visible')
        .click();
    cy.waitForStableAndInteractiveDOM();
};

export const confirmDeleteUser = () => {
    cy.getByTID([TIDs.customer_users_delete_confirm_button]).should('be.visible').click();
    cy.waitForStableAndInteractiveDOM();
};

export const submitManageUserForm = () => {
    cy.getByTID([TIDs.customer_users_manage_submit_button]).should('be.visible').click();
    cy.waitForStableAndInteractiveDOM();
};

const FORM_PREFIX = '#customer-user-manage-profile-form-';

export const waitForRoleGroupOptionsToLoad = () => {
    cy.getByTID([TIDs.layout_popup]).find('input[type="radio"]').should('exist');
};

export const checkRoleGroupOptionsAreEnabled = () => {
    cy.getByTID([TIDs.layout_popup])
        .find('input[type="radio"]')
        .each(($radio) => {
            cy.wrap($radio).should('not.be.disabled');
        });
};

export const fillAddUserForm = (data: { email: string; firstName: string; lastName: string; telephone: string }) => {
    waitForRoleGroupOptionsToLoad();
    // Click the visible label of the first radio button instead of .check() on the sr-only hidden input,
    // because .check({ force: true }) may not properly trigger React's synthetic onChange event
    cy.getByTID([TIDs.layout_popup])
        .find('input[type="radio"]')
        .first()
        .then(($radio) => {
            const id = $radio.attr('id');
            cy.getByTID([TIDs.layout_popup]).find(`label[for="${id}"]`).click();
        });
    cy.getByTID([TIDs.layout_popup]).find('input[type="radio"]').first().should('be.checked');
    cy.get(FORM_PREFIX + 'email')
        .clear()
        .type(data.email);
    cy.get(FORM_PREFIX + 'firstName')
        .clear()
        .type(data.firstName);
    cy.get(FORM_PREFIX + 'lastName')
        .clear()
        .type(data.lastName);
    cy.get(FORM_PREFIX + 'telephone')
        .clear()
        .type(data.telephone);
};

export const fillEditUserForm = (data: { firstName: string; lastName: string; telephone: string }) => {
    waitForRoleGroupOptionsToLoad();
    cy.get(FORM_PREFIX + 'firstName')
        .clear()
        .type(data.firstName);
    cy.get(FORM_PREFIX + 'lastName')
        .clear()
        .type(data.lastName);
    cy.get(FORM_PREFIX + 'telephone')
        .clear()
        .type(data.telephone);
};
