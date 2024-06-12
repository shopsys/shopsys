import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeLastOrderFragment } from 'graphql/requests/orders/fragments/LastOrderFragment.generated';
import {
    TypeLastOrderQuery,
    TypeLastOrderQueryVariables,
    LastOrderQueryDocument,
} from 'graphql/requests/orders/queries/LastOrderQuery.generated';
import { TypeSimplePaymentFragment } from 'graphql/requests/payments/fragments/SimplePaymentFragment.generated';
import { TypeListedStoreFragment } from 'graphql/requests/stores/fragments/ListedStoreFragment.generated';
import {
    TypeStoreQuery,
    TypeStoreQueryVariables,
    StoreQueryDocument,
} from 'graphql/requests/stores/queries/StoreQuery.generated';
import { TypeTransportWithAvailablePaymentsAndStoresFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsAndStoresFragment.generated';
import { Maybe } from 'graphql/types';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { getGtmPickupPlaceFromLastOrder } from 'gtm/mappers/getGtmPickupPlaceFromLastOrder';
import { getGtmPickupPlaceFromStore } from 'gtm/mappers/getGtmPickupPlaceFromStore';
import { Translate } from 'next-translate';
import getConfig from 'next/config';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';
import { useClient } from 'urql';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { ChangePaymentInCart } from 'utils/cart/useChangePaymentInCart';
import { ChangeTransportInCart } from 'utils/cart/useChangeTransportInCart';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { hasValidationErrors } from 'utils/errors/hasValidationErrors';
import { logException } from 'utils/errors/logException';
import { mapPacketeryExtendedPoint, packeteryPick } from 'utils/packetery';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const PickupPlacePopup = dynamic(
    () => import('components/Blocks/Popup/PickupPlacePopup').then((component) => component.PickupPlacePopup),
    {
        ssr: false,
    },
);

const ErrorPopup = dynamic(
    () => import('components/Blocks/Popup/ErrorPopup').then((component) => component.ErrorPopup),
    {
        ssr: false,
    },
);

const { publicRuntimeConfig } = getConfig();

export const usePaymentChangeInSelect = (changePaymentHandler: ChangePaymentInCart) => {
    const { payment: currentPayment, paymentGoPayBankSwift: currentPaymentGoPayBankSwift } = useCurrentCart();

    const changePayment = (updatedPaymentUuid: string | null) =>
        changePaymentHandler(updatedPaymentUuid, currentPaymentGoPayBankSwift);

    const changeGoPaySwift = (newGoPaySwiftValue: string | null) =>
        changePaymentHandler(currentPayment?.uuid ?? null, newGoPaySwiftValue);

    const resetPaymentAndGoPayBankSwift = () => changePaymentHandler(null, null);

    return { changePayment, changeGoPaySwift, resetPaymentAndGoPayBankSwift };
};

export const useTransportChangeInSelect = (
    transports: TypeTransportWithAvailablePaymentsAndStoresFragment[] | undefined,
    lastOrderPickupPlace: TypeListedStoreFragment | null,
    changeTransportHandler: ChangeTransportInCart,
    changePaymentHandler: ChangePaymentInCart,
) => {
    const { defaultLocale } = useDomainConfig();
    const [preSelectedPickupPlace, setPreSelectedPickupPlace] = useState(lastOrderPickupPlace);
    const clearPacketeryPickupPoint = usePersistStore((store) => store.clearPacketeryPickupPoint);
    const setPacketeryPickupPoint = usePersistStore((store) => store.setPacketeryPickupPoint);
    const { transport: currentTransport, pickupPlace: currentPickupPlace } = useCurrentCart();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

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

    const openPacketeryPopup = (newTransport: TypeTransportWithAvailablePaymentsAndStoresFragment) => {
        if (!currentPickupPlace) {
            const packeteryApiKey = publicRuntimeConfig.packeteryApiKey;

            if (!packeteryApiKey?.length) {
                logException('Packeta API key was not set');
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

    const openPersonalPickupPopup = (newTransport: TypeTransportWithAvailablePaymentsAndStoresFragment) => {
        if (newTransport.transportType.code === 'packetery') {
            openPacketeryPopup(newTransport);

            return;
        }

        clearPacketeryPickupPoint();
        updatePortalContent(
            <PickupPlacePopup transport={newTransport} onChangePickupPlaceCallback={changePickupPlace} />,
        );
    };

    const changePickupPlace = (
        transport: TypeTransportWithAvailablePaymentsAndStoresFragment,
        selectedPickupPlace: TypeListedStoreFragment | null,
    ) => {
        if (selectedPickupPlace) {
            changeTransportHandler(transport.uuid, selectedPickupPlace);
        } else {
            changeTransport(null);
            clearPacketeryPickupPoint();
        }

        updatePortalContent(null);
    };

    return {
        changeTransport,
        resetTransportAndPayment,
    };
};

const getLastOrderPickupPlace = (
    lastOrder: TypeLastOrderFragment,
    lastOrderPickupPlaceIdentifier: string,
    lastOrderPickupPlaceFromApi: TypeListedStoreFragment | undefined | null,
    packeteryPickupPoint: TypeListedStoreFragment | null,
): TypeListedStoreFragment | null => {
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
    transport: Maybe<TypeTransportWithAvailablePaymentsAndStoresFragment>,
    pickupPlace: Maybe<TypeListedStoreFragment>,
    payment: Maybe<TypeSimplePaymentFragment>,
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
    if (getIsGoPayBankTransferPayment(payment) && !paymentGoPayBankSwift) {
        errors.goPaySwift = {
            name: 'goPaySwift',
            label: t('Choose your bank'),
            errorMessage: t('Please select your bank'),
        };
    }

    return errors;
};

export const getPickupPlaceDetail = (
    selectedTransport: Maybe<TypeTransportWithAvailablePaymentsAndStoresFragment>,
    selectedPickupPlace: TypeListedStoreFragment | null,
    transportItem: TypeTransportWithAvailablePaymentsAndStoresFragment,
) =>
    selectedTransport?.uuid === transportItem.uuid &&
    transportItem.stores?.edges?.some((storeEdge) => storeEdge?.node?.identifier === selectedPickupPlace?.identifier)
        ? selectedPickupPlace!
        : undefined;

export const useLoadTransportAndPaymentFromLastOrder = (
    changeTransportInCart: ChangeTransportInCart,
    changePaymentInCart: ChangePaymentInCart,
): [boolean, TypeListedStoreFragment | null] => {
    const client = useClient();
    const isUserLoggedIn = useIsUserLoggedIn();
    const { transport: currentTransport, payment: currentPayment, cart } = useCurrentCart();

    const [lastOrderPickupPlace, setLastOrderPickupPlace] = useState<TypeListedStoreFragment | null>(null);
    const [isLoadingTransportAndPaymentFromLastOrder, setIsLoadingTransportAndPaymentFromLastOrder] = useState(false);

    const packeteryPickupPoint = usePersistStore((store) => store.packeteryPickupPoint);

    const loadLastOrderPickupPlace = async (lastOrder: TypeLastOrderQuery | undefined) => {
        if (!lastOrder?.lastOrder?.pickupPlaceIdentifier) {
            return null;
        }

        let lastOrderPickupPlaceDataFromApi;
        if (lastOrder.lastOrder.transport.transportType.code !== 'packetery') {
            lastOrderPickupPlaceDataFromApi = (
                await client
                    .query<TypeStoreQuery, TypeStoreQueryVariables>(StoreQueryDocument, {
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
                TypeLastOrderQuery,
                TypeLastOrderQueryVariables
            >(LastOrderQueryDocument, {}, { requestPolicy: 'network-only' })
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
        if (!!cart && isUserLoggedIn) {
            loadTransportAndPaymentFromLastOrder();
        }
    }, [!cart]);

    return [isLoadingTransportAndPaymentFromLastOrder, lastOrderPickupPlace];
};

export const useTransportAndPaymentPageNavigation = (validationMessages: Partial<TransportAndPaymentErrorsType>) => {
    const { url } = useDomainConfig();
    const router = useRouter();
    const [cartUrl, contactInformationUrl] = getInternationalizedStaticUrls(
        ['/cart', '/order/contact-information'],
        url,
    );
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const goToPreviousStepFromTransportAndPaymentPage = () => {
        updatePageLoadingState({ isPageLoading: true, redirectPageType: 'cart' });
        router.push(cartUrl);
    };

    const goToNextStepFromTransportAndPaymentPage = () => {
        if (hasValidationErrors(validationMessages)) {
            updatePortalContent(
                <ErrorPopup
                    fields={validationMessages}
                    gtmMessageOrigin={GtmMessageOriginType.transport_and_payment_page}
                />,
            );

            return;
        }

        updatePageLoadingState({ isPageLoading: true, redirectPageType: 'contact-information' });
        router.push(contactInformationUrl);
    };

    return { goToPreviousStepFromTransportAndPaymentPage, goToNextStepFromTransportAndPaymentPage };
};

export const getIsGoPayBankTransferPayment = (payment: Maybe<TypeSimplePaymentFragment>) =>
    payment?.goPayPaymentMethod?.identifier === 'BANK_ACCOUNT';
