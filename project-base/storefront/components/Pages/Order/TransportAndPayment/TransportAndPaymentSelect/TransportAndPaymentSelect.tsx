import { PickupPlacePopup } from './PickupPlacePopup';
import { TransportAndPaymentListItem } from './TransportAndPaymentListItem';
import { TransportAndPaymentSelectItemLabel } from './TransportAndPaymentSelectItemLabel';
import { Heading } from 'components/Basic/Heading/Heading';
import { Arrow } from 'components/Basic/Icon/IconsSvg';
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
import { mapPacketeryExtendedPoint, packeteryPick } from 'helpers/packetery';
import { PacketeryExtendedPoint } from 'helpers/packetery/types';
import { ChangePaymentHandler } from 'hooks/cart/useChangePaymentInCart';
import { ChangeTransportHandler } from 'hooks/cart/useChangeTransportInCart';
import useTranslation from 'next-translate/useTranslation';
import { useDomainConfig } from 'hooks/useDomainConfig';
import getConfig from 'next/config';
import { useEffect, useState } from 'react';
import { usePersistStore } from 'store/usePersistStore';

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
    const { t } = useTranslation();
    const { defaultLocale, currencyCode } = useDomainConfig();
    const [preSelectedTransport, setPreselectedTransport] =
        useState<TransportWithAvailablePaymentsAndStoresFragmentApi | null>(null);
    const [preSelectedPickupPlace, setPreSelectedPickupPlace] = useState<ListedStoreFragmentApi | null>(
        lastOrderPickupPlace,
    );
    const { transport, pickupPlace, payment, paymentGoPayBankSwift } = useCurrentCart();
    const [getGoPaySwiftsResult] = useGoPaySwiftsQueryApi({ variables: { currencyCode } });
    const setPacketeryPickupPoint = usePersistStore((store) => store.setPacketeryPickupPoint);
    const clearPacketeryPickupPoint = usePersistStore((store) => store.clearPacketeryPickupPoint);

    const onSelectPacketeryPickupPlaceCallback = (
        packeteryPoint: PacketeryExtendedPoint | null,
        packeteryTransport: TransportWithAvailablePaymentsAndStoresFragmentApi,
    ) => {
        if (packeteryPoint !== null) {
            const mappedPacketeryPoint = mapPacketeryExtendedPoint(packeteryPoint);
            setPacketeryPickupPoint(mappedPacketeryPoint);
            changeTransportInCart(packeteryTransport.uuid, mappedPacketeryPoint);
        }
    };

    const openPacketeryPopup = (newTransport: TransportWithAvailablePaymentsAndStoresFragmentApi) => {
        if (!pickupPlace) {
            const packeteryApiKey = publicRuntimeConfig.packeteryApiKey;

            if (!packeteryApiKey?.length) {
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
    };

    const openPersonalPickupPopup = (newTransport: TransportWithAvailablePaymentsAndStoresFragmentApi) => {
        if (newTransport.transportType.code === 'packetery') {
            openPacketeryPopup(newTransport);

            return;
        }

        clearPacketeryPickupPoint();
        setPreselectedTransport(newTransport);
    };

    const handlePaymentChange = async (newPaymentUuid: string | null) =>
        await changePaymentInCart(newPaymentUuid, paymentGoPayBankSwift);

    const handleTransportChange = async (newTransportUuid: string | null) => {
        const potentialNewTransport = transports.find((transport) => transport.uuid === newTransportUuid);

        if (potentialNewTransport?.uuid === transport?.uuid) {
            return;
        }

        if (!potentialNewTransport) {
            await changeTransportInCart(null, null);
            await handlePaymentChange(null);

            return;
        }

        if (potentialNewTransport.isPersonalPickup || potentialNewTransport.transportType.code === 'packetery') {
            if (!preSelectedPickupPlace) {
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
    };

    const handleGoPaySwiftChange = async (newGoPaySwiftValue: string | null) => {
        await changePaymentInCart(payment?.uuid ?? null, newGoPaySwiftValue);
    };

    const loadPresetsFromLastOrder = async () => {
        if (!transport) {
            await handleTransportChange(lastOrderTransportUuid);
        }
        if (!payment) {
            await handlePaymentChange(lastOrderPaymentUuid);
        }
    };

    useEffect(() => {
        loadPresetsFromLastOrder();
    }, []);

    const resetPaymentAndGoPayBankSwift = () => changePaymentInCart(null, null);

    const resetAll = async () => {
        await handleTransportChange(null);
        await handlePaymentChange(null);
        clearPacketeryPickupPoint();
    };

    const onChangePickupPlaceHandler = (selectedPickupPlace: ListedStoreFragmentApi | null) => {
        if (selectedPickupPlace && preSelectedTransport) {
            changeTransportInCart(preSelectedTransport.uuid, selectedPickupPlace);
        } else {
            handleTransportChange(null);
            clearPacketeryPickupPoint();
        }

        setPreselectedTransport(null);
    };

    const onClosePickupPlacePopupHandler = () => {
        clearPacketeryPickupPoint();
        setPreselectedTransport(null);
    };

    const getPickupPlaceDetail = (transportItem: TransportWithAvailablePaymentsAndStoresFragmentApi) =>
        transport?.uuid === transportItem.uuid &&
        transportItem.stores?.edges?.some((storeEdge) => storeEdge?.node?.identifier === pickupPlace?.identifier)
            ? pickupPlace
            : null;

    const renderTransportListItem = (
        transportItem: TransportWithAvailablePaymentsAndStoresFragmentApi,
        isActive: boolean,
    ) => (
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
                image={transportItem.mainImage}
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

    const renderPaymentListItem = (paymentItem: SimplePaymentFragmentApi, isActive: boolean) => (
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
                image={paymentItem.mainImage}
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

    return (
        <>
            <PacketeryContainer />
            <div data-testid={TEST_IDENTIFIER + 'transport-and-payment'}>
                <div data-testid={TEST_IDENTIFIER + 'transport'}>
                    <Heading type="h3">{t('Choose transport')}</Heading>
                    <ul>
                        {transport
                            ? renderTransportListItem(transport, true)
                            : transports.map((transportItem) => renderTransportListItem(transportItem, false))}
                    </ul>
                    {!!transport && (
                        <ResetButton
                            onClick={resetAll}
                            dataTestId={TEST_IDENTIFIER + 'reset-transport'}
                            text={t('Change transport type')}
                        />
                    )}
                    {!!preSelectedTransport && (
                        <PickupPlacePopup
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
                            {payment
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
        <Arrow className="ml-2" />
    </button>
);
