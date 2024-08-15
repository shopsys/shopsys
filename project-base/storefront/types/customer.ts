import { TypeCountryFragment } from 'graphql/requests/countries/fragments/CountryFragment.generated';
import { TypeLoginInfo } from 'graphql/types';

export enum CustomerTypeEnum {
    CommonCustomer = 'commonCustomer',
    CompanyCustomer = 'companyCustomer',
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
    country: TypeCountryFragment;
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
    country: TypeCountryFragment | null;
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
};
