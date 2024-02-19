import { v4 as uuid } from 'uuid';

export const generateCustomerRegistrationData = () => ({
    firstName: 'John',
    lastName: 'Doe',
    email: generateEmail(),
    password: 'user123',
    telephone: '123456789',
    street: 'Uličnícká 123',
    city: 'Městečko',
    postcode: '12345',
    country: 'CZ',
    newsletterSubscription: true,
    productListsUuids: [],
});

const generateEmail = () => `no-reply-${uuid()}@shopsys.com.cz`;
