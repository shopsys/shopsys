import { customer1 } from 'fixtures/demodata';
import { loginFromHeader } from 'support/login';

it('Login from header', () => {
    cy.visit('/');
    loginFromHeader(customer1.emailRegistered, customer1.password);
});
