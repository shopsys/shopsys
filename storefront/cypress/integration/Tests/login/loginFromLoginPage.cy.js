import { customer1, url } from '../../../fixtures/demodata';
import { checkUserIsLoggedIn } from '../../Functions/header';
import { fillInEmailAndPasswordOnLoginPage } from '../../Functions/login';

it('Login from login page', () => {
    cy.visit(url.login);
    fillInEmailAndPasswordOnLoginPage(customer1.emailRegistered, customer1.password);
    cy.get('[data-testid="pages-login-submit"]').click();
    checkUserIsLoggedIn();
});
