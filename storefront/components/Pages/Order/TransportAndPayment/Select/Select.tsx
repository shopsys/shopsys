import PickupPlacePopup from './PickupPlacePopup/PickupPlacePopup';
import { ListItemStyled, PaymentListWrapper, ResetButtonStyled } from './Select.style';
import SelectItemLabel from './SelectItemLabel';
import Heading from 'components/Basic/Heading';
import Icon from 'components/Basic/Icon';
import Radiobutton from 'components/Forms/Radiobutton';
import { useTransportAndPaymentFormMeta } from 'components/Pages/Order/TransportAndPayment/formMeta';
import PacketeryContainer from 'components/Pages/Order/TransportAndPayment/PacketeryContainer';
import { useCurrentCart } from 'connectors/cart/Cart';
import { useGoPaySwiftsQueryApi } from 'graphql/generated';
import { logException } from 'helpers/errors/logException';
import { mapPacketeryExtendedPoint, packeteryPick, removePacketeryCookie, setPacketeryCookie } from 'helpers/packetery';
import { PacketeryExtendedPoint } from 'helpers/packetery/types';
import { useChangePaymentInCart } from 'hooks/cart/UseChangePaymentInCart';
import { useChangeTransportInCart } from 'hooks/cart/UseChangeTransportInCart';
import { useComponentUpdate } from 'hooks/helpers/UseComponentUpdate';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useEffectOnce } from 'hooks/ui/useEffectOnce';
import getConfig from 'next/config';
import { FC, useCallback, useEffect, useState } from 'react';
import { Controller, ControllerRenderProps, useFormContext, useWatch } from 'react-hook-form';
import { useShopsysSelector } from 'redux/main';
import { TransportAndPaymentFormType } from 'types/form';
import { PaymentType } from 'types/payment';
import { PickupPlaceType } from 'types/pickupPlace';
import { TransportType } from 'types/transport';

const { publicRuntimeConfig } = getConfig();

type SelectProps = {
    transports: TransportType[];
    preselectedPickupPlace: PickupPlaceType | null;
};

