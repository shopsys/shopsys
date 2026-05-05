import { getGtmUserInfo } from 'gtm/utils/getGtmUserInfo';
import { CustomerTypeEnum } from 'types/customer';
import { describe, expect, test } from 'vitest';

const contactInformation = {
    city: 'Prague',
    country: { value: 'CZ', label: 'Czech Republic' },
    customer: CustomerTypeEnum.CommonCustomer,
    email: 'customer@example.com',
    firstName: 'John',
    lastName: 'Doe',
    postcode: '10000',
    street: 'Main street 1',
    telephone: '123456789',
    newsletterSubscription: false,
} as any;

const currentCustomer = {
    uuid: 'customer-uuid',
    city: 'Prague',
    country: { code: 'CZ' },
    email: 'customer@example.com',
    firstName: 'John',
    lastName: 'Doe',
    postcode: '10000',
    street: 'Main street 1',
    telephone: '123456789',
    newsletterSubscription: true,
    pricingGroup: 'Default',
    loginInfo: {
        loginType: 'web',
        externalId: null,
    },
    companyCustomer: false,
} as any;

describe('getGtmUserInfo', () => {
    test('should use newsletter subscription value from submitted contact information', () => {
        const result = getGtmUserInfo(currentCustomer, contactInformation);

        expect(result.newsletterSubscription).toBe(false);
    });
});
