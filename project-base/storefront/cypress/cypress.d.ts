import { CreateOrderMutationVariables } from '../graphql/requests/orders/mutations/CreateOrderMutation.generated';
import { RegistrationDataInput } from '../graphql/types';
import { TIDs } from 'tids';

declare global {
    namespace Cypress {
        interface Chainable<Subject = any> {
            getByTID(value: ([TIDs, number] | TIDs)[]): Chainable<JQuery<HTMLElement>>;
            storeCartUuidInLocalStorage(cartUuid: string): Cypress.Chainable<undefined>;
            visitAndWaitForStableDOM(url: string): Cypress.Chainable<JQuery<HTMLElement>>;
            reloadAndWaitForStableDOM(): Cypress.Chainable<JQuery<HTMLElement>>;
            addProductToCartForTest(productUuid?: string, quantity?: number): Cypress.Chainable<any>;
            preselectTransportForTest(
                transportUuid: string,
                pickupPlaceIdentifier?: string,
            ): Cypress.Chainable<Cypress.Response<any>>;
            preselectPaymentForTest(paymentUuid: string): Cypress.Chainable<Cypress.Response<any>>;
            registerAsNewUser(registrationInput: RegistrationDataInput): Cypress.Chainable<string>;
            createOrder(createOrderInput: CreateOrderMutationVariables): Cypress.Chainable<Cypress.Response<any>>;

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
