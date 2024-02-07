import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import {
    LastOrderFragmentApi,
    LastOrderQueryApi,
    LastOrderQueryDocumentApi,
    LastOrderQueryVariablesApi,
    ListedStoreFragmentApi,
    Maybe,
    SimplePaymentFragmentApi,
    StoreQueryApi,
    StoreQueryDocumentApi,
    StoreQueryVariablesApi,
    TransportWithAvailablePaymentsAndStoresFragmentApi,
} from 'graphql/generated';
import { getGtmPickupPlaceFromStore, getGtmPickupPlaceFromLastOrder } from 'gtm/helpers/mappers';
import { logException } from 'helpers/errors/logException';
import { mapPacketeryExtendedPoint, packeteryPick } from 'helpers/packetery';
import { ChangePaymentHandler } from 'hooks/cart/useChangePaymentInCart';
import { ChangeTransportHandler } from 'hooks/cart/useChangeTransportInCart';
import { useCurrentCart } from 'hooks/cart/useCurrentCart';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { Translate } from 'next-translate';
import getConfig from 'next/config';
import { useEffect, useState } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useClient } from 'urql';

const { publicRuntimeConfig } = getConfig();

export const usePaymentChangeInSelect = (changePaymentHandler: ChangePaymentHandler) => {
    const { payment: currentPayment, paymentGoPayBankSwift: currentPaymentGoPayBankSwift } = useCurrentCart();

    const changePayment = (updatedPaymentUuid: string | null) =>
        changePaymentHandler(updatedPaymentUuid, currentPaymentGoPayBankSwift);

    const changeGoPaySwift = (newGoPaySwiftValue: string | null) =>
        changePaymentHandler(currentPayment?.uuid ?? null, newGoPaySwiftValue);

    const resetPaymentAndGoPayBankSwift = () => changePaymentHandler(null, null);

    return { changePayment, changeGoPaySwift, resetPaymentAndGoPayBankSwift };
};

export const useTransportChangeInSelect = (
    transports: TransportWithAvailablePaymentsAndStoresFragmentApi[] | undefined,
    lastOrderPickupPlace: ListedStoreFragmentApi | null,
    changeTransportHandler: ChangeTransportHandler,
    changePaymentHandler: ChangePaymentHandler,
) => {
    const { defaultLocale } = useDomainConfig();
    const [preSelectedPickupPlace, setPreSelectedPickupPlace] = useState(lastOrderPickupPlace);
    const [preSelectedTransport, setPreselectedTransport] =
        useState<TransportWithAvailablePaymentsAndStoresFragmentApi | null>(null);
    const clearPacketeryPickupPoint = usePersistStore((store) => store.clearPacketeryPickupPoint);
    const setPacketeryPickupPoint = usePersistStore((store) => store.setPacketeryPickupPoint);
    const { transport: currentTransport, pickupPlace: currentPickupPlace } = useCurrentCart();

    const resetTransportAndPayment = async () => {
        await changeTransportHandler(null, null);
        await changePaymentHandler(null, null);
        setPreSelectedPickupPlace(null);
        clearPacketeryPickupPoint();
    };

    const changeTransport = async (updatedTransportUuid: string | null) => {
        const updatedTransport = transports?.find((transport) => transport.uuid === updatedTransportUuid);

        if (!updatedTransport) {
            resetTransportAndPayment();

            return;
        }

        if (updatedTransport.uuid === currentTransport?.uuid) {
            return;
        }

        if (updatedTransport.isPersonalPickup || updatedTransport.transportType.code === 'packetery') {
            if (!preSelectedPickupPlace) {
                openPersonalPickupPopup(updatedTransport);

                return;
            }

            changeTransportHandler(updatedTransport.uuid, preSelectedPickupPlace);
            setPreSelectedPickupPlace(null);

            return;
        }

        if (updatedTransport.uuid !== currentTransport?.uuid) {
            changeTransportHandler(updatedTransport.uuid, null);
        }
    };

    const openPacketeryPopup = (newTransport: TransportWithAvailablePaymentsAndStoresFragmentApi) => {
        if (!currentPickupPlace) {
            const packeteryApiKey = publicRuntimeConfig.packeteryApiKey;

            if (!packeteryApiKey?.length) {
                logException(new Error(`Packeta API key was not set`));
                return;
            }

            packeteryPick(
                packeteryApiKey,
                (packeteryPoint) => {
                    if (packeteryPoint) {
                        const mappedPacketeryPoint = mapPacketeryExtendedPoint(packeteryPoint);
                        setPacketeryPickupPoint(mappedPacketeryPoint);
                        changeTransportHandler(newTransport.uuid, mappedPacketeryPoint);
                    }
                },
                { language: defaultLocale },
            );
        }
    };

    const openPersonalPickupPopup = (newTransport: TransportWithAvailablePaymentsAndStoresFragmentApi) => {
        if (newTransport.transportType.code === 'packetery') {
            openPacketeryPopup(newTransport);

            return;
        }

        clearPacketeryPickupPoint();
        setPreselectedTransport(newTransport);
    };

    const changePickupPlace = (selectedPickupPlace: ListedStoreFragmentApi | null) => {
        if (selectedPickupPlace && preSelectedTransport) {
            changeTransportHandler(preSelectedTransport.uuid, selectedPickupPlace);
        } else {
            changeTransport(null);
            clearPacketeryPickupPoint();
        }

        setPreselectedTransport(null);
    };

    const closePickupPlacePopup = () => {
        clearPacketeryPickupPoint();
        setPreselectedTransport(null);
    };

    return {
        preSelectedTransport,
        changeTransport,
        changePickupPlace,
        closePickupPlacePopup,
        resetTransportAndPayment,
    };
};

