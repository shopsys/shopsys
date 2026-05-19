import { TypeLoginTypeEnum } from 'graphql/types';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { getGtmPageReadyEvent } from 'gtm/factories/getGtmPageReadyEvent';
import { getGtmPageInfoType } from 'gtm/utils/getGtmPageInfoType';
import { ContactInformation, defaultContactInformationState } from 'store/slices/createContactInformationSlice';
import { CurrentCustomerType } from 'types/customer';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { describe, expect, test } from 'vitest';
import { defaultTestDomainConfig } from 'vitest/helpers/mockPublicConfig';

const contactInformationWithDeliveryAddress = {
    ...defaultContactInformationState.contactInformation,
    email: 'billing@example.com',
    firstName: 'Billing',
    lastName: 'Customer',
    telephonePrefix: '+420',
    telephonePrefixCountryCode: 'CZ',
    telephone: '111222333',
    street: 'Billing Street 123',
    city: 'Billing City',
    postcode: '11000',
    country: { value: 'CZ', label: 'Czechia' },
    isDeliveryAddressDifferentFromBilling: true,
    deliveryFirstName: 'Delivery',
    deliveryLastName: 'Customer',
    deliveryTelephone: '444555666',
    deliveryStreet: 'Delivery Street 456',
    deliveryCity: 'Delivery City',
    deliveryPostcode: '22000',
    deliveryCountry: { value: 'CZ', label: 'Czechia' },
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
    deliveryAddresses: [],
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

describe('getGtmPageReadyEvent', () => {
    test('should create page_ready event', () => {
        const result = getGtmPageReadyEvent(
            getGtmPageInfoType(GtmPageType.homepage),
            null,
            true,
            null,
            defaultContactInformationState.contactInformation,
            defaultTestDomainConfig,
            null,
        );

        expect(result.event).toBe(GtmEventType.page_ready);
    });

    test('should not use order delivery address by default', () => {
        const result = getGtmPageReadyEvent(
            getGtmPageInfoType(GtmPageType.homepage),
            null,
            true,
            null,
            contactInformationWithDeliveryAddress,
            defaultTestDomainConfig,
            null,
        );

        expect(result.user.firstName).toBeUndefined();
        expect(result.user.street).toBeUndefined();
        expect(result.user.streetNumber).toBeUndefined();
    });

    test('should use account billing address before default delivery address', () => {
        const result = getGtmPageReadyEvent(
            getGtmPageInfoType(GtmPageType.order_confirmation),
            null,
            true,
            currentCustomer,
            contactInformationWithDeliveryAddress,
            defaultTestDomainConfig,
            null,
            pickupPlace,
        );

        expect(result.user).toMatchObject({
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
});
