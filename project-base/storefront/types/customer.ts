import { TypeLoginInfoFragment } from 'graphql/requests/customer/fragments/LoginInfoFragment.generated';
import { TypeSalesRepresentativeFragment } from 'graphql/requests/customer/fragments/SalesRepresentativeFragment.generated';
import { TypeCountry, TypeCustomerUserRoleGroup } from 'graphql/types';

export enum CustomerTypeEnum {
    CommonCustomer = 'commonCustomer',
    CompanyCustomer = 'companyCustomer',
}

export enum CustomerUserAreaEnum {
    B2C = 'B2C',
    B2B = 'B2B',
    B2E = 'B2E',
}

export type DeliveryAddressType = {
    uuid: string;
    companyName: string;
    street: string;
    city: string;
    postcode: string;
    telephonePrefix: string;
    telephonePrefixCountryCode: string;
    telephoneNumber: string;
    telephone: string;
    firstName: string;
    lastName: string;
    country: TypeCountry;
};

export type CustomerUserType = {
    firstName: string;
    lastName: string;
    email: string;
    telephonePrefix: string;
    telephonePrefixCountryCode: string;
    telephoneNumber: string;
    telephone: string;
};

export type CurrentCustomerType = {
    uuid: string;
    companyCustomer: boolean;
    firstName: string;
    lastName: string;
    email: string;
    telephonePrefix: string;
    telephonePrefixCountryCode: string;
    telephoneNumber: string;
    telephone: string;
    billingAddressUuid: string;
    street: string;
    city: string;
    postcode: string;
    country: TypeCountry;
    newsletterSubscription: boolean;
    companyName: string;
    companyNumber: string;
    companyTaxNumber: string;
    oldPassword: string;
    newPassword: string;
    newPasswordConfirm: string;
    defaultDeliveryAddress: DeliveryAddressType | undefined;
    deliveryAddresses: DeliveryAddressType[];
    pricingGroup: string;
    hasPasswordSet: boolean;
    loginInfo: TypeLoginInfoFragment;
    roles: string[];
    roleGroup: TypeCustomerUserRoleGroup;
    salesRepresentative?: TypeSalesRepresentativeFragment | null;
};
