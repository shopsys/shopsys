import {
    useCurrentCustomerUserQuery,
    CurrentCustomerUserQuery,
} from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { CustomerTypeEnum } from 'types/customer';

export function useCurrentCustomerContactInformationQuery(): ContactInformation | null | undefined {
    const [{ data }] = useCurrentCustomerUserQuery();

    if (data?.currentCustomerUser === undefined) {
        return undefined;
    }

    return mapCurrentCustomerContactInformationApiData(data.currentCustomerUser);
}

const mapCurrentCustomerContactInformationApiData = (
    apiCurrentCustomerUserData: CurrentCustomerUserQuery['currentCustomerUser'],
): ContactInformation | null => {
    if (apiCurrentCustomerUserData === null) {
        return null;
    }

    // EXTEND CUSTOMER CONTACT INFORMATION HERE

    return {
        ...apiCurrentCustomerUserData,
        companyName:
            apiCurrentCustomerUserData.__typename === 'CompanyCustomerUser' &&
            apiCurrentCustomerUserData.companyName !== null
                ? apiCurrentCustomerUserData.companyName
                : '',
        companyNumber:
            apiCurrentCustomerUserData.__typename === 'CompanyCustomerUser' &&
            apiCurrentCustomerUserData.companyNumber !== null
                ? apiCurrentCustomerUserData.companyNumber
                : '',
        companyTaxNumber:
            apiCurrentCustomerUserData.__typename === 'CompanyCustomerUser' &&
            apiCurrentCustomerUserData.companyTaxNumber !== null
                ? apiCurrentCustomerUserData.companyTaxNumber
                : '',
        telephone: apiCurrentCustomerUserData.telephone !== null ? apiCurrentCustomerUserData.telephone : '',
        country: {
            value: apiCurrentCustomerUserData.country.code,
            label: apiCurrentCustomerUserData.country.name,
        },
        deliveryFirstName: apiCurrentCustomerUserData.defaultDeliveryAddress?.firstName ?? '',
        deliveryLastName: apiCurrentCustomerUserData.defaultDeliveryAddress?.lastName ?? '',
        deliveryCompanyName: apiCurrentCustomerUserData.defaultDeliveryAddress?.companyName ?? '',
        deliveryTelephone: apiCurrentCustomerUserData.defaultDeliveryAddress?.telephone ?? '',
        deliveryStreet: apiCurrentCustomerUserData.defaultDeliveryAddress?.street ?? '',
        deliveryCity: apiCurrentCustomerUserData.defaultDeliveryAddress?.city ?? '',
        deliveryPostcode: apiCurrentCustomerUserData.defaultDeliveryAddress?.postcode ?? '',
        deliveryCountry: {
            value: apiCurrentCustomerUserData.defaultDeliveryAddress?.country?.code ?? '',
            label: apiCurrentCustomerUserData.defaultDeliveryAddress?.country?.name ?? '',
        },
        deliveryAddressUuid: apiCurrentCustomerUserData.defaultDeliveryAddress?.uuid ?? null,
        customer:
            apiCurrentCustomerUserData.__typename === 'CompanyCustomerUser'
                ? CustomerTypeEnum.CompanyCustomer
                : CustomerTypeEnum.CommonCustomer,
        differentDeliveryAddress: false,
        note: '',
    };
};
