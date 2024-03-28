import { CreateOrderMutationVariables } from '../../graphql/requests/orders/mutations/CreateOrderMutation.generated';
import { RegistrationDataInput } from '../../graphql/types';
import { v4 as uuid } from 'uuid';

export const generateCustomerRegistrationData = (email?: string): RegistrationDataInput => ({
    firstName: 'John',
    lastName: 'Doe',
    email: email ?? generateEmail(),
    password: 'user123',
    telephone: '123456789',
    street: 'Uličnícká 123',
    city: 'Městečko',
    postcode: '12345',
    country: 'CZ',
    newsletterSubscription: true,
    productListsUuids: [],
    cartUuid: null,
    companyCustomer: false,
    companyName: null,
    companyNumber: null,
    companyTaxNumber: null,
    lastOrderUuid: null,
});

const generateEmail = () => `no-reply-${uuid()}@shopsys.com.cz`;

export const generateCreateOrderInput = (email?: string): CreateOrderMutationVariables => ({
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
    differentDeliveryAddress: false,
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
});
