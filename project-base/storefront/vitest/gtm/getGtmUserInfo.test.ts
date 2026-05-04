import { TypeLoginTypeEnum } from 'graphql/types';
import { getGtmUserInfo } from 'gtm/utils/getGtmUserInfo';
import { ContactInformation, defaultContactInformationState } from 'store/slices/createContactInformationSlice';
import { CurrentCustomerType } from 'types/customer';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { describe, expect, test } from 'vitest';

const contactInformation = {
    ...defaultContactInformationState.contactInformation,
    email: 'billing@example.com',
    firstName: 'Billing',
    lastName: 'Customer',
    telephonePrefix: '+420',
    telephonePrefixCountryCode: 'CZ',
    telephone: '111222333',
    street: 'Billing Street 123/4b',
    city: 'Billing City',
    postcode: '11000',
    country: { value: 'CZ', label: 'Czechia' },
    companyName: 'Billing Company',
    companyNumber: '12345678',
    companyTaxNumber: 'CZ12345678',
    newsletterSubscription: true,
} satisfies ContactInformation;

const currentCustomer = {
    uuid: '5c9a5430-b9d5-4f85-9e53-bde60dc019b4',
    companyCustomer: true,
    firstName: 'Account',
    lastName: 'Customer',
    email: 'account@example.com',
    telephonePrefix: '+420',
    telephonePrefixCountryCode: 'CZ',
    telephoneNumber: '777888999',
    telephone: '+420 777888999',
    billingAddressUuid: 'billing-address-uuid',
    street: 'Account Street 10',
    city: 'Account City',
    postcode: '13000',
    country: { __typename: 'Country', name: 'Czechia', code: 'CZ' },
    newsletterSubscription: false,
    companyName: 'Account Company',
    companyNumber: '87654321',
    companyTaxNumber: 'CZ87654321',
    oldPassword: '',
    newPassword: '',
    newPasswordConfirm: '',
    defaultDeliveryAddress: {
        uuid: 'default-delivery-address-uuid',
        companyName: 'Default Delivery Company',
        street: 'Default Delivery Street 20',
        city: 'Default Delivery City',
        postcode: '14000',
        telephonePrefix: '+420',
        telephonePrefixCountryCode: 'CZ',
        telephoneNumber: '222333444',
        telephone: '+420 222333444',
        firstName: 'Default',
        lastName: 'Delivery',
        country: { __typename: 'Country', name: 'Czechia', code: 'CZ' },
    },
    deliveryAddresses: [
        {
            uuid: 'selected-delivery-address-uuid',
            companyName: 'Selected Delivery Company',
            street: 'Selected Delivery Street 30',
            city: 'Selected Delivery City',
            postcode: '15000',
            telephonePrefix: '+420',
            telephonePrefixCountryCode: 'CZ',
            telephoneNumber: '333444555',
            telephone: '+420 333444555',
            firstName: 'Selected',
            lastName: 'Delivery',
            country: { __typename: 'Country', name: 'Czechia', code: 'CZ' },
        },
    ],
    pricingGroup: 'B2B',
    hasPasswordSet: true,
    loginInfo: {
        __typename: 'LoginInfo',
        loginType: TypeLoginTypeEnum.Web,
        externalId: 'external-id',
    },
    roles: [],
    roleGroup: {
        __typename: 'CustomerUserRoleGroup',
        uuid: 'role-group-uuid',
        name: 'Role group',
    },
} satisfies CurrentCustomerType;

const pickupPlace = {
    street: 'Pickup Street 40',
    city: 'Pickup City',
    postcode: '16000',
    country: { __typename: 'Country', name: 'Czechia', code: 'CZ' },
} as StoreOrPacketeryPoint;

