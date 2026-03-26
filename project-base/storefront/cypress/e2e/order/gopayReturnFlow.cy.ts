import { url } from 'fixtures/demodata';

const { routes } = require('/config/routes');
const route = routes[0];

const GO_PAY_PAYMENT_SESSION_LOCAL_STORAGE_KEY = 'goPayPaymentSession';
const ORDER_PAYMENT_CONFIRMATION_PATH = route['/order-payment-confirmation'] as string;

const testOrderUuid = '11111111-1111-4111-8111-111111111111';
const testOrderEmail = 'gopay-flow@shopsys.com';
const testOrderUrlHash = 'test-order-url-hash';
const testValidityHash = 'test-validity-hash';

type NextDataWindow = Cypress.AUTWindow & {
    __NEXT_DATA__?: {
        locale?: string;
        props?: { pageProps?: { domainConfig?: { url?: string } } };
        runtimeConfig?: { domains?: Array<{ defaultLocale: string; url: string }> };
    };
};

const getDomainUrl = (win: Cypress.AUTWindow) => {
    const nextData = (win as NextDataWindow).__NEXT_DATA__;
    const domainUrlFromPageProps = nextData?.props?.pageProps?.domainConfig?.url;
    if (domainUrlFromPageProps) {
        return domainUrlFromPageProps;
    }

    const runtimeDomains = nextData?.runtimeConfig?.domains;
    const locale = nextData?.locale;

    if (runtimeDomains?.length && locale) {
        const localeDomain = runtimeDomains.find((domain) => domain.defaultLocale === locale);

        if (localeDomain) {
            return localeDomain.url;
        }
    }

    return win.location.origin;
};

const buildGoPaySession = (domainUrl: string) => ({
    orderUuid: testOrderUuid,
    orderUrlHash: testOrderUrlHash,
    orderPaymentStatusPageValidityHash: testValidityHash,
    domainUrl,
    timestamp: Date.now(),
});

const seedGoPayPaymentSession = () => {
    cy.visit('/');
    cy.window().then((win) => {
        const domainUrl = getDomainUrl(win);
        win.localStorage.setItem(
            GO_PAY_PAYMENT_SESSION_LOCAL_STORAGE_KEY,
            JSON.stringify(buildGoPaySession(domainUrl)),
        );
    });
};

describe('GoPay Return Flow Guards', () => {
    it('redirects from order confirmation to order payment confirmation using stored GoPay session', () => {
        const orderConfirmationUrl = `${url.order.orderConfirmation}?orderUuid=${testOrderUuid}&orderEmail=${encodeURIComponent(testOrderEmail)}&orderPaymentType=goPay`;

        seedGoPayPaymentSession();
        cy.visit(orderConfirmationUrl);

        cy.url().should('include', ORDER_PAYMENT_CONFIRMATION_PATH);
        cy.url().should('include', `orderIdentifier=${testOrderUuid}`);
        cy.url().should('include', `orderPaymentStatusPageValidityHash=${testValidityHash}`);
        cy.url().should('not.include', 'orderEmail=');
    });

    it('recovers missing payment validity hash from GoPay session on order payment confirmation page', () => {
        const orderPaymentConfirmationUrl = `${ORDER_PAYMENT_CONFIRMATION_PATH}?orderIdentifier=${testOrderUuid}&orderEmail=${encodeURIComponent(testOrderEmail)}&orderUrlHash=${testOrderUrlHash}`;

        seedGoPayPaymentSession();
        cy.visit(orderPaymentConfirmationUrl);

        cy.url().should('include', ORDER_PAYMENT_CONFIRMATION_PATH);
        cy.url().should('include', `orderIdentifier=${testOrderUuid}`);
        cy.url().should('include', `orderPaymentStatusPageValidityHash=${testValidityHash}`);
        cy.url().should('not.include', 'orderEmail=');
    });
});
