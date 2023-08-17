import { DeliveryAddressFragmentApi } from 'graphql/requests/customer/fragments/DeliveryAddressFragment.generated';
import {
    useCurrentCustomerUserQueryApi,
    CurrentCustomerUserQueryApi,
} from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { useMemo } from 'react';
import { CurrentCustomerType, DeliveryAddressType } from 'types/customer';

export function useCurrentCustomerData(): CurrentCustomerType | null | undefined {
    const [{ data }] = useCurrentCustomerUserQueryApi();

    return useMemo(() => {
        if (!data?.currentCustomerUser) {
            return undefined;
        }

        return mapCurrentCustomerApiData(data.currentCustomerUser);
    }, [data?.currentCustomerUser]);
}

const mapCurrentCustomerApiData = (
    apiCurrentCustomerData: CurrentCustomerUserQueryApi['currentCustomerUser'],
): CurrentCustomerType | null => {
    if (!apiCurrentCustomerData) {
        return null;
    }

    const isCompanyCustomer = apiCurrentCustomerData.__typename === 'CompanyCustomerUser';

    return {
        ...apiCurrentCustomerData,
        companyCustomer: isCompanyCustomer,
        telephone: apiCurrentCustomerData.telephone ? apiCurrentCustomerData.telephone : '',
        companyName: isCompanyCustomer && apiCurrentCustomerData.companyName ? apiCurrentCustomerData.companyName : '',
        companyNumber:
            isCompanyCustomer && apiCurrentCustomerData.companyNumber ? apiCurrentCustomerData.companyNumber : '',
        companyTaxNumber:
            isCompanyCustomer && apiCurrentCustomerData.companyTaxNumber ? apiCurrentCustomerData.companyTaxNumber : '',
        defaultDeliveryAddress: apiCurrentCustomerData.defaultDeliveryAddress
            ? mapDeliveryAddress(apiCurrentCustomerData.defaultDeliveryAddress)
            : undefined,
        deliveryAddresses: mapDeliveryAddresses(apiCurrentCustomerData.deliveryAddresses),
        passwordOld: '',
        passwordFirst: '',
        passwordSecond: '',
    };
};

export const mapDeliveryAddress = (apiDeliveryAddressData: DeliveryAddressFragmentApi): DeliveryAddressType => {
    return {
        ...apiDeliveryAddressData,
        companyName: apiDeliveryAddressData.companyName ?? '',
        street: apiDeliveryAddressData.street ?? '',
        city: apiDeliveryAddressData.city ?? '',
        postcode: apiDeliveryAddressData.postcode ?? '',
        telephone: apiDeliveryAddressData.telephone ?? '',
        firstName: apiDeliveryAddressData.firstName ?? '',
        lastName: apiDeliveryAddressData.lastName ?? '',
        country: apiDeliveryAddressData.country?.name ?? '',
    };
};

export const mapDeliveryAddresses = (apiDeliveryAddressesData: DeliveryAddressFragmentApi[]): DeliveryAddressType[] => {
    return apiDeliveryAddressesData.map((address) => mapDeliveryAddress(address));
};
