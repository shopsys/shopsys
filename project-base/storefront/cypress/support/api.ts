import { TypeCreateOrderMutationVariables } from '../../graphql/requests/orders/mutations/CreateOrderMutation.generated';
import { TypeRegistrationDataInput } from '../../graphql/types';
import 'cypress-real-events';
import { PERSIST_STORE_NAME, products } from 'fixtures/demodata';

Cypress.Commands.add('addProductToCartForTest', (productUuid?: string, quantity?: number) => {
    const currentAppStoreAsString = window.localStorage.getItem(PERSIST_STORE_NAME);

    return cy.getCookie('accessToken').then((cookie) => {
        const accessToken = cookie?.value;
        let cartUuid: string | null = null;

        if (!accessToken && currentAppStoreAsString) {
            cartUuid = JSON.parse(currentAppStoreAsString).state.cartUuid;
        }

        return cy
            .request({
                method: 'POST',
                url: 'graphql/',
                body: JSON.stringify({
                    operationName: 'AddToCartMutation',
                    query: `mutation AddToCartMutation($input: AddToCartInput!) {
                    AddToCart(input: $input) {
                        cart {
                            uuid
                        }
                    }
                }`,
                    variables: {
                        input: {
                            cartUuid,
                            productUuid: productUuid ?? products.helloKitty.uuid,
                            quantity: quantity ?? 1,
                        },
                    },
                }),
                headers: {
                    'Content-Type': 'application/json',
                    ...(accessToken ? { 'X-Auth-Token': 'Bearer ' + accessToken } : {}),
                },
            })
            .its('body.data.AddToCart.cart');
    });
});

Cypress.Commands.add('addPromoCodeToCartForTest', (promoCode: string) => {
    const currentAppStoreAsString = window.localStorage.getItem(PERSIST_STORE_NAME);

    return cy.getCookie('accessToken').then((cookie) => {
        const accessToken = cookie?.value;
        let cartUuid: string | null = null;

        if (!accessToken && currentAppStoreAsString) {
            cartUuid = JSON.parse(currentAppStoreAsString).state.cartUuid;
        }

        return cy
            .request({
                method: 'POST',
                url: 'graphql/',
                body: JSON.stringify({
                    operationName: 'ApplyPromoCodeToCartMutation',
                    query: `mutation ApplyPromoCodeToCartMutation($input: ApplyPromoCodeToCartInput!) { 
                    ApplyPromoCodeToCart(input: $input) { 
                        uuid 
                        promoCode
                    } 
                }`,
                    variables: {
                        input: {
                            cartUuid,
                            promoCode,
                        },
                    },
                }),
                headers: {
                    'Content-Type': 'application/json',
                    ...(accessToken ? { 'X-Auth-Token': 'Bearer ' + accessToken } : {}),
                },
            })
            .its('body.data.ApplyPromoCodeToCart')
            .then((cart) => {
                expect(cart.uuid).equal(cartUuid);
                expect(cart.promoCode).equal(promoCode);
            });
    });
});

Cypress.Commands.add('preselectTransportForTest', (transportUuid: string, pickupPlaceIdentifier?: string) => {
    const currentAppStoreAsString = window.localStorage.getItem(PERSIST_STORE_NAME);
    if (!currentAppStoreAsString) {
        throw new Error(
            'Could not load app store from local storage. This is an issue with tests, not with the application.',
        );
    }

    return cy.getCookie('accessToken').then((cookie) => {
        const accessToken = cookie?.value;
        let cartUuid: string | null = null;

        if (!accessToken && currentAppStoreAsString) {
            cartUuid = JSON.parse(currentAppStoreAsString).state.cartUuid;
        }

        return cy
            .request({
                method: 'POST',
                url: 'graphql/',
                body: JSON.stringify({
                    operationName: 'ChangeTransportInCartMutation',
                    query: `mutation ChangeTransportInCartMutation($input: ChangeTransportInCartInput!) {
                    ChangeTransportInCart(input: $input) {
                        uuid,
                        transport {
                            uuid
                        },
                        selectedPickupPlaceIdentifier
                    }
                }`,
                    variables: {
                        input: {
                            cartUuid,
                            transportUuid,
                            pickupPlaceIdentifier,
                        },
                    },
                }),
                headers: {
                    'Content-Type': 'application/json',
                    ...(accessToken ? { 'X-Auth-Token': 'Bearer ' + accessToken } : {}),
                },
            })
            .its('body.data.ChangeTransportInCart')
            .then((cart) => {
                expect(cart.uuid).equal(cartUuid);
                expect(cart.transport.uuid).equal(transportUuid);
                if (pickupPlaceIdentifier) {
                    expect(cart.selectedPickupPlaceIdentifier).equal(pickupPlaceIdentifier);
                }
            });
    });
});

