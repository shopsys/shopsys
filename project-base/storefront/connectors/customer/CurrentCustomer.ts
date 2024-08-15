import { useCurrentCustomerUserQuery } from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { TypeCompanyCustomerUser, TypeDeliveryAddress } from 'graphql/types';
import { CurrentCustomerType, DeliveryAddressType } from 'types/customer';

export const useCurrentCustomerData = (companyCustomerUser?: TypeCompanyCustomerUser): CurrentCustomerType | null | undefined => {
    const [{ data: currentCustomerUserData }] = useCurrentCustomerUserQuery();

    if (!currentCustomerUserData?.currentCustomerUser) {
        return undefined;
    }

    const currentCustomerUser = companyCustomerUser ?? currentCustomerUserData.currentCustomerUser;
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
    };
};

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
