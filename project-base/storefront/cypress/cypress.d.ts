import { CreateOrderMutationVariables } from '../graphql/requests/orders/mutations/CreateOrderMutation.generated';
import { RegistrationDataInput } from '../graphql/types';
import { TIDs } from 'tids';

declare global {
    namespace Cypress {
        interface Chainable<Subject = any> {
            getByTID(
                value: ([TIDs, number | string] | TIDs)[],
                options?:
                    | Partial<Cypress.Loggable & Cypress.Timeoutable & Cypress.Withinable & Cypress.Shadow>
                    | undefined,
            ): Chainable<JQuery<HTMLElement>>;
            storeCartUuidInLocalStorage(cartUuid: string): Cypress.Chainable<undefined>;
            waitForStableAndInteractiveDOM(): Cypress.Chainable<JQuery<HTMLElement>>;
            visitAndWaitForStableAndInteractiveDOM(url: string): Cypress.Chainable<JQuery<HTMLElement>>;
            reloadAndWaitForStableAndInteractiveDOM(): Cypress.Chainable<JQuery<HTMLElement>>;
            addProductToCartForTest(productUuid?: string, quantity?: number): Cypress.Chainable<any>;
            addPromoCodeToCartForTest(promoCode: string): Cypress.Chainable<any>;
            preselectTransportForTest(
                transportUuid: string,
                pickupPlaceIdentifier?: string,
            ): Cypress.Chainable<Cypress.Response<any>>;
            preselectPaymentForTest(paymentUuid: string): Cypress.Chainable<Cypress.Response<any>>;
            logout(): Cypress.Chainable<Cypress.Response<any>>;
            createOrder(createOrderInput: CreateOrderMutationVariables): Cypress.Chainable<{ urlHash: string }>;
            registerAsNewUser(
                registrationInput: RegistrationDataInputApi,
                shouldLogin?: boolean,
            ): Cypress.Chainable<Cypress.Response<any>>;

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