Cypress.Commands.add('preselectPaymentForTest', (paymentUuid: string) => {
    const currentAppStoreAsString = window.localStorage.getItem(PERSIST_STORE_NAME);
    if (!currentAppStoreAsString) {
        throw new Error(
            'Could not load app store from local storage. This is an issue with tests, not with the application.',
        );
    }

    return cy.getCookie('accessToken').then((cookie) => {
        const accessToken = cookie?.value;
        let cartUuid: string | null = null;

        if (!accessToken && currentAppStoreAsString) {
            cartUuid = JSON.parse(currentAppStoreAsString).state.cartUuid;
        }

        return cy
            .request({
                method: 'POST',
                url: 'graphql/',
                body: JSON.stringify({
                    operationName: 'ChangePaymentInCartMutation',
                    query: `mutation ChangePaymentInCartMutation($input: ChangePaymentInCartInput!) {
                    ChangePaymentInCart(input: $input) {
                        uuid,
                        payment {
                            uuid
                        }
                    }
                }`,
                    variables: {
                        input: {
                            cartUuid,
                            paymentUuid,
                        },
                    },
                }),
                headers: {
                    'Content-Type': 'application/json',
                    ...(accessToken ? { 'X-Auth-Token': 'Bearer ' + accessToken } : {}),
                },
            })
            .its('body.data.ChangePaymentInCart')
            .then((cart) => {
                expect(cart.uuid).equal(cartUuid);
                expect(cart.payment.uuid).equal(paymentUuid);
            });
    });
});

Cypress.Commands.add('registerAsNewUser', (registrationInput: TypeRegistrationDataInput, shouldLogin = true) => {
    return cy
        .request({
            method: 'POST',
            url: 'graphql/',
            body: JSON.stringify({
                operationName: 'RegistrationMutation',
                query: `mutation RegistrationMutation($input: RegistrationDataInput!) {
                    Register(input: $input) {
                      tokens {
                        accessToken
                        refreshToken
                      }
                    }
                  }`,
                variables: {
                    input: registrationInput,
                },
            }),
            headers: {
                'Content-Type': 'application/json',
            },
        })
        .its('body.data.Register')
        .then((registrationResponse) => {
            if (shouldLogin) {
                expect(registrationResponse.tokens.accessToken).to.be.a('string').and.not.be.empty;
                expect(registrationResponse.tokens.refreshToken).to.be.a('string').and.not.be.empty;
                cy.setCookie('accessToken', registrationResponse.tokens.accessToken, { path: '/' });
                cy.setCookie('refreshToken', registrationResponse.tokens.refreshToken, {
                    expiry: Math.floor(Date.now() / 1000) + 3600 * 24 * 14,
                    path: '/',
                });
            }
        });
});

