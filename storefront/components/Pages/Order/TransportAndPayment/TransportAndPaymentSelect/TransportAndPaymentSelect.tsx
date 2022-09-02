import { PickupPlacePopup } from './PickupPlacePopup/PickupPlacePopup';
import { ListItemStyled, PaymentListWrapper, ResetButtonStyled } from './TransportAndPaymentSelect.style';
import { TransportAndPaymentSelectItemLabel } from './TransportAndPaymentSelectItemLabel/TransportAndPaymentSelectItemLabel';
import Heading from 'components/Basic/Heading';
import Icon from 'components/Basic/Icon';
import Radiobutton from 'components/Forms/Radiobutton';
import PacketeryContainer from 'components/Pages/Order/TransportAndPayment/PacketeryContainer';
import { useCurrentCart } from 'connectors/cart/Cart';
import { useGoPaySwiftsQueryApi } from 'graphql/generated';
import { logException } from 'helpers/errors/logException';
import { mapPacketeryExtendedPoint, packeteryPick, removePacketeryCookie, setPacketeryCookie } from 'helpers/packetery';
import { PacketeryExtendedPoint } from 'helpers/packetery/types';
import { useChangePaymentInCart } from 'hooks/cart/UseChangePaymentInCart';
import { useChangeTransportInCart } from 'hooks/cart/UseChangeTransportInCart';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useEffectOnce } from 'hooks/ui/useEffectOnce';
import getConfig from 'next/config';
import { FC, useCallback, useState } from 'react';
import { useShopsysSelector } from 'redux/main';
import { PaymentType } from 'types/payment';
import { PickupPlaceType } from 'types/pickupPlace';
import { TransportType } from 'types/transport';

const { publicRuntimeConfig } = getConfig();

type TransportAndPaymentSelectProps = {
    transports: TransportType[];
    lastOrderPickupPlace: PickupPlaceType | null;
    lastOrderTransportUuid: string | null;
    lastOrderPaymentUuid: string | null;
};

const TEST_IDENTIFIER = 'pages-order-';

