import { PickupPlacePopup } from './PickupPlacePopup';
import { TransportAndPaymentListItem } from './TransportAndPaymentListItem';
import { TransportAndPaymentSelectItemLabel } from './TransportAndPaymentSelectItemLabel';
import { Heading } from 'components/Basic/Heading/Heading';
import { Icon } from 'components/Basic/Icon/Icon';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { Radiobutton } from 'components/Forms/Radiobutton/Radiobutton';
import { PacketeryContainer } from 'components/Pages/Order/TransportAndPayment/PacketeryContainer';
import { useCurrentCart } from 'connectors/cart/Cart';
import {
    ListedStoreFragmentApi,
    SimplePaymentFragmentApi,
    TransportWithAvailablePaymentsAndStoresFragmentApi,
    useGoPaySwiftsQueryApi,
} from 'graphql/generated';
import { logException } from 'helpers/errors/logException';
import { getFirstImageOrNull } from 'helpers/mappers/image';
import { mapPacketeryExtendedPoint, packeteryPick, removePacketeryCookie, setPacketeryCookie } from 'helpers/packetery';
import { PacketeryExtendedPoint } from 'helpers/packetery/types';
import { ChangePaymentHandler } from 'hooks/cart/useChangePaymentInCart';
import { ChangeTransportHandler } from 'hooks/cart/useChangeTransportInCart';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useEffectOnce } from 'hooks/ui/useEffectOnce';
import { useDomainConfig } from 'hooks/useDomainConfig';
import getConfig from 'next/config';
import { useCallback, useState } from 'react';

const { publicRuntimeConfig } = getConfig();

type TransportAndPaymentSelectProps = {
    transports: TransportWithAvailablePaymentsAndStoresFragmentApi[];
    lastOrderPickupPlace: ListedStoreFragmentApi | null;
    lastOrderTransportUuid: string | null;
    lastOrderPaymentUuid: string | null;
    changeTransportInCart: ChangeTransportHandler;
    changePaymentInCart: ChangePaymentHandler;
    isTransportSelectionLoading: boolean;
};

const TEST_IDENTIFIER = 'pages-order-';

