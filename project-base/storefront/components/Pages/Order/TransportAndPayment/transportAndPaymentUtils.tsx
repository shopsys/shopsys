import { getPublicConfigProperty } from 'envConfig';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import {
    LastOrderQueryDocument,
    TypeLastOrderQuery,
    TypeLastOrderQueryVariables,
} from 'graphql/requests/orders/queries/LastOrderQuery.generated';
import { TypeSimplePaymentFragment } from 'graphql/requests/payments/fragments/SimplePaymentFragment.generated';
import {
    StoreQueryDocument,
    TypeStoreQuery,
    TypeStoreQueryVariables,
} from 'graphql/requests/stores/queries/StoreQuery.generated';
import { TypeTransportWithAvailablePaymentsFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsFragment.generated';
import { Maybe, TypeTransportUnavailabilityReasonInCartEnum } from 'graphql/types';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { Translate } from 'next-translate';
import { useEffect, useEffectEvent, useRef, useState } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';
import { useClient } from 'urql';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import type { TransportAndPaymentErrorsType } from 'utils/cart/getTransportAndPaymentValidationMessages';
import { getLastOrderPickupPlace, PICKUP_POINT_NOT_SET_ERROR_MESSAGE } from 'utils/cart/pickupPlaceCalculations';
import { ChangePaymentInCart } from 'utils/cart/useChangePaymentInCart';
import { ChangeTransportInCart } from 'utils/cart/useChangeTransportInCart';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { hasValidationErrors } from 'utils/errors/hasValidationErrors';
import { logException } from 'utils/errors/logException';
import { createIntlDateTimeFormatter } from 'utils/formaters/createIntlDateTimeFormatter';
import { useDisplayTimezone } from 'utils/formatting/useDisplayTimezone';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getOrderPaymentItem, getOrderTransportItem } from 'utils/mappers/order';
import { mapPacketeryExtendedPoint, packeteryPick } from 'utils/packetery';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { isPacketeryTransport, isPersonalPickupTransport, isPickupPlaceTransport } from 'utils/transport';

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

const packeteryApiKey = getPublicConfigProperty('packeteryApiKey');

export const getTransportUnavailabilityHeading = (
    unavailabilityReason: TypeTransportUnavailabilityReasonInCartEnum,
    t: Translate,
): string => {
    switch (unavailabilityReason) {
        case TypeTransportUnavailabilityReasonInCartEnum.PersonalPickupRequired:
            return t('These products can only be picked up personally:');
        default:
            return t('These products cannot be delivered using this transport:');
    }
};

export const usePaymentChangeInSelect = (changePaymentHandler: ChangePaymentInCart) => {
    const { payment: currentPayment, paymentGoPayBankSwift: currentPaymentGoPayBankSwift } = useCurrentCart();

    const changePayment = (updatedPaymentUuid: string | null) =>
        changePaymentHandler(updatedPaymentUuid, currentPaymentGoPayBankSwift);

    const changeGoPaySwift = (newGoPaySwiftValue: string | null) =>
        changePaymentHandler(currentPayment?.uuid ?? null, newGoPaySwiftValue);

    const resetPaymentAndGoPayBankSwift = () => changePaymentHandler(null, null);

    return { changePayment, changeGoPaySwift, resetPaymentAndGoPayBankSwift };
};

type TransportGroupChoice = {
    group: NonNullable<TypeTransportWithAvailablePaymentsFragment['group']>;
    transports: TypeTransportWithAvailablePaymentsFragment[];
};

export const getTransportGroupChoices = (
    transports: TypeTransportWithAvailablePaymentsFragment[],
): TransportGroupChoice[] => {
    const transportGroupChoicesByUuid = new Map<string, TransportGroupChoice>();

    for (const transport of transports) {
        if (!transport.group) {
            continue;
        }

        const transportGroupChoice = transportGroupChoicesByUuid.get(transport.group.uuid);

        if (transportGroupChoice) {
            transportGroupChoice.transports.push(transport);
            continue;
        }

        transportGroupChoicesByUuid.set(transport.group.uuid, {
            group: transport.group,
            transports: [transport],
        });
    }

    return Array.from(transportGroupChoicesByUuid.values()).sort((firstChoice, secondChoice) => {
        if (firstChoice.group.position !== secondChoice.group.position) {
            return firstChoice.group.position - secondChoice.group.position;
        }

        return firstChoice.group.name.localeCompare(secondChoice.group.name);
    });
};

