import { TypeCountry, TypeCustomerUserRoleGroup, TypeLoginInfo, TypeSalesRepresentative } from 'graphql/types';

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
    telephone: string;
    firstName: string;
    lastName: string;
    country: TypeCountry;
};

export type CustomerUserType = {
    firstName: string;
    lastName: string;
    email: string;
    telephone: string;
};

export type CurrentCustomerType = {
    uuid: string;
    companyCustomer: boolean;
    firstName: string;
    lastName: string;
    email: string;
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
    loginInfo: TypeLoginInfo;
    roles: string[];
    roleGroup: TypeCustomerUserRoleGroup;
    salesRepresentative?: TypeSalesRepresentative | null;
};
