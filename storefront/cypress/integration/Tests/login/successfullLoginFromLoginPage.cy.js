/// <reference types="Cypress" />
import { customer1, url } from '../../../fixtures/demodata';
import { checkSuccesfulLoginIconInHeader } from '../../Functions/header';
import { fillInEmailAndPasswordOnLoginPage } from '../../Functions/login';

it('Successfull login from login page', () => {
    cy.visit(url.login);
    fillInEmailAndPasswordOnLoginPage(customer1.email, customer1.password);
    cy.get('[data-testid="pages-login-submit"]').click();
    checkSuccesfulLoginIconInHeader();
});
