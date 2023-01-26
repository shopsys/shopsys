import { customer1 } from '../../../fixtures/demodata';
import { succesfulLogInFromHeader } from '../../Functions/login';

it('Successfull login from header', () => {
    cy.visit('/');
    succesfulLogInFromHeader(customer1.email, customer1.password);
});
