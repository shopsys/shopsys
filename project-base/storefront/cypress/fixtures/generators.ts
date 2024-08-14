import { TypeCreateOrderMutationVariables } from '../../graphql/requests/orders/mutations/CreateOrderMutation.generated';
import { TypeRegistrationDataInput } from '../../graphql/types';
import { password } from './demodata';
import { v4 as uuid } from 'uuid';

export const generateCreateOrderInput = (email?: string): TypeCreateOrderMutationVariables => ({
    firstName: 'Alice',
    lastName: 'Inwonderland',
    email: email ?? generateEmail(),
    telephone: '9876541213',
    onCompanyBehalf: false,
    companyName: null,
    companyNumber: null,
    companyTaxNumber: null,
    street: 'Wondertown 123',
    city: 'Wondertown',
    postcode: '14234',
    country: 'CZ',
    isDeliveryAddressDifferentFromBilling: false,
    deliveryFirstName: null,
    deliveryLastName: null,
    deliveryCompanyName: null,
    deliveryTelephone: null,
    deliveryStreet: null,
    deliveryCity: null,
    deliveryPostcode: null,
    deliveryCountry: null,
    deliveryAddressUuid: null,
    note: null,
    cartUuid: null,
    newsletterSubscription: false,
    heurekaAgreement: true,
});

export const generateCustomerRegistrationData = (
    customerType: 'commonCustomer' | 'companyCustomer',
    staticEmail?: string,
) => {
    const generatedData: TypeRegistrationDataInput = {
        firstName: 'John',
        lastName: 'Doe',
        email: staticEmail ?? generateEmail(),
        password,
        telephone: '123456789',
        street: 'Uličnícká 123',
        city: 'Městečko',
        postcode: '12345',
        country: 'CZ',
        newsletterSubscription: true,
        productListsUuids: [],
        companyCustomer: false,
        cartUuid: null,
        companyName: null,
        companyNumber: null,
        companyTaxNumber: null,
        lastOrderUuid: null,
        billingAddressUuid: null,
    };

    if (customerType === 'companyCustomer') {
        generatedData.companyName = 'Firma firemní';
        generatedData.companyNumber = '12345678';
        generatedData.companyTaxNumber = 'CZ12345678';
    }

    return generatedData;
};

const generateEmail = () => `no-reply-${uuid()}@shopsys.com`;
