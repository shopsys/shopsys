import { customer1, url } from 'fixtures/demodata';
import { checkUserIsLoggedIn } from 'support/header';
import { fillInEmailAndPasswordOnLoginPage } from 'support/login';

it('Login from login page', () => {
    cy.visit(url.login);
    fillInEmailAndPasswordOnLoginPage(customer1.emailRegistered, customer1.password);
    cy.getByDataTestId('pages-login-submit').click();
    checkUserIsLoggedIn();
});
