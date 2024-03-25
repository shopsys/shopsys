import { CountryFragment } from 'graphql/requests/countries/fragments/CountryFragment.generated';

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
    country: string;
};

export type CurrentCustomerType = {
    uuid: string;
    companyCustomer: boolean;
    firstName: string;
    lastName: string;
    email: string;
    telephone: string;
    street: string;
    city: string;
    postcode: string;
    country: CountryFragment;
    newsletterSubscription: boolean;
    companyName: string;
    companyNumber: string;
    companyTaxNumber: string;
    passwordOld: string;
    passwordFirst: string;
    passwordSecond: string;
    defaultDeliveryAddress: DeliveryAddressType | undefined;
    deliveryAddresses: DeliveryAddressType[];
    pricingGroup: string;
};
