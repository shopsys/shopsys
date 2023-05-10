import {
    CurrentCustomerUserQueryApi,
    DeliveryAddressFragmentApi,
    useCurrentCustomerUserQueryApi,
} from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { useMemo } from 'react';
import { CurrentCustomerType, DeliveryAddressType } from 'types/customer';

export function useCurrentCustomerData(): CurrentCustomerType | null | undefined {
    const [{ data }] = useQueryError(useCurrentCustomerUserQueryApi());

    return useMemo(() => {
        if (data?.currentCustomerUser === undefined) {
            return undefined;
        }

        return mapCurrentCustomerApiData(data.currentCustomerUser);
    }, [data?.currentCustomerUser]);
}

const mapCurrentCustomerApiData = (
    apiCurrentCustomerData: CurrentCustomerUserQueryApi['currentCustomerUser'],
): CurrentCustomerType | null => {
    if (apiCurrentCustomerData === null) {
        return null;
    }

    return {
        ...apiCurrentCustomerData,
        companyCustomer: apiCurrentCustomerData.__typename === 'CompanyCustomerUser',
        telephone: apiCurrentCustomerData.telephone === null ? '' : apiCurrentCustomerData.telephone,
        companyName:
            apiCurrentCustomerData.__typename === 'CompanyCustomerUser' && apiCurrentCustomerData.companyName !== null
                ? apiCurrentCustomerData.companyName
                : '',
        companyNumber:
            apiCurrentCustomerData.__typename === 'CompanyCustomerUser' && apiCurrentCustomerData.companyNumber !== null
                ? apiCurrentCustomerData.companyNumber
                : '',
        companyTaxNumber:
            apiCurrentCustomerData.__typename === 'CompanyCustomerUser' &&
            apiCurrentCustomerData.companyTaxNumber !== null
                ? apiCurrentCustomerData.companyTaxNumber
                : '',
        defaultDeliveryAddress:
            apiCurrentCustomerData.defaultDeliveryAddress !== null
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