export const getLastOrderPickupPlace = (
    lastOrder: LastOrderFragmentApi,
    lastOrderPickupPlaceIdentifier: string,
    lastOrderPickupPlaceFromApi: ListedStoreFragmentApi | undefined | null,
    packeteryPickupPoint: ListedStoreFragmentApi | null,
): ListedStoreFragmentApi | null => {
    if (packeteryPickupPoint?.identifier === lastOrderPickupPlaceIdentifier) {
        return packeteryPickupPoint;
    }

    if (lastOrderPickupPlaceFromApi?.identifier) {
        return getGtmPickupPlaceFromStore(lastOrderPickupPlaceFromApi);
    }

    return getGtmPickupPlaceFromLastOrder(lastOrderPickupPlaceIdentifier, lastOrder);
};

type TransportAndPaymentErrorsType = {
    transport: {
        name: 'transport';
        label: string;
        errorMessage: string | undefined;
    };
    payment: {
        name: 'payment';
        label: string;
        errorMessage: string | undefined;
    };
    goPaySwift: {
        name: 'goPaySwift';
        label: string;
        errorMessage: string | undefined;
    };
};

export const getTransportAndPaymentValidationMessages = (
    transport: Maybe<TransportWithAvailablePaymentsAndStoresFragmentApi>,
    pickupPlace: Maybe<ListedStoreFragmentApi>,
    payment: Maybe<SimplePaymentFragmentApi>,
    paymentGoPayBankSwift: Maybe<string>,
    t: Translate,
) => {
    const errors: Partial<TransportAndPaymentErrorsType> = {};

    if (!transport) {
        errors.transport = {
            name: 'transport',
            label: t('Choose transport'),
            errorMessage: t('Please select transport'),
        };

        return errors;
    }

    if (transport.isPersonalPickup && !pickupPlace?.identifier) {
        errors.transport = {
            name: 'transport',
            label: t('Choose transport'),
            errorMessage: t('Please select transport with a personal pickup place'),
        };
    }
    if (!payment) {
        errors.payment = {
            name: 'payment',
            label: t('Choose payment'),
            errorMessage: t('Please select payment'),
        };
    }
    if (payment?.goPayPaymentMethod?.identifier === 'BANK_ACCOUNT' && !paymentGoPayBankSwift) {
        errors.goPaySwift = {
            name: 'goPaySwift',
            label: t('Choose your bank'),
            errorMessage: t('Please select your bank'),
        };
    }

    return errors;
};

