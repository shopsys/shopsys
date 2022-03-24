import { CurrentCustomerType, DeliveryAddressType } from 'types/customer';
import {
    CurrentCustomerUserQueryApi,
    DeliveryAddressFragmentApi,
    useCurrentCustomerUserQueryApi,
} from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/UseQueryError';

export function useCurrentCustomerData(): CurrentCustomerType | undefined {
    const [{ data, error }] = useCurrentCustomerUserQueryApi();
    useQueryError(error);

    if (data?.currentCustomerUser === undefined) {
        return undefined;
    }

    return mapCurrentCustomerApiData(data.currentCustomerUser);
}

const mapCurrentCustomerApiData = (
    apiCurrentCustomerData: CurrentCustomerUserQueryApi['currentCustomerUser'],
): CurrentCustomerType => {
    const mappedCurrentCustomerData = {
        ...apiCurrentCustomerData,
        isCompanyUser: apiCurrentCustomerData.__typename === 'CompanyCustomerUser',
        telephone: apiCurrentCustomerData.telephone === null ? '' : apiCurrentCustomerData.telephone,
        country: {
            value: apiCurrentCustomerData.country.code,
            label: apiCurrentCustomerData.country.name,
        },
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

    return mappedCurrentCustomerData;
};

export const mapDeliveryAddress = (apiDeliveryAddressData: DeliveryAddressFragmentApi): DeliveryAddressType => {
    return {
        ...apiDeliveryAddressData,
        country: apiDeliveryAddressData.country.name,
    };
};

export const mapDeliveryAddresses = (apiDeliveryAddressesData: DeliveryAddressFragmentApi[]): DeliveryAddressType[] => {
    return apiDeliveryAddressesData.map((address) => mapDeliveryAddress(address));
};
