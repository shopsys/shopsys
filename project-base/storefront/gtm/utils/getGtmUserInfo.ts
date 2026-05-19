import { GtmUserStatus } from 'gtm/enums/GtmUserStatus';
import { GtmUserType } from 'gtm/enums/GtmUserType';
import { GtmUserInfoType } from 'gtm/types/objects';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { CurrentCustomerType, CustomerTypeEnum, DeliveryAddressType } from 'types/customer';
import { normalizeTelephone } from 'utils/formaters/normalizeTelephone';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { parseStreetWithHouseNumber } from 'utils/parsing/parseStreetWithHouseNumber';

type GtmUserInfoSource = {
    firstName?: string;
    lastName?: string;
    telephone?: string;
    street?: string;
    city?: string;
    postcode?: string;
    country?: string;
    email?: string;
    companyName?: string;
    companyNumber?: string;
    companyVatNumber?: string;
};

type GtmUserInfoSourceMode = 'order' | 'account';

export const getGtmUserInfo = (
    currentSignedInCustomer: CurrentCustomerType | null | undefined,
    userContactInformation: ContactInformation,
    pickupPlace?: StoreOrPacketeryPoint | null,
    ipAddress?: string,
    sourceMode: GtmUserInfoSourceMode = 'order',
): GtmUserInfoType => {
    const userInfo: GtmUserInfoType = getGtmUserInfoFromAvailableSources(
        currentSignedInCustomer,
        userContactInformation,
        pickupPlace,
        ipAddress,
        sourceMode,
    );

    if (currentSignedInCustomer) {
        overwriteGtmUserInfoWithLoggedCustomer(userInfo, currentSignedInCustomer);
    }

    return userInfo;
};

const getGtmUserInfoFromAvailableSources = (
    currentSignedInCustomer: CurrentCustomerType | null | undefined,
    userContactInformation: ContactInformation,
    pickupPlace?: StoreOrPacketeryPoint | null,
    ipAddress?: string,
    sourceMode: GtmUserInfoSourceMode = 'order',
): GtmUserInfoType => {
    const userInfoSource = getFirstFilledGtmUserInfoSource(
        currentSignedInCustomer,
        userContactInformation,
        pickupPlace,
        sourceMode,
    );
    const streetInfo = isFilled(userInfoSource.street) ? parseStreetWithHouseNumber(userInfoSource.street) : undefined;

    return {
        status: GtmUserStatus.visitor,
        ...(isFilled(userInfoSource.city) && { city: userInfoSource.city }),
        ...(isFilled(userInfoSource.country) && { country: userInfoSource.country }),
        ...(isFilled(userInfoSource.email) && { email: userInfoSource.email }),
        ...(isFilled(userInfoSource.firstName) && { firstName: userInfoSource.firstName }),
        ...(isFilled(userInfoSource.telephone) && { telephone: normalizeTelephone(userInfoSource.telephone) }),
        ...(isFilled(userInfoSource.postcode) && { postcode: userInfoSource.postcode }),
        ...(isFilled(streetInfo?.street) && { street: streetInfo.street }),
        ...(isFilled(streetInfo?.streetNumber) && { streetNumber: streetInfo.streetNumber }),
        ...(isFilled(userInfoSource.lastName) && { lastName: userInfoSource.lastName }),
        ...(isFilled(userInfoSource.companyName) && { companyName: userInfoSource.companyName }),
        ...(isFilled(userInfoSource.companyNumber) && { companyNumber: userInfoSource.companyNumber }),
        ...(isFilled(userInfoSource.companyVatNumber) && { companyVatNumber: userInfoSource.companyVatNumber }),
        ...(isFilled(ipAddress) && { ipAddress }),
        type: getGtmUserType(userContactInformation.customer),
        newsletterSubscription: userContactInformation.newsletterSubscription,
        pickupSomeoneElse: !!pickupPlace && userContactInformation.isDeliveryAddressDifferentFromBilling,
    };
};

const getFirstFilledGtmUserInfoSource = (
    currentSignedInCustomer: CurrentCustomerType | null | undefined,
    userContactInformation: ContactInformation,
    pickupPlace?: StoreOrPacketeryPoint | null,
    sourceMode: GtmUserInfoSourceMode = 'order',
): GtmUserInfoSource => {
    const sources =
        sourceMode === 'order'
            ? [
                  getGtmUserInfoSourceFromPickupPlace(pickupPlace),
                  getGtmUserInfoSourceFromDeliveryAddress(currentSignedInCustomer, userContactInformation),
                  getGtmUserInfoSourceFromBillingAddress(userContactInformation),
              ]
            : [
                  getGtmUserInfoSourceFromCurrentCustomer(currentSignedInCustomer),
                  getGtmUserInfoSourceFromDeliveryAddressType(currentSignedInCustomer?.defaultDeliveryAddress),
              ];

    return sources.reduce<GtmUserInfoSource>((result, source) => {
        for (const [key, value] of Object.entries(source) as [keyof GtmUserInfoSource, string | undefined][]) {
            if (!isFilled(result[key]) && isFilled(value)) {
                result[key] = value;
            }
        }

        return result;
    }, {});
};