export const getShouldDisplayTransportGroups = (transportGroupChoices: TransportGroupChoice[]): boolean => {
    if (transportGroupChoices.length === 0) {
        return false;
    }

    return transportGroupChoices.some((transportGroupChoice) => transportGroupChoice.transports.length > 1);
};

export const getTransportsWithoutGroup = (
    transports: TypeTransportWithAvailablePaymentsFragment[],
): TypeTransportWithAvailablePaymentsFragment[] => transports.filter((transport) => !transport.group);

export const useTransportChangeInSelect = (
    transports: TypeTransportWithAvailablePaymentsFragment[] | undefined,
    lastOrderPickupPlace: StoreOrPacketeryPoint | null,
    changeTransportHandler: ChangeTransportInCart,
    changePaymentHandler: ChangePaymentInCart,
) => {
    const { defaultLocale, packeteryCountry } = useDomainConfig();
    const { t } = useTranslation();
    const isPacketeryScriptLoadingRef = useRef(false);
    const [preSelectedPickupPlace, setPreSelectedPickupPlace] = useState(lastOrderPickupPlace);
    const clearPacketeryPickupPoint = usePersistStore((store) => store.clearPacketeryPickupPoint);
    const setPacketeryPickupPoint = usePersistStore((store) => store.setPacketeryPickupPoint);
    const { transport: currentTransport } = useCurrentCart();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const closePortalContent = useSessionStore((s) => s.closePortalContent);

    const resetTransportAndPayment = async () => {
        await changeTransportHandler(null, null);
        await changePaymentHandler(null, null);
        setPreSelectedPickupPlace(null);
        clearPacketeryPickupPoint();
    };

    const openPickupPlacePopup = (updatedTransportUuid: string) => {
        const updatedTransport = transports?.find((transport) => transport.uuid === updatedTransportUuid);

        if (updatedTransport && isPacketeryTransport(updatedTransport.transportTypeCode)) {
            openPacketeryPopup(updatedTransport);
        }

        if (updatedTransport && isPersonalPickupTransport(updatedTransport.transportTypeCode)) {
            openPersonalPickupPopup(updatedTransport);
        }
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

        if (isPickupPlaceTransport(updatedTransport.transportTypeCode)) {
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

    const openPacketeryPopup = (newTransport: TypeTransportWithAvailablePaymentsFragment) => {
        if (!packeteryApiKey.length) {
            logException('Packeta API key was not set');
            return;
        }

        const pickWithPacketery = () => {
            packeteryPick(
                packeteryApiKey,
                (packeteryPoint) => {
                    if (packeteryPoint) {
                        const mappedPacketeryPoint = mapPacketeryExtendedPoint(packeteryPoint);
                        setPacketeryPickupPoint(mappedPacketeryPoint);
                        changeTransportHandler(newTransport.uuid, mappedPacketeryPoint);
                    }
                },
                { language: defaultLocale, country: packeteryCountry },
            );
        };

        if (window.Packeta?.Widget.pick !== undefined) {
            pickWithPacketery();
            return;
        }

        if (isPacketeryScriptLoadingRef.current) {
            return;
        }

        isPacketeryScriptLoadingRef.current = true;
        const script = document.createElement('script');
        script.src = 'https://widget.packeta.com/v6/www/js/library.js';
        script.async = true;
        document.body.appendChild(script);

        script.onload = () => {
            isPacketeryScriptLoadingRef.current = false;
            pickWithPacketery();
        };

        script.onerror = () => {
            isPacketeryScriptLoadingRef.current = false;
            showErrorMessage(t('Failed to load Packeta widget. Please try again later.'));
            logException('Packetery script failed to load');
        };
    };

    const openPersonalPickupPopup = (newTransport: TypeTransportWithAvailablePaymentsFragment) => {
        if (isPacketeryTransport(newTransport.transportTypeCode)) {
            openPacketeryPopup(newTransport);

            return;
        }

        clearPacketeryPickupPoint();
        updatePortalContent(
            <PickupPlacePopup
                lastOrderPickupPlace={lastOrderPickupPlace}
                transportUuid={newTransport.uuid}
                onChangePickupPlaceCallback={changePickupPlace}
            />,
        );
    };

    const changePickupPlace = (transportUuid: string, selectedPickupPlace: StoreOrPacketeryPoint | null) => {
        if (selectedPickupPlace) {
            changeTransportHandler(transportUuid, selectedPickupPlace);
        } else {
            changeTransport(null);
            clearPacketeryPickupPoint();
        }

        closePortalContent();
    };

    return {
        changeTransport,
        resetTransportAndPayment,
        openPickupPlacePopup,
    };
};

export const useLoadTransportAndPaymentFromLastOrder = (
    changeTransportInCart: ChangeTransportInCart,
    changePaymentInCart: ChangePaymentInCart,
): [boolean, StoreOrPacketeryPoint | null] => {
    const client = useClient();
    const isUserLoggedIn = useIsUserLoggedIn();
    const { transport: currentTransport, payment: currentPayment, cart } = useCurrentCart();

    const [lastOrderPickupPlace, setLastOrderPickupPlace] = useState<StoreOrPacketeryPoint | null>(null);
    const [isLoadingTransportAndPaymentFromLastOrder, setIsLoadingTransportAndPaymentFromLastOrder] = useState(false);
    const hasLoadedFromLastOrderRef = useRef(false);

    const packeteryPickupPoint = usePersistStore((store) => store.packeteryPickupPoint);

    const hasCart = !!cart;

    const onLoadFromLastOrder = useEffectEvent(async () => {
        const loadLastOrderPickupPlace = async (lastOrder: TypeLastOrderQuery | undefined) => {
            if (!lastOrder?.lastOrder?.pickupPlaceIdentifier) {
                return null;
            }

            const orderTransport = getOrderTransportItem(lastOrder.lastOrder.items);

            let lastOrderPickupPlaceDataFromApi: TypeStoreQuery['store'] | undefined;
            if (!isPacketeryTransport(orderTransport?.transport?.transportTypeCode)) {
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

        setIsLoadingTransportAndPaymentFromLastOrder(true);

        if (currentTransport || currentPayment) {
            setIsLoadingTransportAndPaymentFromLastOrder(false);

            return;
        }

        const { data: lastOrderData } = await client
            .query<TypeLastOrderQuery, TypeLastOrderQueryVariables>(
                LastOrderQueryDocument,
                {},
                { requestPolicy: 'network-only' },
            )
            .toPromise();

        const orderTransport = getOrderTransportItem(lastOrderData?.lastOrder?.items);
        const orderPayment = getOrderPaymentItem(lastOrderData?.lastOrder?.items);

        try {
            const lastOrderPickupPlace = await loadLastOrderPickupPlace(lastOrderData);

            const newCart = await changeTransportInCart(orderTransport?.transport?.uuid ?? null, lastOrderPickupPlace, {
                suppressValidationErrors: true,
            });
            const successfullyChangedTransport = newCart?.transport?.uuid === orderTransport?.transport?.uuid;
            const successfullyChangedPickupPlace =
                !!newCart?.selectedPickupPlaceIdentifier &&
                newCart.selectedPickupPlaceIdentifier === lastOrderPickupPlace?.identifier;

            if (successfullyChangedTransport) {
                if (successfullyChangedPickupPlace) {
                    setLastOrderPickupPlace(lastOrderPickupPlace);
                }

                await changePaymentInCart(orderPayment?.payment?.uuid ?? null, null);
            }
        } catch (e: unknown) {
            const error = e as Error;
            if (error.message && error.message !== PICKUP_POINT_NOT_SET_ERROR_MESSAGE) {
                throw Error;
            }
        } finally {
            setIsLoadingTransportAndPaymentFromLastOrder(false);
        }
    });

    useEffect(() => {
        if (hasCart && isUserLoggedIn && !hasLoadedFromLastOrderRef.current) {
            hasLoadedFromLastOrderRef.current = true;
            onLoadFromLastOrder();
        }
    }, [hasCart, isUserLoggedIn]);

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

export const useExpectedDeliveryDateMessage = (
    expectedDeliveryDate: string | null | undefined,
    isPersonalPickup = false,
): string | null => {
    const { t } = useTranslation();
    const { defaultLocale } = useDomainConfig();
    const timezone = useDisplayTimezone();

    if (!expectedDeliveryDate) {
        return null;
    }

    return getExpectedDeliveryDateMessage(
        expectedDeliveryDate,
        isPersonalPickup,
        new Date(),
        timezone,
        defaultLocale,
        t,
    );
};

export const getExpectedDeliveryDateMessage = (
    expectedDeliveryDate: string,
    isPersonalPickup: boolean,
    now: Date,
    timezone: string,
    locale: string,
    t: Translate,
): string => {
    const deliveryDay = getStartOfDayInTimezone(new Date(expectedDeliveryDate), timezone);
    const today = getStartOfDayInTimezone(now, timezone);
    const dayDifference = Math.round((deliveryDay.getTime() - today.getTime()) / (24 * 60 * 60 * 1000));
    const date = createIntlDateTimeFormatter({ day: 'numeric', month: 'numeric' }, timezone, locale).format(
        new Date(expectedDeliveryDate),
    );
    const todayIsoWeekday = today.getUTCDay() === 0 ? 7 : today.getUTCDay();
    const dayDifferenceOfMondayOfWeekAfterNext = 15 - todayIsoWeekday;

    // a date in the past (clock skew, a page left open over midnight) must not claim "today",
    // the plain date is the only message that cannot contradict itself
    if (dayDifference < 0) {
        return isPersonalPickup ? t('Personal pickup {{ date }}', { date }) : t('Delivery {{ date }}', { date });
    }

    if (isPersonalPickup) {
        if (dayDifference === 0) {
            return t('Personal pickup today {{ date }}', { date });
        }

        if (dayDifference === 1) {
            return t('Personal pickup tomorrow {{ date }}', { date });
        }

        if (dayDifference < dayDifferenceOfMondayOfWeekAfterNext) {
            return getPersonalPickupOnDayOfWeekMessage(deliveryDay.getUTCDay(), date, t);
        }

        return t('Personal pickup {{ date }}', { date });
    }

    if (dayDifference === 0) {
        return t('Delivery today {{ date }}', { date });
    }

    if (dayDifference === 1) {
        return t('Delivery tomorrow {{ date }}', { date });
    }

    if (dayDifference < dayDifferenceOfMondayOfWeekAfterNext) {
        return getDeliveryOnDayOfWeekMessage(deliveryDay.getUTCDay(), date, t);
    }

    return t('Delivery {{ date }}', { date });
};

const getDeliveryOnDayOfWeekMessage = (dayOfWeek: number, date: string, t: Translate): string => {
    switch (dayOfWeek) {
        case 1:
            return t('Delivery on Monday {{ date }}', { date });
        case 2:
            return t('Delivery on Tuesday {{ date }}', { date });
        case 3:
            return t('Delivery on Wednesday {{ date }}', { date });
        case 4:
            return t('Delivery on Thursday {{ date }}', { date });
        case 5:
            return t('Delivery on Friday {{ date }}', { date });
        case 6:
            return t('Delivery on Saturday {{ date }}', { date });
        default:
            return t('Delivery on Sunday {{ date }}', { date });
    }
};

const getPersonalPickupOnDayOfWeekMessage = (dayOfWeek: number, date: string, t: Translate): string => {
    switch (dayOfWeek) {
        case 1:
            return t('Personal pickup on Monday {{ date }}', { date });
        case 2:
            return t('Personal pickup on Tuesday {{ date }}', { date });
        case 3:
            return t('Personal pickup on Wednesday {{ date }}', { date });
        case 4:
            return t('Personal pickup on Thursday {{ date }}', { date });
        case 5:
            return t('Personal pickup on Friday {{ date }}', { date });
        case 6:
            return t('Personal pickup on Saturday {{ date }}', { date });
        default:
            return t('Personal pickup on Sunday {{ date }}', { date });
    }
};

const getStartOfDayInTimezone = (date: Date, timezone: string): Date => {
    // en-CA formats the date as YYYY-MM-DD
    const dateString = createIntlDateTimeFormatter(
        { year: 'numeric', month: '2-digit', day: '2-digit' },
        timezone,
        'en-CA',
    ).format(date);

    return new Date(`${dateString}T00:00:00Z`);
};