const Select: FC<SelectProps> = (props) => {
    const testIdentifier = 'pages-order-';

    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext<TransportAndPaymentFormType>();
    const formMeta = useTransportAndPaymentFormMeta(formProviderMethods);
    const { defaultLocale, currencyCode } = useShopsysSelector((state) => state.domain);
    const [transportValue, paymentValue, goPaySwiftValue] = useWatch({
        name: [formMeta.fields.transport.name, formMeta.fields.payment.name, formMeta.fields.goPaySwift.name],
        control: formProviderMethods.control,
    });
    const [preSelectedTransport, setPreselectedTransport] = useState<TransportType | null>(null);
    const { transport, pickupPlace, payment } = useCurrentCart();
    const changeTransportInCart = useChangeTransportInCart();
    const changePaymentInCart = useChangePaymentInCart();
    const [getGoPaySwiftsResult] = useGoPaySwiftsQueryApi({
        variables: { currencyCode: currencyCode },
    });

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

    useComponentUpdate(() => {
        const potentialNewTransport = props.transports.find((transport) => transport.uuid === transportValue);

        if (potentialNewTransport === undefined) {
            changeTransportInCart(null, null);
            return;
        }

        if (potentialNewTransport.isPersonalPickup) {
            openPersonalPickupPopup(potentialNewTransport);
            return;
        }

        changeTransportInCart(transportValue, null);
    }, [transportValue]);

    useEffectOnce(() => {
        changeTransportInCart(transportValue, props.preselectedPickupPlace);
    });

    useEffect(() => {
        changePaymentInCart(paymentValue, goPaySwiftValue);
    }, [paymentValue, goPaySwiftValue, changePaymentInCart]);

    const resetTransportAndPayment = () => {
        formProviderMethods.setValue(formMeta.fields.transport.name, null);
        formProviderMethods.setValue(formMeta.fields.payment.name, null);
        formProviderMethods.setValue(formMeta.fields.goPaySwift.name, null);
        removePacketeryCookie();
    };

    const onChangePickupPlaceHandler = (selectedPickupPlace: PickupPlaceType | null) => {
        if (selectedPickupPlace !== null) {
            changeTransportInCart(transportValue, selectedPickupPlace);
        } else {
            removePacketeryCookie();
        }

        setPreselectedTransport(null);
    };

    const onClosePickupPlacePopupHandler = () => {
        removePacketeryCookie();
        setPreselectedTransport(null);
    };

    const getPickupPlaceDetail = (transportItem: TransportType) => {
        return transportValue === transportItem.uuid &&
            transportItem.stores.some((store) => store.identifier === pickupPlace?.identifier)
            ? pickupPlace
            : null;
    };

    const renderTransportListItem = (
        transportItem: TransportType,
        isActive: boolean,
        fieldRef: ControllerRenderProps<any, any>,
    ) => {
        return (
            <ListItemStyled
                key={transportItem.uuid}
                isActive={isActive}
                data-testid={testIdentifier + 'transport-item' + (isActive ? '-active' : '')}
            >
                <Radiobutton
                    name={formMeta.fields.transport.name}
                    id={transportItem.uuid}
                    value={transportItem.uuid}
                    fieldRef={fieldRef}
                    image={transportItem.image}
                    checked={isActive}
                    uncheckCallback={resetTransportAndPayment}
                    data-testid={testIdentifier + 'transport-item-input'}
                    label={
                        <SelectItemLabel
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

    const renderPaymentListItem = (
        paymentItem: PaymentType,
        isActive: boolean,
        fieldRef: ControllerRenderProps<any, any>,
    ) => {
        return (
            <ListItemStyled
                key={paymentItem.uuid}
                isActive={isActive}
                data-testid={testIdentifier + 'payment-item' + (isActive ? '-active' : '')}
            >
                <Radiobutton
                    name={formMeta.fields.payment.name}
                    id={paymentItem.uuid}
                    value={paymentItem.uuid}
                    fieldRef={fieldRef}
                    image={paymentItem.image}
                    checked={isActive}
                    uncheckCallback={() => formProviderMethods.setValue(formMeta.fields.payment.name, null)}
                    data-testid={testIdentifier + 'payment-item-input'}
                    label={
                        <SelectItemLabel
                            name={paymentItem.name}
                            price={paymentItem.price}
                            description={paymentItem.description}
                            type={paymentItem.type}
                        />
                    }
                />
            </ListItemStyled>
        );
    };

    return (
        <>
            <PacketeryContainer />
            <div data-testid={testIdentifier + 'transport-and-payment'}>
                <div data-testid={testIdentifier + 'transport'}>
                    <Heading type="h3">{formMeta.fields.transport.label}</Heading>
                    <Controller
                        name={formMeta.fields.transport.name}
                        render={({ field }) => (
                            <ul>
                                {transport !== null
                                    ? renderTransportListItem(transport, true, field)
                                    : props.transports.map((transportItem) =>
                                          renderTransportListItem(transportItem, false, field),
                                      )}
                            </ul>
                        )}
                    />
                    {transportValue !== null && (
                        <ResetButtonStyled
                            type="button"
                            onClick={resetTransportAndPayment}
                            data-testid={testIdentifier + 'reset-transport'}
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
                {transport !== null &&
                    transportValue !== null &&
                    transport.uuid === transportValue &&
                    preSelectedTransport === null && (
                        <PaymentListWrapper data-testid={testIdentifier + 'payment'}>
                            <Heading type="h3">{formMeta.fields.payment.label}</Heading>
                            <Controller
                                name={formMeta.fields.payment.name}
                                render={({ field }) => (
                                    <ul>
                                        {paymentValue !== null && payment !== null
                                            ? renderPaymentListItem(payment, true, field)
                                            : transport.payments.map((paymentItem) =>
                                                  renderPaymentListItem(paymentItem, false, field),
                                              )}
                                    </ul>
                                )}
                            />

                            {payment?.type === 'goPay' && payment.goPayPaymentMethod?.identifier === 'BANK_ACCOUNT' && (
                                <>
                                    <Heading type="h3">{formMeta.fields.goPaySwift.label}</Heading>
                                    <Controller
                                        name={formMeta.fields.goPaySwift.name}
                                        render={({ field }) => (
                                            <>
                                                {getGoPaySwiftsResult.data?.GoPaySwifts.map((goPaySwift) => (
                                                    <Radiobutton
                                                        key={goPaySwift.swift}
                                                        name="GoPaySwift"
                                                        id={goPaySwift.swift}
                                                        value={goPaySwift.swift}
                                                        fieldRef={field}
                                                        checked={goPaySwiftValue === goPaySwift.swift}
                                                        label={goPaySwift.name}
                                                    />
                                                ))}
                                            </>
                                        )}
                                    />
                                </>
                            )}
                            {paymentValue !== null && payment !== null && (
                                <ResetButtonStyled
                                    type="button"
                                    onClick={() => formProviderMethods.setValue(formMeta.fields.payment.name, null)}
                                    data-testid={testIdentifier + 'reset-payment'}
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

export default Select;
