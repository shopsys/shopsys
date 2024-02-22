import { TIDs } from 'tids';

declare global {
    namespace Cypress {
        interface Chainable<Subject = any> {
            getByTID(value: ([TIDs, number] | TIDs)[]): Chainable<JQuery<HTMLElement>>;
            storeCartUuidInLocalStorage(cartUuid: string): Cypress.Chainable<undefined>;
            addProductToCartForTest(productUuid?: string, quantity?: number): Cypress.Chainable<any>;
            preselectTransportForTest(
                transportUuid: string,
                pickupPlaceIdentifier?: string,
            ): Cypress.Chainable<Cypress.Response<any>>;
            preselectPaymentForTest(paymentUuid: string): Cypress.Chainable<Cypress.Response<any>>;

            setDevicePixelRatio(
                pixelRatio: number,
                options?: {
                    mobile: boolean;
                    width: number;
                    height: number;
                },
            ): void;
        }
    }
}

export {};
