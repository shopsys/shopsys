'use server';

import { createQuery } from 'app/_urql/urql-dto';
import {
    CurrentCustomerUserQueryDocument,
    TypeCurrentCustomerUserQuery,
    TypeCurrentCustomerUserQueryVariables,
} from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.ssr';
import { TypeDeliveryAddress } from 'graphql/types';
import { DeliveryAddressType } from 'types/customer';

export async function getCurrentCustomerData() {
    const result = await createQuery<TypeCurrentCustomerUserQuery, TypeCurrentCustomerUserQueryVariables>(
        CurrentCustomerUserQueryDocument,
        {},
    );

    if (!result.data?.currentCustomerUser) {
        return undefined;
    }

    const { currentCustomerUser } = result.data;
    const isCompanyCustomer = currentCustomerUser.__typename === 'CompanyCustomerUser';

    return {
        ...currentCustomerUser,
        companyCustomer: isCompanyCustomer,
        firstName: currentCustomerUser.firstName ?? '',
        lastName: currentCustomerUser.lastName ?? '',
        billingAddressUuid: currentCustomerUser.billingAddressUuid,
        street: currentCustomerUser.street ?? '',
        city: currentCustomerUser.city ?? '',
        postcode: currentCustomerUser.postcode ?? '',
        telephone: currentCustomerUser.telephone ?? '',
        companyName: isCompanyCustomer && currentCustomerUser.companyName ? currentCustomerUser.companyName : '',
        companyNumber: isCompanyCustomer && currentCustomerUser.companyNumber ? currentCustomerUser.companyNumber : '',
        companyTaxNumber:
            isCompanyCustomer && currentCustomerUser.companyTaxNumber ? currentCustomerUser.companyTaxNumber : '',
        defaultDeliveryAddress: currentCustomerUser.defaultDeliveryAddress
            ? mapDeliveryAddress(currentCustomerUser.defaultDeliveryAddress)
            : undefined,
        deliveryAddresses:
            currentCustomerUser.deliveryAddresses.length > 0
                ? mapDeliveryAddresses(currentCustomerUser.deliveryAddresses)
                : [],
        oldPassword: '',
        newPassword: '',
        newPasswordConfirm: '',
        arePricesHidden: !currentCustomerUser.roles.includes('ROLE_API_CUSTOMER_SEES_PRICES'),
        country: currentCustomerUser.country ?? {
            __typename: 'Country',
            name: '',
            code: '',
        },
    };
}

const mapDeliveryAddress = (apiDeliveryAddressData: TypeDeliveryAddress): DeliveryAddressType => {
    return {
        ...apiDeliveryAddressData,
        companyName: apiDeliveryAddressData.companyName ?? '',
        street: apiDeliveryAddressData.street ?? '',
        city: apiDeliveryAddressData.city ?? '',
        postcode: apiDeliveryAddressData.postcode ?? '',
        telephone: apiDeliveryAddressData.telephone ?? '',
        firstName: apiDeliveryAddressData.firstName ?? '',
        lastName: apiDeliveryAddressData.lastName ?? '',
        country: apiDeliveryAddressData.country ?? {
            __typename: 'Country',
            name: '',
            code: '',
        },
    };
};

const mapDeliveryAddresses = (apiDeliveryAddressesData: TypeDeliveryAddress[]): DeliveryAddressType[] => {
    return apiDeliveryAddressesData.map((address) => mapDeliveryAddress(address));
};
