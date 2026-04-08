import { url } from 'fixtures/demodata';
import { generateCustomerRegistrationData } from 'fixtures/generators';
import { checkFormLineError, checkUrl, translations } from 'support';
import { TIDs } from 'tids';

export const goToRegistrationPageFromHeader = () => {
    cy.getByTID([TIDs.my_account_link])
        .should('be.visible')
        .realHover()
        .then(() => {
            cy.getByTID([TIDs.login_popup_register_button]).click();
            checkUrl(url.registration);
            cy.waitForStableAndInteractiveDOM();
        });
};

export const submitRegistrationForm = () => {
    cy.getByTID([TIDs.registration_submit_button]).click();
};

export const submitLoginForm = () => {
    cy.getByTID([TIDs.login_form_submit_button]).click();
};

export const logoutFromCustomerMenu = () => {
    cy.getByTID([TIDs.user_menu_logout]).click();
};

export const loginFromHeader = (email: string | undefined, password: string) => {
    cy.getByTID([TIDs.my_account_link])
        .should('be.visible')
        .realHover()
        .then(() => {
            fillInEmailAndPasswordInLoginPopup(email, password);
            submitLoginForm();
        });
};

export const logoutFromHeader = () => {
    cy.getByTID([TIDs.my_account_link])
        .should('be.visible')
        .realHover()
        .then(() => cy.getByTID([TIDs.user_menu_logout]).should('be.visible').click());
};

export const fillInEmailAndPasswordInLoginPopup = (email: string | undefined, password: string) => {
    if (email) {
        cy.get('#login-form-email').type(email, { force: true });
    }
    cy.get('#login-form-password').type(password, { force: true });
};

export const fillInEmailAndPasswordOnLoginPage = (email: string, password: string) => {
    cy.get('#login-form-email').type(email, { force: true });
    cy.get('#login-form-password').type(password, { force: true });
};

export const clearAndFillInRegstrationFormEmail = (email: string, placeholderEmail: string) => {
    cy.get('#registration-form-email').should('have.attr', 'placeholder', placeholderEmail).clear().type(email);
};

export const fillInRegstrationForm = (custmerType: 'commonCustomer' | 'companyCustomer', email: string) => {
    const generatedData = generateCustomerRegistrationData(custmerType, email);
    const phoneWithPrefix = `${generatedData.telephone.prefix}${generatedData.telephone.number}`;

    cy.get('#registration-form-firstName')
        .should('have.attr', 'placeholder', translations.placeholder.firstName)
        .type(generatedData.firstName);

    cy.get('#registration-form-lastName')
        .should('have.attr', 'placeholder', translations.placeholder.lastName)
        .type(generatedData.lastName);

    cy.get('#registration-form-telephone')
        .should('have.attr', 'placeholder', translations.placeholder.phone)
        .type(phoneWithPrefix);

    if (
        custmerType === 'companyCustomer' &&
        generatedData.companyName &&
        generatedData.companyNumber &&
        generatedData.companyTaxNumber
    ) {
        cy.get('[for="registration-formcustomer1"]').click();

        cy.get('#registration-form-companyName')
            .should('have.attr', 'placeholder', translations.placeholder.companyName)
            .type(generatedData.companyName!);

        cy.get('#registration-form-companyNumber')
            .should('have.attr', 'placeholder', translations.placeholder.companyNumber)
            .type(generatedData.companyNumber!);

        cy.get('#registration-form-companyTaxNumber')
            .should('have.attr', 'placeholder', translations.placeholder.companyTaxNumber)
            .type(generatedData.companyTaxNumber!);
    } else {
        cy.get('[for="registration-formcustomer0"]').click();
    }

    cy.get('#registration-form-street')
        .should('have.attr', 'placeholder', translations.placeholder.street)
        .type(generatedData.street);

    cy.get('#registration-form-city')
        .should('have.attr', 'placeholder', translations.placeholder.city)
        .type(generatedData.city);

    cy.get('#registration-form-postcode')
        .should('have.attr', 'placeholder', translations.placeholder.postCode)
        .type(generatedData.postcode, { force: true });

    cy.get('[for="registration-form-gdprAgreement"]').find('span').first().click();
};

export const clearAndFillInRegistrationFormPasswords = (password: string) => {
    cy.get('#registration-form-password')
        .should('have.attr', 'placeholder', translations.placeholder.password)
        .clear()
        .type(password);

    cy.get('#registration-form-passwordConfirm')
        .should('have.attr', 'placeholder', translations.placeholder.passwordAgain)
        .clear({ force: true })
        .type(password);
};

export const checkRegistrationValidationErrors = () => {
    checkFormLineError('Please enter email');
    checkFormLineError('Please enter password');
    checkFormLineError('Please enter password again');
    checkFormLineError('Please enter phone number');
    checkFormLineError('Please enter first name');
    checkFormLineError('Please enter last name');
    checkFormLineError('Please enter street');
    checkFormLineError('Please enter city');
    checkFormLineError('Please enter zip code');
    checkFormLineError('You have to agree with our privacy policy');
};