export const TransportAndPaymentSelect: FC<TransportAndPaymentSelectProps> = ({
    transports,
    lastOrderPickupPlace,
    lastOrderTransportUuid,
    lastOrderPaymentUuid,
    changeTransportInCart,
    changePaymentInCart,
    isTransportSelectionLoading,
}) => {
    const t = useTypedTranslationFunction();
    const { defaultLocale, currencyCode } = useDomainConfig();
    const [preSelectedTransport, setPreselectedTransport] =
        useState<TransportWithAvailablePaymentsAndStoresFragmentApi | null>(null);
    const [preSelectedPickupPlace, setPreSelectedPickupPlace] = useState<ListedStoreFragmentApi | null>(
        lastOrderPickupPlace,
    );
    const { transport, pickupPlace, payment, paymentGoPayBankSwift } = useCurrentCart();
    const [getGoPaySwiftsResult] = useQueryError(useGoPaySwiftsQueryApi({ variables: { currencyCode } }));

    const isPickupPlaceSelected = pickupPlace !== null;

    const onSelectPacketeryPickupPlaceCallback = useCallback(
        (
            packeteryPoint: PacketeryExtendedPoint | null,
            packeteryTransport: TransportWithAvailablePaymentsAndStoresFragmentApi,
        ) => {
            if (packeteryPoint !== null) {
                const mappedPacketeryPoint = mapPacketeryExtendedPoint(packeteryPoint);
                setPacketeryCookie(mappedPacketeryPoint);
                changeTransportInCart(packeteryTransport.uuid, mappedPacketeryPoint);
            }
        },
        [changeTransportInCart],
    );

    const openPacketeryPopup = useCallback(
        (newTransport: TransportWithAvailablePaymentsAndStoresFragmentApi) => {
            if (!isPickupPlaceSelected) {
                const packeteryApiKey = publicRuntimeConfig.packeteryApiKey;
                if (packeteryApiKey === undefined || packeteryApiKey.length === 0) {
                    logException(new Error(`Packeta API key was not set`));
                    return;
                }

                packeteryPick(
                    packeteryApiKey,
                    (point) => {
                        onSelectPacketeryPickupPlaceCallback(point, newTransport);
                    },
                    { language: defaultLocale },
                );
            }
        },
        [defaultLocale, isPickupPlaceSelected, onSelectPacketeryPickupPlaceCallback],
    );

    const openPersonalPickupPopup = useCallback(
        (newTransport: TransportWithAvailablePaymentsAndStoresFragmentApi) => {
            if (newTransport.transportType.code === 'packetery') {
                openPacketeryPopup(newTransport);

                return;
            }

            removePacketeryCookie();
            setPreselectedTransport(newTransport);
        },
        [openPacketeryPopup],
    );

    const handlePaymentChange = useCallback(
        async (newPaymentUuid: string | null) => {
            await changePaymentInCart(newPaymentUuid, paymentGoPayBankSwift);
        },
        [paymentGoPayBankSwift, changePaymentInCart],
    );

    const handleTransportChange = useCallback(
        async (newTransportUuid: string | null) => {
            const potentialNewTransport = transports.find((transport) => transport.uuid === newTransportUuid);
            if (potentialNewTransport?.uuid === transport?.uuid) {
                return;
            }

            if (potentialNewTransport === undefined) {
                await changeTransportInCart(null, null);
                await handlePaymentChange(null);

                return;
            }

            if (potentialNewTransport.isPersonalPickup || potentialNewTransport.transportType.code === 'packetery') {
                if (preSelectedPickupPlace === null) {
                    openPersonalPickupPopup(potentialNewTransport);

                    return;
                }

                await changeTransportInCart(newTransportUuid, preSelectedPickupPlace);
                setPreSelectedPickupPlace(null);

                return;
            }

            if (newTransportUuid !== transport?.uuid) {
                await changeTransportInCart(newTransportUuid, null);
            }
        },
        [
            changeTransportInCart,
            transports,
            openPersonalPickupPopup,
            transport?.uuid,
            preSelectedPickupPlace,
            handlePaymentChange,
        ],
    );

    const handleGoPaySwiftChange = useCallback(
        async (newGoPaySwiftValue: string | null) => {
            await changePaymentInCart(payment?.uuid ?? null, newGoPaySwiftValue);
        },
        [changePaymentInCart, payment],
    );

    const loadPresetsFromLastOrder = useCallback(async () => {
        if (transport === null) {
            await handleTransportChange(lastOrderTransportUuid);
        }
        if (payment === null) {
            await handlePaymentChange(lastOrderPaymentUuid);
        }
    }, [handlePaymentChange, handleTransportChange, lastOrderPaymentUuid, lastOrderTransportUuid, payment, transport]);

    useEffectOnce(() => {
        loadPresetsFromLastOrder();
    });

    const resetPaymentAndGoPayBankSwift = () => {
        changePaymentInCart(null, null);
    };

    const resetAll = async () => {
        await handleTransportChange(null);
        await handlePaymentChange(null);
        removePacketeryCookie();
    };

    const onChangePickupPlaceHandler = (selectedPickupPlace: ListedStoreFragmentApi | null) => {
        if (selectedPickupPlace !== null && preSelectedTransport !== null) {
            changeTransportInCart(preSelectedTransport.uuid, selectedPickupPlace);
        } else {
            handleTransportChange(null);
            removePacketeryCookie();
        }

        setPreselectedTransport(null);
    };

    const onClosePickupPlacePopupHandler = () => {
        removePacketeryCookie();
        setPreselectedTransport(null);
    };

    const getPickupPlaceDetail = (transportItem: TransportWithAvailablePaymentsAndStoresFragmentApi) => {
        return transport?.uuid === transportItem.uuid &&
            transportItem.stores?.edges?.some((storeEdge) => storeEdge?.node?.identifier === pickupPlace?.identifier)
            ? pickupPlace
            : null;
    };

    const renderTransportListItem = (
        transportItem: TransportWithAvailablePaymentsAndStoresFragmentApi,
        isActive: boolean,
    ) => {
        return (
            <TransportAndPaymentListItem
                key={transportItem.uuid}
                isActive={isActive}
                dataTestId={TEST_IDENTIFIER + 'transport-item' + (isActive ? '-active' : '')}
            >
                <Radiobutton
                    name="transport"
                    id={transportItem.uuid}
                    value={transportItem.uuid}
                    checked={isActive}
                    dataTestId={TEST_IDENTIFIER + 'transport-item-input'}
                    image={getFirstImageOrNull(transportItem.images)}
                    onChangeCallback={handleTransportChange}
                    label={
                        <TransportAndPaymentSelectItemLabel
                            name={transportItem.name}
                            daysUntilDelivery={transportItem.daysUntilDelivery}
                            price={transportItem.price}
                            description={transportItem.description}
                            pickupPlaceDetail={getPickupPlaceDetail(transportItem)}
                        />
                    }
                />
            </TransportAndPaymentListItem>
        );
    };

    const renderPaymentListItem = (paymentItem: SimplePaymentFragmentApi, isActive: boolean) => {
        return (
            <TransportAndPaymentListItem
                key={paymentItem.uuid}
                isActive={isActive}
                dataTestId={TEST_IDENTIFIER + 'payment-item' + (isActive ? '-active' : '')}
            >
                <Radiobutton
                    name="payment"
                    id={paymentItem.uuid}
                    value={paymentItem.uuid}
                    checked={isActive}
                    dataTestId={TEST_IDENTIFIER + 'payment-item-input'}
                    image={getFirstImageOrNull(paymentItem.images)}
                    onChangeCallback={handlePaymentChange}
                    label={
                        <TransportAndPaymentSelectItemLabel
                            name={paymentItem.name}
                            price={paymentItem.price}
                            description={paymentItem.description}
                        />
                    }
                />
            </TransportAndPaymentListItem>
        );
    };

    return (
        <>
            <PacketeryContainer />
            <div data-testid={TEST_IDENTIFIER + 'transport-and-payment'}>
                <div data-testid={TEST_IDENTIFIER + 'transport'}>
                    <Heading type="h3">{t('Choose transport')}</Heading>
                    <ul>
                        {transport !== null
                            ? renderTransportListItem(transport, true)
                            : transports.map((transportItem) => renderTransportListItem(transportItem, false))}
                    </ul>
                    {transport !== null && (
                        <ResetButton
                            onClick={resetAll}
                            dataTestId={TEST_IDENTIFIER + 'reset-transport'}
                            text={t('Change transport type')}
                        />
                    )}
                    {preSelectedTransport !== null && (
                        <PickupPlacePopup
                            isVisible
                            transport={preSelectedTransport}
                            onChangePickupPlaceCallback={onChangePickupPlaceHandler}
                            onClosePickupPlacePopupCallback={onClosePickupPlacePopupHandler}
                        />
                    )}
                </div>
                {transport !== null && preSelectedTransport === null && (
                    <div className="relative mt-12" data-testid={TEST_IDENTIFIER + 'payment'}>
                        {isTransportSelectionLoading && <LoaderWithOverlay className="w-8" />}
                        <Heading type="h3">{t('Choose payment')}</Heading>
                        <ul>
                            {payment !== null
                                ? renderPaymentListItem(payment, true)
                                : transport.payments.map((paymentItem) => renderPaymentListItem(paymentItem, false))}
                        </ul>
                        {payment?.type === 'goPay' && payment.goPayPaymentMethod?.identifier === 'BANK_ACCOUNT' && (
                            <>
                                <Heading type="h3">{t('Choose your bank')}</Heading>
                                {getGoPaySwiftsResult.data?.GoPaySwifts.map((goPaySwift) => (
                                    <Radiobutton
                                        key={goPaySwift.swift}
                                        name="goPaySwift"
                                        id={goPaySwift.swift}
                                        value={goPaySwift.swift}
                                        onChangeCallback={handleGoPaySwiftChange}
                                        checked={paymentGoPayBankSwift === goPaySwift.swift}
                                        label={goPaySwift.name}
                                    />
                                ))}
                            </>
                        )}
                        {payment !== null && (
                            <ResetButton
                                onClick={resetPaymentAndGoPayBankSwift}
                                dataTestId={TEST_IDENTIFIER + 'reset-payment'}
                                text={t('Change payment type')}
                            />
                        )}
                    </div>
                )}
            </div>
        </>
    );
};

type ResetButtonProps = { text: string; onClick: () => void };

const ResetButton: FC<ResetButtonProps> = ({ text, dataTestId, onClick }) => (
    <button
        onClick={onClick}
        data-testid={dataTestId}
        className="flex w-full items-center bg-whitesmoke px-2 py-1 text-sm"
    >
        {text}
        <Icon iconType="icon" icon="Arrow" className="ml-2" />
    </button>
);
