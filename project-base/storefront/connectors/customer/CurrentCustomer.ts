import { useCurrentCustomerUserQueryData } from 'components/providers/CurrentCustomerUserProvider';
import { TypeDeliveryAddress } from 'graphql/types';
import { CurrentCustomerType, DeliveryAddressType } from 'types/customer';

export const useCurrentCustomerData = (): CurrentCustomerType | undefined => {
    const currentCustomerUserData = useCurrentCustomerUserQueryData();

    if (!currentCustomerUserData?.currentCustomerUser) {
        return undefined;
    }

    const { currentCustomerUser } = currentCustomerUserData;
    const isCompanyCustomer = currentCustomerUser.__typename === 'CurrentCompanyCustomerUser';

    return {
        ...currentCustomerUser,
        companyCustomer: isCompanyCustomer,
        firstName: currentCustomerUser.firstName ?? '',
        lastName: currentCustomerUser.lastName ?? '',
        billingAddressUuid: currentCustomerUser.billingAddressUuid,
        street: currentCustomerUser.street ?? '',
        city: currentCustomerUser.city ?? '',
        postcode: currentCustomerUser.postcode ?? '',
        telephonePrefix: currentCustomerUser.telephoneData?.prefix ?? '',
        telephonePrefixCountryCode: currentCustomerUser.telephoneData?.countryCode ?? '',
        telephoneNumber: currentCustomerUser.telephoneData?.number ?? '',
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
        country: currentCustomerUser.country ?? {
            __typename: 'Country',
            name: '',
            code: '',
        },
    };
};

const mapDeliveryAddress = (apiDeliveryAddressData: TypeDeliveryAddress): DeliveryAddressType => {
    return {
        ...apiDeliveryAddressData,
        companyName: apiDeliveryAddressData.companyName ?? '',
        street: apiDeliveryAddressData.street ?? '',
        city: apiDeliveryAddressData.city ?? '',
        postcode: apiDeliveryAddressData.postcode ?? '',
        telephonePrefix: apiDeliveryAddressData.telephoneData?.prefix ?? '',
        telephonePrefixCountryCode: apiDeliveryAddressData.telephoneData?.countryCode ?? '',
        telephoneNumber: apiDeliveryAddressData.telephoneData?.number ?? '',
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
