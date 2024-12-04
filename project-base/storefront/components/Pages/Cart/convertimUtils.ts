import { getDeliveryMessage } from 'components/Pages/Order/TransportAndPayment/transportAndPaymentUtils';
import {
    CartData,
    CartItemPromoCodeFullPrice,
    PaymentData,
    PaymentTypes,
    StoreData,
    StoreDataOpeningHour,
    StoreDataProductOnStoreAvailability,
    TransportData,
    TransportSources,
    TransportTypes,
} from 'convertim-react-lib';
import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { TypeTransportWithAvailablePaymentsAndStoresFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsAndStoresFragment.generated';
import { TypeTransportWithAvailablePaymentsFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsFragment.generated';
import { TypeOpeningHours, TypePaymentTypeEnum, TypePromoCodeTypeEnum, TypeTransportTypeEnum } from 'graphql/types';
import { Translate } from 'next-translate';
import { formatPercent } from 'utils/formaters/formatPercent';
import { FormatPriceFunctionType } from 'utils/formatting/useFormatPrice';

export const getGtm = () => {
    return {};
};

const mapTransportSource = (type: TypeTransportTypeEnum): TransportSources | null => {
    switch (type) {
        case TypeTransportTypeEnum.Common:
            return null;
        case TypeTransportTypeEnum.Packetery:
            return TransportSources.PACKETA;
        case TypeTransportTypeEnum.PersonalPickup:
            return TransportSources.STORES;
        case TypeTransportTypeEnum.ConvertimBalikovna:
            return TransportSources.BALIKOVNA;
        case TypeTransportTypeEnum.ConvertimPpl:
            return TransportSources.PPL;
        case TypeTransportTypeEnum.ConvertimDpdCzechia:
            return TransportSources.DPD;
        case TypeTransportTypeEnum.ConvertimDpdSlovakia:
            return TransportSources.SK_DPD;
        default:
            return null;
    }
};

const mapTransportType = (type: TypeTransportTypeEnum): TransportTypes | null => {
    switch (type) {
        case TypeTransportTypeEnum.Common:
            return null;
        case TypeTransportTypeEnum.Packetery:
            return TransportTypes.PICKUP_PLACE;
        case TypeTransportTypeEnum.PersonalPickup:
            return TransportTypes.PICKUP_PLACE;
        case TypeTransportTypeEnum.ConvertimBalikovna:
            return TransportTypes.PICKUP_PLACE;
        case TypeTransportTypeEnum.ConvertimPpl:
            return TransportTypes.PICKUP_PLACE;
        case TypeTransportTypeEnum.ConvertimDpdCzechia:
            return TransportTypes.PICKUP_PLACE;
        case TypeTransportTypeEnum.ConvertimDpdSlovakia:
            return TransportTypes.PICKUP_PLACE;
        default:
            return null;
    }
};

export const mapTransportsData = (
    transports: TypeTransportWithAvailablePaymentsFragment[] | undefined,
    t: Translate,
): TransportData[] => {
    return (
        transports?.map((transport) => ({
            uuid: transport.uuid,
            name: transport.name,
            isShortForm: mapTransportType(transport.transportTypeCode) === TransportTypes.PICKUP_PLACE,
            transportDescription: transport.description ?? '',
            source: mapTransportSource(transport.transportTypeCode),
            group: null,
            type: mapTransportType(transport.transportTypeCode),
            priceWithVat: parseFloat(transport.price.priceWithVat),
            priceWithoutVat: parseFloat(transport.price.priceWithoutVat),
            vat: parseFloat(transport.vat.percent),
            services: [],
            image: transport.mainImage?.url ?? null,
            groupDescription: null,
            deliveryTime: getDeliveryMessage(
                transport.daysUntilDelivery,
                mapTransportType(transport.transportTypeCode) === TransportTypes.PICKUP_PLACE,
                t,
            ),
            calculatedDeliveryTime: null,
        })) ?? []
    );
};

const mapPaymentType = (type: TypePaymentTypeEnum): PaymentTypes => {
    switch (type) {
        case TypePaymentTypeEnum.Basic:
            return PaymentTypes.CASH_ON_DELIVERY;
        case TypePaymentTypeEnum.ConvertimCashOnDelivery:
            return PaymentTypes.CASH_ON_DELIVERY;
        case TypePaymentTypeEnum.GoPay:
            return PaymentTypes.GOPAY;
        case TypePaymentTypeEnum.ConvertimAdyen:
            return PaymentTypes.ADYEN;
        case TypePaymentTypeEnum.ConvertimComgate:
            return PaymentTypes.COMGATE;
        case TypePaymentTypeEnum.ConvertimQr:
            return PaymentTypes.BANK_TRANSFER_WITH_QR;
        case TypePaymentTypeEnum.ConvertimStripe:
            return PaymentTypes.STRIPE;
        case TypePaymentTypeEnum.ConvertimTrustpay:
            return PaymentTypes.TRUST_PAY;
        case TypePaymentTypeEnum.ConvertimPaypal:
            return PaymentTypes.PAYPAL;
        case TypePaymentTypeEnum.ConvertimEssox:
            return PaymentTypes.ESSOX;
        default:
            return PaymentTypes.CASH_ON_DELIVERY;
    }
};

const getSpecialExtensions = (paymentType: TypePaymentTypeEnum) => {
    switch (paymentType) {
        case TypePaymentTypeEnum.ConvertimComgate:
            return {
                comgate: {
                    defaultPaymentInstruction: 'CARD_CZ_CSOB_2',
                },
            };
        case TypePaymentTypeEnum.ConvertimAdyen:
            return {
                adyen: {
                    method: 'card',
                },
            };
        default:
            return {};
    }
};

export const mapPaymentsData = (transports?: TypeTransportWithAvailablePaymentsFragment[]): PaymentData[] => {
    const transportUuids = transports?.map((transport) => transport.uuid) ?? [];
    const payments: Map<string, PaymentData> = new Map();

    transports?.forEach((transport) => {
        transport.payments.forEach((payment) => {
            if (!payments.has(payment.uuid)) {
                payments.set(payment.uuid, {
                    uuid: payment.uuid,
                    type: mapPaymentType(payment.type),
                    name: payment.name,
                    priceWithVat: parseFloat(payment.price.priceWithVat),
                    priceWithoutVat: parseFloat(payment.price.priceWithoutVat),
                    vat: parseFloat(payment.vat.percent),
                    image: payment.mainImage?.url ?? '',
                    gopay: payment.goPayPaymentMethod
                        ? {
                              allowedPaymentInstruments: [payment.goPayPaymentMethod.paymentGroup],
                          }
                        : undefined,
                    paymentDescription: payment.description ?? '',
                    restrictedTransports: [...transportUuids],
                    paymentInstruction: payment.instruction ?? '',
                    ...getSpecialExtensions(payment.type),
                });
            }

            const restrictedTransports = payments.get(payment.uuid)!.restrictedTransports;
            const restrictedTransportIndex = restrictedTransports.findIndex((uuid) => uuid === transport.uuid);
            restrictedTransports.splice(restrictedTransportIndex, 1);
        });
    });

    return Array.from(payments.values());
};

export const mapCartData = (
    cart: TypeCartFragment | undefined | null,
    formatPrice: FormatPriceFunctionType,
): CartData => {
    return {
        items:
            cart?.items.map(({ product, discounts, quantity }) => ({
                id: product.uuid,
                availability: product.availability.name,
                name: product.fullName,
                quantity,
                priceWithoutVat: product.price.priceWithoutVat,
                priceWithVat: product.price.priceWithVat,
                vat: product.vat.percent,
                image: product.mainImage?.url ?? null,
                gtm: {},
                labels: product.flags.map(({ name }) => name),
                discounts: discounts.reduce(
                    (acc, { promoCode, totalDiscount }) => {
                        acc[promoCode] = {
                            withVat: Math.abs(parseFloat(totalDiscount.priceWithVat)),
                            withoutVat: Math.abs(parseFloat(totalDiscount.priceWithoutVat)),
                        };
                        return acc;
                    },
                    {} as {
                        [_key: string]: CartItemPromoCodeFullPrice | number;
                    },
                ),
            })) ?? [],
        promoCodes:
            cart?.promoCodes.map(({ code, discount, type }) => ({
                code,
                uuid: code,
                discount:
                    type === TypePromoCodeTypeEnum.Nominal
                        ? formatPrice(discount.priceWithVat)
                        : formatPercent(discount.priceWithVat) ?? '',
                discountWithoutVat:
                    type === TypePromoCodeTypeEnum.Nominal
                        ? formatPrice(discount.priceWithoutVat)
                        : formatPercent(discount.priceWithoutVat) ?? '',
            })) ?? [],
    };
};

const mapOpeningHours = (dayNames: string[], openingHours?: TypeOpeningHours): StoreDataOpeningHour[] => {
    return (
        openingHours?.openingHoursOfDays.map((openingHour) => {
            const morning = openingHour.openingHoursRanges.length > 0 ? openingHour.openingHoursRanges[0] : null;
            const afternoon = openingHour.openingHoursRanges.length > 1 ? openingHour.openingHoursRanges[1] : null;
            return {
                day: openingHour.dayOfWeek,
                dayName: dayNames[openingHour.dayOfWeek - 1],
                openMorning: morning?.openingTime ?? '',
                closeMorning: morning?.closingTime ?? null,
                openAfternoon: afternoon?.openingTime ?? null,
                closeAfternoon: afternoon?.closingTime ?? null,
            };
        }) ?? []
    );
};

export const mapStoresData = (
    dayNames: string[],
    cart: TypeCartFragment | undefined | null,
    transports?: TypeTransportWithAvailablePaymentsAndStoresFragment[],
): StoreData[] => {
    const cartItemsAvailabilityByStoreUuid = cart?.items
        .flatMap(getProductOnStoreAvailability)
        .reduce(
            groupByStoreUuid<StoreDataProductOnStoreAvailability>,
            new Map<string, StoreDataProductOnStoreAvailability[]>(),
        );

    return (
        transports?.flatMap(
            (transport) =>
                transport.stores?.edges?.map((store) => ({
                    name: store?.node?.name ?? '',
                    code: store?.node?.identifier ?? '',
                    latitude: store?.node?.latitude ?? '',
                    longitude: store?.node?.longitude ?? '',
                    company: store?.node?.name ?? '',
                    street: store?.node?.street ?? '',
                    postcode: store?.node?.postcode ?? '',
                    city: store?.node?.city ?? '',
                    source: 'stores',
                    hours: mapOpeningHours(dayNames, store?.node?.openingHours),
                    availability: store?.node?.openingHours.status ?? '',
                    productOnStoreAvailability: cartItemsAvailabilityByStoreUuid?.get(store?.node?.identifier ?? ''),
                })) ?? [],
        ) ?? []
    );
};

const getProductOnStoreAvailability = ({
    product,
}: TypeCartItemFragment): [string, StoreDataProductOnStoreAvailability][] => {
    return product.storeAvailabilities.map((storeAvailability) => [
        storeAvailability.store?.uuid ?? '',
        { productUuid: product.uuid, availability: storeAvailability.availabilityInformation },
    ]);
};

function groupByStoreUuid<T>(acc: Map<string, T[]>, [storeUuid, item]: [string, T]) {
    if (!acc.has(storeUuid)) {
        acc.set(storeUuid, []);
    }

    acc.get(storeUuid)!.push(item);
    return acc;
}
