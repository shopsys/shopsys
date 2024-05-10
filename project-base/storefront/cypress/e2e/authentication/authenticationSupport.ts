import { buttonName, placeholder, url } from 'fixtures/demodata';
import { generateCustomerRegistrationData } from 'fixtures/generators';
import { checkUrl } from 'support';
import { TIDs } from 'tids';

export const clickOnUserIconInHeader = () => {
    cy.getByTID([TIDs.layout_header_menuiconic_login_link_popup]).click();
};

export const goToRegistrationPageFromHeader = () => {
    clickOnUserIconInHeader();
    cy.getByTID([TIDs.login_popup_register_button]).click();
    checkUrl(url.registration);
    cy.waitForStableAndInteractiveDOM();
};

export const submitRegistrationForm = () => {
    cy.getByTID([TIDs.registration_submit_button]).click();
};

export const submitLoginForm = () => {
    cy.getByTID([TIDs.login_form_submit_button]).click();
};

export const logoutFromCustomerPage = () => {
    cy.getByTID([TIDs.customer_page_logout]).click();
};

export const loginFromHeader = (email: string | undefined, password: string) => {
    clickOnUserIconInHeader();
    fillInEmailAndPasswordInLoginPopup(email, password);
    submitLoginForm();
};

export const logoutFromHeader = () => {
    cy.getByTID([TIDs.my_account_link])
        .should('be.visible')
        .realHover()
        .then(() => cy.getByTID([TIDs.header_logout]).should('be.visible').click());
};

export const fillInEmailAndPasswordInLoginPopup = (email: string | undefined, password: string) => {
    const getLoginPopup = () => cy.getByTID([TIDs.layout_popup]);
    if (email) {
        getLoginPopup().get('#login-form-email').type(email, { force: true });
    }
    getLoginPopup().get('#login-form-password').type(password, { force: true });
};

export const fillInEmailAndPasswordOnLoginPage = (email: string, password: string) => {
    cy.get('#login-form-email').type(email, { force: true });
    cy.get('#login-form-password').type(password, { force: true });
};

export const clearAndFillInRegstrationFormEmail = (email: string) => {
    cy.get('#registration-form-email').should('have.attr', 'placeholder', placeholder.email).clear().type(email);
};

export const fillInRegstrationForm = (custmerType: 'commonCustomer' | 'companyCustomer', email: string) => {
    const generatedData = generateCustomerRegistrationData(custmerType, email);

    cy.get('#registration-form-firstName')
        .should('have.attr', 'placeholder', placeholder.firstName)
        .type(generatedData.firstName);
    cy.get('#registration-form-lastName')
        .should('have.attr', 'placeholder', placeholder.lastName)
        .type(generatedData.lastName);
    cy.get('#registration-form-telephone')
        .should('have.attr', 'placeholder', placeholder.phone)
        .type(generatedData.telephone);

    if (
        custmerType === 'companyCustomer' &&
        generatedData.companyName &&
        generatedData.companyNumber &&
        generatedData.companyTaxNumber
    ) {
        cy.get('[for="registration-formcustomer1"]').click();

        cy.get('#registration-form-companyName')
            .should('have.attr', 'placeholder', placeholder.companyName)
            .type(generatedData.companyName);
        cy.get('#registration-form-companyNumber')
            .should('have.attr', 'placeholder', placeholder.companyNumber)
            .type(generatedData.companyNumber);
        cy.get('#registration-form-companyTaxNumber')
            .should('have.attr', 'placeholder', placeholder.companyTaxNumber)
            .type(generatedData.companyTaxNumber);
    } else {
        cy.get('[for="registration-formcustomer0"]').click();
    }

    cy.get('#registration-form-street')
        .should('have.attr', 'placeholder', placeholder.street)
        .type(generatedData.street);
    cy.get('#registration-form-city').should('have.attr', 'placeholder', placeholder.city).type(generatedData.city);
    cy.get('#registration-form-postcode')
        .should('have.attr', 'placeholder', placeholder.postCode)
        .type(generatedData.postcode, { force: true });

    cy.get('[for="registration-form-gdprAgreement"]').find('div').first().click();
};

export const clearAndFillInRegistrationFormPasswords = (password: string) => {
    cy.get('#registration-form-password')
        .should('have.attr', 'placeholder', placeholder.password)
        .clear()
        .type(password);
    cy.get('#registration-form-passwordConfirm')
        .should('have.attr', 'placeholder', placeholder.passwordAgain)
        .clear({ force: true })
        .type(password);
};

export const checkRegistrationValidationErrorsPopup = () => {
    cy.getByTID([TIDs.layout_popup]).contains('li', 'Please enter email').should('exist');
    cy.getByTID([TIDs.layout_popup]).contains('li', 'Please enter password').should('exist');
    cy.getByTID([TIDs.layout_popup]).contains('li', 'Please enter password again').should('exist');
    cy.getByTID([TIDs.layout_popup])
        .contains('li', 'Telephone number cannot be shorter than 9 characters')
        .should('exist');
    cy.getByTID([TIDs.layout_popup]).contains('li', 'Please enter first name').should('exist');
    cy.getByTID([TIDs.layout_popup]).contains('li', 'Please enter last name').should('exist');
    cy.getByTID([TIDs.layout_popup]).contains('li', 'Please enter street').should('exist');
    cy.getByTID([TIDs.layout_popup]).contains('li', 'Please enter city').should('exist');
    cy.getByTID([TIDs.layout_popup]).contains('li', 'Please enter zip code').should('exist');
    cy.getByTID([TIDs.layout_popup]).contains('li', 'You have to agree with our privacy policy').should('exist');
};
