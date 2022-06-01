import { CurrentCustomerUserQueryApi, useCurrentCustomerUserQueryApi } from 'graphql/generated';
import { CustomerTypeEnum } from 'types/customer';
import { ContactInformationFormType } from 'types/form';

export function useCurrentCustomerContactInformationQuery(): ContactInformationFormType | undefined {
    const [{ data }] = useCurrentCustomerUserQueryApi();

    if (data?.currentCustomerUser === undefined) {
        return undefined;
    }

    return mapCurrentCustomerContactInformationApiData(data);
}

const mapCurrentCustomerContactInformationApiData = (
    apiCurrentCustomerUserData: CurrentCustomerUserQueryApi,
): ContactInformationFormType => {
    const companyCustomerUser = apiCurrentCustomerUserData.currentCustomerUser;

    // EXTEND CUSTOMER CONTACT INFORMATION HERE

    return {
        ...companyCustomerUser,
        companyName:
            apiCurrentCustomerUserData.currentCustomerUser.__typename === 'CompanyCustomerUser' &&
            apiCurrentCustomerUserData.currentCustomerUser.companyName !== null
                ? apiCurrentCustomerUserData.currentCustomerUser.companyName
                : '',
        companyNumber:
            apiCurrentCustomerUserData.currentCustomerUser.__typename === 'CompanyCustomerUser' &&
            apiCurrentCustomerUserData.currentCustomerUser.companyNumber !== null
                ? apiCurrentCustomerUserData.currentCustomerUser.companyNumber
                : '',
        companyTaxNumber:
            apiCurrentCustomerUserData.currentCustomerUser.__typename === 'CompanyCustomerUser' &&
            apiCurrentCustomerUserData.currentCustomerUser.companyTaxNumber !== null
                ? apiCurrentCustomerUserData.currentCustomerUser.companyTaxNumber
                : '',
        telephone: companyCustomerUser.telephone !== null ? companyCustomerUser.telephone : '',
        country: {
            value: companyCustomerUser.country.code,
            label: companyCustomerUser.country.name,
        },
        deliveryFirstName: companyCustomerUser.defaultDeliveryAddress?.firstName ?? '',
        deliveryLastName: companyCustomerUser.defaultDeliveryAddress?.lastName ?? '',
        deliveryCompanyName: companyCustomerUser.defaultDeliveryAddress?.companyName ?? '',
        deliveryTelephone: companyCustomerUser.defaultDeliveryAddress?.telephone ?? '',
        deliveryStreet: companyCustomerUser.defaultDeliveryAddress?.street ?? '',
        deliveryCity: companyCustomerUser.defaultDeliveryAddress?.city ?? '',
        deliveryPostcode: companyCustomerUser.defaultDeliveryAddress?.postcode ?? '',
        deliveryCountry: {
            value: companyCustomerUser.defaultDeliveryAddress?.country?.code ?? '',
            label: companyCustomerUser.defaultDeliveryAddress?.country?.name ?? '',
        },
        deliveryAddressUuid: companyCustomerUser.defaultDeliveryAddress?.uuid ?? null,
        register: false,
        passwordFirst: '',
        passwordSecond: '',
        customer:
            apiCurrentCustomerUserData.currentCustomerUser.__typename === 'CompanyCustomerUser'
                ? CustomerTypeEnum.CompanyCustomer
                : CustomerTypeEnum.CommonCustomer,
        differentDeliveryAddress: false,
        note: '',
    };
};
