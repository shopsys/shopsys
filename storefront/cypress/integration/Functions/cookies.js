import { flashMessages } from '../../fixtures/demodata';
import { checkSuccessfulFlashMessage } from './flashMessage';

export function saveCookiesOptionsInCookiesBar() {
    cy.get('[data-testid="blocks-userconsent"] [data-testid="blocks-userconsent-save"]').click();
    checkSuccessfulFlashMessage(flashMessages.successfulSaveCookiesOptions);
    cy.get('[data-testid="blocks-userconsent"]').should('not.exist');
}
