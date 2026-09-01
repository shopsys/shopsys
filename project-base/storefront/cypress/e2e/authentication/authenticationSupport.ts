import { url } from 'fixtures/demodata';
import { generateCustomerRegistrationData } from 'fixtures/generators';
import {
    checkFormLineError,
    checkUrl,
    openHeaderUserMenu,
    translations,
} from 'support';
import { TIDs } from 'tids';

const preventScrollOptions = { scrollBehavior: false } as const;

const fillInEmailAndPasswordInScopedLoginForm = (
    email: string | undefined,
    password: string,
    typeOptions: Partial<Cypress.TypeOptions> = {},
) => {
    if (email) {
        cy.get('input[name="email"]:enabled').should('be.visible').type(email, typeOptions);
    }
    cy.get('input[name="password"]:enabled').should('be.visible').type(password, typeOptions);
};

const submitScopedLoginForm = (clickOptions: Partial<Cypress.ClickOptions> = {}) => {
    cy.getByTID([TIDs.login_form_submit_button]).should('be.visible').and('be.enabled').click(clickOptions);
};

const getVisibleLoginFormWithEnabledEmail = (parentTIDs: TIDs[]) =>
    cy
        .getByTID([...parentTIDs, TIDs.login_form])
        .filter(':visible')
        .filter(':has(input[name="email"]:enabled)')
        .should('have.length.at.least', 1)
        .first();

const goToRegistrationPageFromHeaderTID = (headerTID: TIDs.header | TIDs.fixed_header) => {
    cy.getByTID([headerTID, TIDs.my_account_link])
        .should('be.visible')
        .click({ scrollBehavior: false })
        .should('have.attr', 'aria-expanded', 'true');
    cy.getByTID([headerTID, TIDs.my_account_link, TIDs.login_popup_register_button])
        .filter(':visible')
        .first()
        .should('be.visible')
        .click({ scrollBehavior: false });
    checkUrl(url.registration);
    cy.waitForStableAndInteractiveDOM();
    cy.getByTID([TIDs.overlay]).should('not.exist');
};

export const goToRegistrationPageFromHeader = () => {
    cy.scrollTo('top', { ensureScrollable: false });
    cy.getByTID([TIDs.fixed_header]).should('not.exist');
    goToRegistrationPageFromHeaderTID(TIDs.header);
};

export const goToRegistrationPageFromFixedHeader = () => {
    goToRegistrationPageFromHeaderTID(TIDs.fixed_header);
};

export const submitRegistrationForm = () => {
    cy.getByTID([TIDs.registration_submit_button]).click();
};

export const waitForRegistrationRedirect = () => {
    cy.location('pathname').should('eq', '/');
    cy.waitForHydration();
};

export const submitLoginForm = () => {
    cy.getByTID([TIDs.login_form])
        .filter(':visible')
        .should('have.length', 1)
        .within(() => submitScopedLoginForm());
};

export const submitLoginPopupForm = () => {
    getVisibleLoginFormWithEnabledEmail([TIDs.layout_popup]).within(() => submitScopedLoginForm());
};

export const logoutFromCustomerMenu = () => {
    cy.getByTID([TIDs.user_menu_logout]).click();
};

export const loginFromHeader = (email: string | undefined, password: string) => {
    openHeaderUserMenu();
    getVisibleLoginFormWithEnabledEmail([TIDs.header, TIDs.my_account_link]).within(() => {
        fillInEmailAndPasswordInScopedLoginForm(email, password, preventScrollOptions);
        submitScopedLoginForm(preventScrollOptions);
    });
};

export const logoutFromHeader = () => {
    openHeaderUserMenu();
    cy.getByTID([TIDs.header, TIDs.my_account_link, TIDs.user_menu_logout])
        .filter(':visible')
        .first()
        .should('be.visible')
        .click({ scrollBehavior: false });
};

export const fillInEmailAndPasswordInLoginPopup = (email: string | undefined, password: string) => {
    getVisibleLoginFormWithEnabledEmail([TIDs.layout_popup]).within(() =>
        fillInEmailAndPasswordInScopedLoginForm(email, password),
    );
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
        .type(generatedData.firstName, { scrollBehavior: 'center' });

    cy.get('#registration-form-lastName')
        .should('have.attr', 'placeholder', translations.placeholder.lastName)
        .type(generatedData.lastName, { scrollBehavior: 'center' });

    cy.get('#registration-form-telephone')
        .should('have.attr', 'placeholder', translations.placeholder.phone)
        .type(phoneWithPrefix, { scrollBehavior: 'center' });

    if (
        custmerType === 'companyCustomer' &&
        generatedData.companyName &&
        generatedData.companyNumber &&
        generatedData.companyTaxNumber
    ) {
        cy.get('[for="registration-formcustomer1"]').click({ scrollBehavior: 'center' });
        cy.waitForStableAndInteractiveDOM();

        cy.get('#registration-form-companyName')
            .should('have.attr', 'placeholder', translations.placeholder.companyName)
            .type(generatedData.companyName!, { scrollBehavior: 'center' });

        cy.get('#registration-form-companyNumber')
            .should('have.attr', 'placeholder', translations.placeholder.companyNumber)
            .type(generatedData.companyNumber!, { scrollBehavior: 'center' });

        cy.get('#registration-form-companyTaxNumber')
            .should('have.attr', 'placeholder', translations.placeholder.companyTaxNumber)
            .type(generatedData.companyTaxNumber!, { scrollBehavior: 'center' });
    } else {
        cy.get('[for="registration-formcustomer0"]').click({ scrollBehavior: 'center' });
    }

    cy.get('#registration-form-street')
        .should('have.attr', 'placeholder', translations.placeholder.street)
        .type(generatedData.street, { scrollBehavior: 'center' });

    cy.get('#registration-form-city')
        .should('have.attr', 'placeholder', translations.placeholder.city)
        .type(generatedData.city, { scrollBehavior: 'center' });

    cy.get('#registration-form-postcode')
        .should('have.attr', 'placeholder', translations.placeholder.postCode)
        .type(generatedData.postcode, { scrollBehavior: 'center' });

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