describe('getGtmUserInfo', () => {
    test('should map visitor billing data and new user fields', () => {
        const userInfo = getGtmUserInfo(undefined, contactInformation, null, '89.248.244.148');

        expect(userInfo).toMatchObject({
            email: 'billing@example.com',
            firstName: 'Billing',
            lastName: 'Customer',
            telephone: '+420111222333',
            street: 'Billing Street',
            streetNumber: '123/4b',
            city: 'Billing City',
            postcode: '11000',
            country: 'CZ',
            companyName: 'Billing Company',
            companyNumber: '12345678',
            companyVatNumber: 'CZ12345678',
            ipAddress: '89.248.244.148',
            newsletterSubscription: true,
            pickupSomeoneElse: false,
        });
    });

    test('should prefer pickup place address and selected delivery contact details', () => {
        const userInfo = getGtmUserInfo(
            currentCustomer,
            {
                ...contactInformation,
                deliveryAddressUuid: 'selected-delivery-address-uuid',
                isDeliveryAddressDifferentFromBilling: true,
            },
            pickupPlace,
        );

        expect(userInfo).toMatchObject({
            firstName: 'Selected',
            lastName: 'Delivery',
            telephone: '+420333444555',
            street: 'Pickup Street',
            streetNumber: '40',
            city: 'Pickup City',
            postcode: '16000',
            country: 'CZ',
            companyName: 'Selected Delivery Company',
            companyNumber: '12345678',
            companyVatNumber: 'CZ12345678',
            pickupSomeoneElse: true,
        });
    });

    test('should prefer selected delivery address when another delivery address is enabled', () => {
        const userInfo = getGtmUserInfo(currentCustomer, {
            ...contactInformation,
            deliveryAddressUuid: 'selected-delivery-address-uuid',
            isDeliveryAddressDifferentFromBilling: true,
        });

        expect(userInfo).toMatchObject({
            firstName: 'Selected',
            lastName: 'Delivery',
            telephone: '+420333444555',
            street: 'Selected Delivery Street',
            streetNumber: '30',
            city: 'Selected Delivery City',
            postcode: '15000',
            country: 'CZ',
            companyName: 'Selected Delivery Company',
        });
    });

    test('should ignore stale selected delivery address when another delivery address is disabled', () => {
        const userInfo = getGtmUserInfo(currentCustomer, {
            ...contactInformation,
            deliveryAddressUuid: 'selected-delivery-address-uuid',
            isDeliveryAddressDifferentFromBilling: false,
        });

        expect(userInfo).toMatchObject({
            firstName: 'Billing',
            lastName: 'Customer',
            telephone: '+420111222333',
            street: 'Billing Street',
            streetNumber: '123/4b',
            city: 'Billing City',
            postcode: '11000',
            country: 'CZ',
            companyName: 'Billing Company',
        });
    });

    test('should use account billing address before default delivery address in account mode', () => {
        const userInfo = getGtmUserInfo(
            currentCustomer,
            {
                ...defaultContactInformationState.contactInformation,
                newsletterSubscription: false,
            },
            null,
            undefined,
            'account',
        );

        expect(userInfo).toMatchObject({
            firstName: 'Account',
            lastName: 'Customer',
            telephone: '+420777888999',
            street: 'Account Street',
            streetNumber: '10',
            city: 'Account City',
            postcode: '13000',
            country: 'CZ',
            email: 'account@example.com',
            companyName: 'Account Company',
            companyNumber: '87654321',
            companyVatNumber: 'CZ87654321',
        });
    });

    test('should not use order billing address in account mode', () => {
        const userInfo = getGtmUserInfo(currentCustomer, contactInformation, null, undefined, 'account');

        expect(userInfo).toMatchObject({
            firstName: 'Account',
            lastName: 'Customer',
            telephone: '+420777888999',
            street: 'Account Street',
            streetNumber: '10',
            city: 'Account City',
            postcode: '13000',
            country: 'CZ',
            email: 'account@example.com',
            companyName: 'Account Company',
            companyNumber: '87654321',
            companyVatNumber: 'CZ87654321',
        });
    });

    test('should not use order delivery address in account mode', () => {
        const userInfo = getGtmUserInfo(
            currentCustomer,
            {
                ...contactInformation,
                deliveryAddressUuid: 'selected-delivery-address-uuid',
                isDeliveryAddressDifferentFromBilling: true,
            },
            null,
            undefined,
            'account',
        );

        expect(userInfo).toMatchObject({
            firstName: 'Account',
            lastName: 'Customer',
            telephone: '+420777888999',
            street: 'Account Street',
            streetNumber: '10',
            city: 'Account City',
            postcode: '13000',
            country: 'CZ',
            companyName: 'Account Company',
        });
    });

    test('should not fallback to account addresses in order mode', () => {
        const userInfo = getGtmUserInfo(currentCustomer, {
            ...defaultContactInformationState.contactInformation,
            newsletterSubscription: false,
        });

        expect(userInfo.firstName).toBeUndefined();
        expect(userInfo.street).toBeUndefined();
        expect(userInfo.city).toBeUndefined();
        expect(userInfo.email).toBeUndefined();
    });

    test('should use entered delivery company name before billing company name', () => {
        const userInfo = getGtmUserInfo(undefined, {
            ...contactInformation,
            isDeliveryAddressDifferentFromBilling: true,
            deliveryCompanyName: 'Entered Delivery Company',
        });

        expect(userInfo.companyName).toBe('Entered Delivery Company');
    });

    test('should use newsletter subscription value from submitted contact information', () => {
        const userInfo = getGtmUserInfo(currentCustomer, {
            ...contactInformation,
            newsletterSubscription: false,
        });

        expect(userInfo.newsletterSubscription).toBe(false);
    });
});
