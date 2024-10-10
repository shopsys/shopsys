import {
    CardData,
    GetCartType,
    GetPaymentsType,
    GetStoresType,
    GetTransportsType,
    PaymentData,
    PaymentTypes,
    StoreData,
    StoreDataOpeningHour,
    StoreDataProductOnStoreAvailability,
    TransportData,
    TransportSources,
} from 'convertim-react-lib';
import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { TypeTransportWithAvailablePaymentsAndStoresFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsAndStoresFragment.generated';
import { TypeTransportWithAvailablePaymentsFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsFragment.generated';
import { TypeOpeningHours, TypeTransportTypeEnum } from 'graphql/types';

export const getTransports =
    (transports?: TypeTransportWithAvailablePaymentsFragment[]): GetTransportsType =>
    (setData) => {
        setData(mapTransportsData(transports));
    };

export const getPayments = (transports?: TypeTransportWithAvailablePaymentsFragment[]): GetPaymentsType => {
    const paymentsData = mapPaymentsData(transports);
    return (setData) => {
        setData(paymentsData);
    };
};

export const getCart =
    (cart: TypeCartFragment): GetCartType =>
    (setData) => {
        setData(mapCartData(cart));
    };

export const getStores = (
    dayNames: string[],
    cart: TypeCartFragment,
    transports?: TypeTransportWithAvailablePaymentsAndStoresFragment[],
): GetStoresType => {
    const storesData = mapStoresData(dayNames, cart, transports);
    return (setData) => {
        setData(storesData);
    };
};

export const getGtm = () => {
    return {};
};

const mapTransportType = (type: TypeTransportTypeEnum): TransportSources | null => {
    switch (type) {
        case TypeTransportTypeEnum.Common:
            return null;
        case TypeTransportTypeEnum.Packetery:
            return TransportSources.PACKETA;
        case TypeTransportTypeEnum.PersonalPickup:
            return TransportSources.STORES;
        default:
            return null;
    }
};

const mapTransportsData = (transports?: TypeTransportWithAvailablePaymentsFragment[]): TransportData[] => {
    return (
        transports?.map((transport) => ({
            uuid: transport.uuid,
            name: transport.name,
            isShortForm: true,
            transportDescription: transport.description ?? '',
            source: mapTransportType(transport.transportTypeCode),
            group: null,
            type: null,
            priceWithVat: parseFloat(transport.price.priceWithVat),
            priceWithoutVat: parseFloat(transport.price.priceWithoutVat),
            vat: parseFloat(transport.price.vatAmount),
            services: [],
            image: transport.mainImage?.url ?? null,
            groupDescription: null,
            deliveryTime: transport.daysUntilDelivery.toString(),
            calculatedDeliveryTime: null,
        })) ?? []
    );
};

const mapPaymentType = (type: string): PaymentTypes => {
    switch (type) {
        case 'basic':
            return PaymentTypes.CASH_ON_DELIVERY;
        case 'goPay':
            return PaymentTypes.GOPAY;
        default:
            return PaymentTypes.CASH_ON_DELIVERY;
    }
};

const mapPaymentsData = (transports?: TypeTransportWithAvailablePaymentsFragment[]): PaymentData[] => {
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
                    vat: parseFloat(payment.price.vatAmount),
                    image: payment.mainImage?.url ?? '',
                    gopay: payment.goPayPaymentMethod ? {} : undefined,
                    paymentDescription: payment.description ?? '',
                    restrictedTransports: [...transportUuids],
                    paymentInstruction: payment.instruction ?? '',
                });
            }

            const restrictedTransports = payments.get(payment.uuid)!.restrictedTransports;
            const restrictedTransportIndex = restrictedTransports.findIndex((uuid) => uuid === transport.uuid);
            restrictedTransports.splice(restrictedTransportIndex, 1);
        });
    });

    return Array.from(payments.values());
};

const mapCartData = (cart: TypeCartFragment): CardData => {
    return {
        items: cart.items.map(({ product, discount, quantity, uuid }) => ({
            id: uuid,
            availability: product.availability.name,
            name: product.fullName,
            quantity,
            priceWithoutVat: product.price.priceWithoutVat,
            priceWithVat: product.price.priceWithVat,
            vat: product.price.vatAmount,
            image: product.mainImage?.url ?? null,
            gtm: {},
            labels: product.flags.map(({ name }) => name),
            discount: discount?.map(({ promoCode, totalDiscount }) => ({
                [promoCode]: {
                    withVat: parseFloat(totalDiscount.priceWithVat),
                    withoutVat: parseFloat(totalDiscount.priceWithoutVat),
                },
            }))[0],
        })),
        promoCodes: cart.promoCode ? [cart.promoCode] : [],
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

const mapStoresData = (
    dayNames: string[],
    cart: TypeCartFragment,
    transports?: TypeTransportWithAvailablePaymentsAndStoresFragment[],
): StoreData[] => {
    const cartItemsAvailabilityByStoreUuid = cart.items
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
                    code: store?.node?.country.code ?? '',
                    latitude: store?.node?.latitude ?? '',
                    longitude: store?.node?.longitude ?? '',
                    company: store?.node?.slug ?? '',
                    street: store?.node?.street ?? '',
                    postcode: store?.node?.postcode ?? '',
                    city: store?.node?.city ?? '',
                    source: 'stores',
                    hours: mapOpeningHours(dayNames, store?.node?.openingHours),
                    availability: store?.node?.openingHours.status ?? '',
                    productOnStoreAvailability: cartItemsAvailabilityByStoreUuid.get(store?.node?.identifier ?? ''),
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

export function groupByStoreUuid<T>(acc: Map<string, T[]>, [storeUuid, item]: [string, T]) {
    if (!acc.has(storeUuid)) {
        acc.set(storeUuid, []);
    }

    acc.get(storeUuid)!.push(item);
    return acc;
}