export const getPickupPlaceDetail = (
    selectedTransport: Maybe<TransportWithAvailablePaymentsAndStoresFragmentApi>,
    selectedPickupPlace: ListedStoreFragmentApi | null,
    transportItem: TransportWithAvailablePaymentsAndStoresFragmentApi,
) =>
    selectedTransport?.uuid === transportItem.uuid &&
    transportItem.stores?.edges?.some((storeEdge) => storeEdge?.node?.identifier === selectedPickupPlace?.identifier)
        ? selectedPickupPlace!
        : undefined;

export const useLoadTransportAndPaymentFromLastOrder = (
    changeTransportInCart: ChangeTransportHandler,
    changePaymentInCart: ChangePaymentHandler,
): [boolean, ListedStoreFragmentApi | null] => {
    const client = useClient();
    const isUserLoggedIn = !!useCurrentCustomerData();
    const { transport: currentTransport, payment: currentPayment } = useCurrentCart();

    const [lastOrderPickupPlace, setLastOrderPickupPlace] = useState<ListedStoreFragmentApi | null>(null);
    const [isLoadingTransportAndPaymentFromLastOrder, setIsLoadingTransportAndPaymentFromLastOrder] = useState(false);

    const packeteryPickupPoint = usePersistStore((store) => store.packeteryPickupPoint);

    const loadLastOrderPickupPlace = async (lastOrder: LastOrderQueryApi | undefined) => {
        if (!lastOrder?.lastOrder?.pickupPlaceIdentifier) {
            return null;
        }

        let lastOrderPickupPlaceDataFromApi;
        if (lastOrder.lastOrder.transport.transportType.code !== 'packetery') {
            lastOrderPickupPlaceDataFromApi = (
                await client
                    .query<StoreQueryApi, StoreQueryVariablesApi>(StoreQueryDocumentApi, {
                        uuid: lastOrder.lastOrder.pickupPlaceIdentifier,
                    })
                    .toPromise()
            ).data?.store;
        }

        return getLastOrderPickupPlace(
            lastOrder.lastOrder,
            lastOrder.lastOrder.pickupPlaceIdentifier,
            lastOrderPickupPlaceDataFromApi,
            packeteryPickupPoint,
        );
    };

    const loadTransportAndPaymentFromLastOrder = async () => {
        setIsLoadingTransportAndPaymentFromLastOrder(true);

        if (currentTransport || currentPayment) {
            setIsLoadingTransportAndPaymentFromLastOrder(false);

            return;
        }

        const { data: lastOrderData } = await client
            .query<
                LastOrderQueryApi,
                LastOrderQueryVariablesApi
            >(LastOrderQueryDocumentApi, {}, { requestPolicy: 'network-only' })
            .toPromise();

        const lastOrderPickupPlace = await loadLastOrderPickupPlace(lastOrderData);

        const newCart = await changeTransportInCart(
            lastOrderData?.lastOrder?.transport.uuid ?? null,
            lastOrderPickupPlace,
        );
        const successfullyChangedTransport = newCart?.transport?.uuid === lastOrderData?.lastOrder?.transport.uuid;
        const successfullyChangedPickupPlace =
            !!newCart?.selectedPickupPlaceIdentifier &&
            newCart.selectedPickupPlaceIdentifier === lastOrderPickupPlace?.identifier;

        if (successfullyChangedTransport) {
            if (successfullyChangedPickupPlace) {
                setLastOrderPickupPlace(lastOrderPickupPlace);
            }

            await changePaymentInCart(lastOrderData?.lastOrder?.payment.uuid ?? null, null);
        }

        setIsLoadingTransportAndPaymentFromLastOrder(false);
    };

    useEffect(() => {
        if (!isUserLoggedIn) {
            return;
        }

        loadTransportAndPaymentFromLastOrder();
    }, []);

    return [isLoadingTransportAndPaymentFromLastOrder, lastOrderPickupPlace];
};