const TransportAndPaymentSelect: FC<TransportAndPaymentSelectProps> = ({
    transports,
    lastOrderPickupPlace,
    lastOrderTransportUuid,
    lastOrderPaymentUuid,
}) => {
    const t = useTypedTranslationFunction();
    const { defaultLocale, currencyCode } = useShopsysSelector((state) => state.domain);
    const [preSelectedTransport, setPreselectedTransport] = useState<TransportType | null>(null);
    const [preSelectedPickupPlace, setPreSelectedPickupPlace] = useState<PickupPlaceType | null>(lastOrderPickupPlace);
    const { transport, pickupPlace, payment, paymentGoPayBankSwift } = useCurrentCart();

    const changeTransportInCart = useChangeTransportInCart();
    const changePaymentInCart = useChangePaymentInCart();
    const [getGoPaySwiftsResult] = useGoPaySwiftsQueryApi({ variables: { currencyCode } });

    const isPickupPlaceSelected = pickupPlace !== null;

    const onSelectPacketeryPickupPlaceCallback = useCallback(
        (packeteryPoint: PacketeryExtendedPoint | null, packeteryTransport: TransportType) => {
            if (packeteryPoint !== null) {
                const mappedPacketeryPoint = mapPacketeryExtendedPoint(packeteryPoint);
                setPacketeryCookie(mappedPacketeryPoint);
                changeTransportInCart(packeteryTransport.uuid, mappedPacketeryPoint);
            }
        },
        [changeTransportInCart],
    );

    const openPacketeryPopup = useCallback(
        (newTransport: TransportType) => {
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
        (newTransport: TransportType) => {
            if (newTransport.transportType.code === 'packetery') {
                openPacketeryPopup(newTransport);

                return;
            }

            removePacketeryCookie();
            setPreselectedTransport(newTransport);
        },
        [openPacketeryPopup],
    );

    const handleTransportChange = useCallback(
        (newTransportUuid: string | null) => {
            const potentialNewTransport = transports.find((transport) => transport.uuid === newTransportUuid);
            if (potentialNewTransport?.uuid === transport?.uuid) {
                return;
            }

            if (potentialNewTransport === undefined) {
                changeTransportInCart(null, null);

                return;
            }

            if (potentialNewTransport.isPersonalPickup || potentialNewTransport.transportType.code === 'packetery') {
                if (preSelectedPickupPlace === null) {
                    openPersonalPickupPopup(potentialNewTransport);

                    return;
                }

                changeTransportInCart(newTransportUuid, preSelectedPickupPlace);
                setPreSelectedPickupPlace(null);

                return;
            }

            if (newTransportUuid !== transport?.uuid) {
                changeTransportInCart(newTransportUuid, null);
            }
        },
        [changeTransportInCart, transports, openPersonalPickupPopup, transport?.uuid, preSelectedPickupPlace],
    );

    const handlePaymentChange = useCallback(
        (newPaymentUuid: string | null) => {
            changePaymentInCart(newPaymentUuid, paymentGoPayBankSwift);
        },
        [paymentGoPayBankSwift, changePaymentInCart],
    );

    const handleGoPaySwiftChange = useCallback(
        (newGoPaySwiftValue: string | null) => {
            changePaymentInCart(payment?.uuid ?? null, newGoPaySwiftValue);
        },
        [changePaymentInCart, payment],
    );

    useEffectOnce(() => {
        if (transport === null) {
            handleTransportChange(lastOrderTransportUuid);
        }
    });

    useEffectOnce(() => {
        if (payment === null) {
            handlePaymentChange(lastOrderPaymentUuid);
        }
    });

    const resetPaymentAndGoPayBankSwift = () => {
        changePaymentInCart(null, null);
    };

    const resetAll = () => {
        handleTransportChange(null);
        handlePaymentChange(null);
        removePacketeryCookie();
    };

    const onChangePickupPlaceHandler = (selectedPickupPlace: PickupPlaceType | null) => {
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

    const getPickupPlaceDetail = (transportItem: TransportType) => {
        return transport?.uuid === transportItem.uuid &&
            transportItem.stores.some((store) => store.identifier === pickupPlace?.identifier)
            ? pickupPlace
            : null;
    };

    const renderTransportListItem = (transportItem: TransportType, isActive: boolean) => {
        return (
            <ListItemStyled
                key={transportItem.uuid}
                isActive={isActive}
                data-testid={TEST_IDENTIFIER + 'transport-item' + (isActive ? '-active' : '')}
            >
                <Radiobutton
                    name="transport"
                    id={transportItem.uuid}
                    value={transportItem.uuid}
                    checked={isActive}
                    image={transportItem.image}
                    onChangeCallback={handleTransportChange}
                    data-testid={TEST_IDENTIFIER + 'transport-item-input'}
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
            </ListItemStyled>
        );
    };

    const renderPaymentListItem = (paymentItem: PaymentType, isActive: boolean) => {
        return (
            <ListItemStyled
                key={paymentItem.uuid}
                isActive={isActive}
                data-testid={TEST_IDENTIFIER + 'payment-item' + (isActive ? '-active' : '')}
            >
                <Radiobutton
                    name="payment"
                    id={paymentItem.uuid}
                    value={paymentItem.uuid}
                    checked={isActive}
                    image={paymentItem.image}
                    onChangeCallback={handlePaymentChange}
                    data-testid={TEST_IDENTIFIER + 'payment-item-input'}
                    label={
                        <TransportAndPaymentSelectItemLabel
                            name={paymentItem.name}
                            price={paymentItem.price}
                            description={paymentItem.description}
                        />
                    }
                />
            </ListItemStyled>
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
                        <ResetButtonStyled
                            type="button"
                            onClick={resetAll}
                            data-testid={TEST_IDENTIFIER + 'reset-transport'}
                        >
                            {t('Change transport type')}
                            <Icon iconType="icon" icon="Arrow" />
                        </ResetButtonStyled>
                    )}
                    {preSelectedTransport !== null && (
                        <PickupPlacePopup
                            isVisible={true}
                            transport={preSelectedTransport}
                            onChangePickupPlaceCallback={onChangePickupPlaceHandler}
                            onClosePickupPlacePopupCallback={onClosePickupPlacePopupHandler}
                        />
                    )}
                </div>
                {transport !== null && preSelectedTransport === null && (
                    <PaymentListWrapper data-testid={TEST_IDENTIFIER + 'payment'}>
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
                            <ResetButtonStyled
                                type="button"
                                onClick={resetPaymentAndGoPayBankSwift}
                                data-testid={TEST_IDENTIFIER + 'reset-payment'}
                            >
                                {t('Change payment type')}
                                <Icon iconType="icon" icon="Arrow" />
                            </ResetButtonStyled>
                        )}
                    </PaymentListWrapper>
                )}
            </div>
        </>
    );
};

export default TransportAndPaymentSelect;
