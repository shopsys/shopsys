import { b2bDomain } from 'fixtures/demodata';
import {
    check403PageIsVisible,
    loginAsB2bAccountant,
    loginAsB2bCatalogUser,
    loginAsB2bLimitedUser,
    loginAsB2bOwner,
    loginAsB2bUser,
    type Blackout,
} from 'support';
import { TIDs } from 'tids';

export type B2bRole = 'owner' | 'user' | 'limitedUser' | 'catalogUser' | 'accountant';

export const skipIfB2bNotConfigured = () => {
    before(function () {
        if (b2bDomain === null) {
            this.skip();
        }
    });
};

export const B2B_FOOTER_BLACKOUTS: Blackout[] = [
    { tid: TIDs.footer_social_links },
    { tid: TIDs.footer_payment_images },
    { tid: TIDs.footer_copyright },
];

export const loginAsB2bRole = (role: B2bRole) => {
    const loginByRole: Record<B2bRole, () => void> = {
        owner: loginAsB2bOwner,
        user: loginAsB2bUser,
        limitedUser: loginAsB2bLimitedUser,
        catalogUser: loginAsB2bCatalogUser,
        accountant: loginAsB2bAccountant,
    };

    loginByRole[role]();
};

export const loginAndVisitB2bPage = (role: B2bRole, path: string, options?: Partial<Cypress.VisitOptions>) => {
    loginAsB2bRole(role);
    cy.visitB2bAndWaitForStableAndInteractiveDOM(path, options);
};

export const expectB2bPageForbiddenForRole = (role: B2bRole, path: string) => {
    loginAndVisitB2bPage(role, path, { failOnStatusCode: false });
    check403PageIsVisible();
};