const getGtmUserInfoSourceFromPickupPlace = (
    pickupPlace: StoreOrPacketeryPoint | null | undefined,
): GtmUserInfoSource => ({
    street: pickupPlace?.street,
    city: pickupPlace?.city,
    postcode: pickupPlace?.postcode,
    country: pickupPlace?.country.code,
});

const getGtmUserInfoSourceFromDeliveryAddress = (
    currentSignedInCustomer: CurrentCustomerType | null | undefined,
    userContactInformation: ContactInformation,
): GtmUserInfoSource => {
    if (!userContactInformation.isDeliveryAddressDifferentFromBilling) {
        return {};
    }

    const selectedDeliveryAddress = currentSignedInCustomer?.deliveryAddresses.find(
        (deliveryAddress) => deliveryAddress.uuid === userContactInformation.deliveryAddressUuid,
    );

    if (selectedDeliveryAddress) {
        return getGtmUserInfoSourceFromDeliveryAddressType(selectedDeliveryAddress);
    }

    return {
        firstName: userContactInformation.deliveryFirstName,
        lastName: userContactInformation.deliveryLastName,
        telephone: formatGtmTelephone(
            userContactInformation.deliveryTelephonePrefix,
            userContactInformation.deliveryTelephone,
        ),
        street: userContactInformation.deliveryStreet,
        city: userContactInformation.deliveryCity,
        postcode: userContactInformation.deliveryPostcode,
        country: userContactInformation.deliveryCountry.value,
        companyName: userContactInformation.deliveryCompanyName,
    };
};

const getGtmUserInfoSourceFromBillingAddress = (userContactInformation: ContactInformation): GtmUserInfoSource => ({
    firstName: userContactInformation.firstName,
    lastName: userContactInformation.lastName,
    telephone: formatGtmTelephone(userContactInformation.telephonePrefix, userContactInformation.telephone),
    street: userContactInformation.street,
    city: userContactInformation.city,
    postcode: userContactInformation.postcode,
    country: userContactInformation.country.value,
    email: userContactInformation.email,
    companyName: userContactInformation.companyName,
    companyNumber: userContactInformation.companyNumber,
    companyVatNumber: userContactInformation.companyTaxNumber,
});

const getGtmUserInfoSourceFromDeliveryAddressType = (
    deliveryAddress: DeliveryAddressType | undefined,
): GtmUserInfoSource => ({
    firstName: deliveryAddress?.firstName,
    lastName: deliveryAddress?.lastName,
    telephone: deliveryAddress?.telephone,
    street: deliveryAddress?.street,
    city: deliveryAddress?.city,
    postcode: deliveryAddress?.postcode,
    country: deliveryAddress?.country.code,
    companyName: deliveryAddress?.companyName,
});

const getGtmUserInfoSourceFromCurrentCustomer = (
    currentSignedInCustomer: CurrentCustomerType | null | undefined,
): GtmUserInfoSource => ({
    firstName: currentSignedInCustomer?.firstName,
    lastName: currentSignedInCustomer?.lastName,
    telephone: currentSignedInCustomer?.telephone,
    street: currentSignedInCustomer?.street,
    city: currentSignedInCustomer?.city,
    postcode: currentSignedInCustomer?.postcode,
    country: currentSignedInCustomer?.country.code,
    email: currentSignedInCustomer?.email,
    companyName: currentSignedInCustomer?.companyName,
    companyNumber: currentSignedInCustomer?.companyNumber,
    companyVatNumber: currentSignedInCustomer?.companyTaxNumber,
});

const formatGtmTelephone = (telephonePrefix: string, telephone: string): string | undefined => {
    if (!isFilled(telephone)) {
        return undefined;
    }

    if (!isFilled(telephonePrefix)) {
        return telephone;
    }

    return normalizeTelephone(`${telephonePrefix}${telephone}`);
};

const isFilled = (value: string | undefined): value is string => value !== undefined && value.length > 0;

const getGtmUserType = (customerType: CustomerTypeEnum | undefined): GtmUserType | undefined => {
    if (customerType === undefined) {
        return undefined;
    }

    if (customerType === CustomerTypeEnum.CompanyCustomer) {
        return GtmUserType.b2b;
    }

    return GtmUserType.b2c;
};

const overwriteGtmUserInfoWithLoggedCustomer = (
    userInfo: GtmUserInfoType,
    currentSignedInCustomer: CurrentCustomerType,
) => {
    userInfo.status = GtmUserStatus.customer;
    userInfo.id = currentSignedInCustomer.uuid;
    userInfo.group = currentSignedInCustomer.pricingGroup;
    if (userInfo.loginType === undefined) {
        userInfo.loginType = currentSignedInCustomer.loginInfo.loginType;
    }
    if (userInfo.externalId === undefined) {
        userInfo.externalId = currentSignedInCustomer.loginInfo.externalId;
    }

    if (userInfo.type !== undefined) {
        return;
    }

    if (currentSignedInCustomer.companyCustomer) {
        userInfo.type = GtmUserType.b2b;
    } else {
        userInfo.type = GtmUserType.b2c;
    }
};