Cypress.Commands.add('logout', () => {
    const currentAppStoreAsString = window.localStorage.getItem(PERSIST_STORE_NAME);
    if (!currentAppStoreAsString) {
        throw new Error(
            'Could not load app store from local storage. This is an issue with tests, not with the application.',
        );
    }

    return cy.getCookie('accessToken').then((cookie) => {
        const accessToken = cookie?.value;

        return cy
            .request({
                method: 'POST',
                url: 'graphql/',
                body: JSON.stringify({
                    operationName: 'LogoutMutation',
                    query: `mutation LogoutMutation {
                    Logout
                }`,
                    variables: {},
                }),
                headers: {
                    'Content-Type': 'application/json',
                    ...(accessToken ? { 'X-Auth-Token': 'Bearer ' + accessToken } : {}),
                },
            })
            .then((logoutResponse) => {
                expect(logoutResponse.body.data.Logout).to.be.true;
                cy.clearCookie('accessToken');
                cy.clearCookie('refreshToken');
            });
    });
});

Cypress.Commands.add('createOrder', (createOrderVariables: TypeCreateOrderMutationVariables) => {
    const currentAppStoreAsString = window.localStorage.getItem(PERSIST_STORE_NAME);
    if (!currentAppStoreAsString) {
        throw new Error(
            'Could not load app store from local storage. This is an issue with tests, not with the application.',
        );
    }

    return cy.getCookie('accessToken').then((cookie) => {
        const accessToken = cookie?.value;
        let cartUuid: string | null = null;

        if (!accessToken && currentAppStoreAsString) {
            cartUuid = JSON.parse(currentAppStoreAsString).state.cartUuid;
        }

        return cy
            .request({
                method: 'POST',
                url: 'graphql/',
                body: JSON.stringify({
                    operationName: 'CreateOrderMutation',
                    query: `mutation CreateOrderMutation(
                    $firstName: String!
                    $lastName: String!
                    $email: String!
                    $telephone: String!
                    $onCompanyBehalf: Boolean!
                    $companyName: String
                    $companyNumber: String
                    $companyTaxNumber: String
                    $street: String!
                    $city: String!
                    $postcode: String!
                    $country: String!
                    $isDeliveryAddressDifferentFromBilling: Boolean!
                    $deliveryFirstName: String
                    $deliveryLastName: String
                    $deliveryCompanyName: String
                    $deliveryTelephone: String
                    $deliveryStreet: String
                    $deliveryCity: String
                    $deliveryPostcode: String
                    $deliveryCountry: String
                    $deliveryAddressUuid: Uuid
                    $note: String
                    $cartUuid: Uuid
                    $newsletterSubscription: Boolean
                    $heurekaAgreement: Boolean!
                ) {
                    CreateOrder(
                        input: {
                            firstName: $firstName
                            lastName: $lastName
                            email: $email
                            telephone: $telephone
                            onCompanyBehalf: $onCompanyBehalf
                            companyName: $companyName
                            companyNumber: $companyNumber
                            companyTaxNumber: $companyTaxNumber
                            street: $street
                            city: $city
                            postcode: $postcode
                            country: $country
                            isDeliveryAddressDifferentFromBilling: $isDeliveryAddressDifferentFromBilling
                            deliveryFirstName: $deliveryFirstName
                            deliveryLastName: $deliveryLastName
                            deliveryCompanyName: $deliveryCompanyName
                            deliveryTelephone: $deliveryTelephone
                            deliveryStreet: $deliveryStreet
                            deliveryCity: $deliveryCity
                            deliveryPostcode: $deliveryPostcode
                            deliveryCountry: $deliveryCountry
                            deliveryAddressUuid: $deliveryAddressUuid
                            note: $note
                            cartUuid: $cartUuid
                            newsletterSubscription: $newsletterSubscription
                            heurekaAgreement: $heurekaAgreement
                        }
                    ) {
                        order {
                            urlHash
                    }
                }
            }`,
                    variables: { ...createOrderVariables, cartUuid },
                }),
                headers: {
                    'Content-Type': 'application/json',
                    ...(accessToken ? { 'X-Auth-Token': 'Bearer ' + accessToken } : {}),
                },
            })
            .its('body.data.CreateOrder.order');
    });
});
