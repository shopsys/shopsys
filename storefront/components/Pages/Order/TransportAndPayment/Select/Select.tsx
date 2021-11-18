import { Controller, useFormContext, useWatch } from 'react-hook-form';
import { FC, useEffect, useState } from 'react';
import { ListItemStyled, PaymentListWrapper, ResetButtonStyled } from './Select.style';
import { loadCart, mapPaymentToPaymentInput, mapTransportToTransportInput } from 'connectors/cart/Cart';
import { mapPacketeryExtendedPoint, packeteryPick, removePacketeryCookie, setPacketeryCookie } from 'helpers/packetery';
import { StoreType, TransportInputType, TransportType } from 'connectors/transports/types';
import { getSelectedPersonalPickupStore } from 'connectors/transports/PersonalPickupStore';
import Heading from 'components/Basic/Heading';
import Icon from 'components/Basic/Icon';
import PacketeryContainer from 'components/Pages/Order/TransportAndPayment/PacketeryContainer';
import { PacketeryExtendedPoint } from 'helpers/packetery/types';
import { PaymentInputType } from 'connectors/payments/types';
import PersonalPickupStorePopup from './PersonalPickupStorePopup/PersonalPickupStorePopup';
import Radiobutton from 'components/Forms/Radiobutton';
import SelectItemLabel from './SelectItemLabel';
import { useComponentUpdate } from 'hooks/helpers/UseComponentUpdate';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type SelectProps = {
    transports: TransportType[];
};

const Select: FC<SelectProps> = (props) => {
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext();
    const { defaultLocale } = useShopsysSelector((state) => state.domain);
    const transportValue = useWatch({ name: 'transport' });
    const paymentValue = useWatch({ name: 'payment' });

    const { payment, transport, personalPickupStore } = useShopsysSelector((state) => state.user);
    const transportInput = useShopsysSelector((state) => state.cartInput.transport);
    const paymentInput = useShopsysSelector((state) => state.cartInput.payment);
    const { cartUuid, promoCode } = useShopsysSelector((state) => state.cartInput);

    const [isPreSelectingTransport, setIsPreSelectingTransport] = useState(false);

    const [mappedTransportInput, setMappedTransportInput] = useState<TransportInputType | null>(transportInput);
    const [mappedPaymentInput, setMappedPaymentInput] = useState<PaymentInputType | null>(paymentInput);

    const [updatedTransport, updateTransport] = useState<TransportType | null>(transport);
    const [updatedPersonalPickupStore, updatePersonalPickupStore] = useState<StoreType | null>(personalPickupStore);

    loadCart(cartUuid, mappedTransportInput, mappedPaymentInput, promoCode);

    useEffect(() => {
        formProviderMethods.setValue('transport', transport?.uuid === undefined ? null : transport.uuid);
        formProviderMethods.setValue('payment', payment?.uuid === undefined ? null : payment.uuid);
        updateTransport(transport);
        updatePersonalPickupStore(personalPickupStore);
    }, [transport, personalPickupStore, payment]);

    useComponentUpdate(() => {
        const newTransport = props.transports.find((transport) => transport.uuid === transportValue);
        formProviderMethods.setValue('transport', newTransport?.uuid === undefined ? null : newTransport.uuid);

        if (newTransport?.isPersonalPickup === true) {
            onChangePersonalPickupTransportHandler(newTransport);
        }

        if (newTransport === undefined) {
            updateTransport(null);
            setMappedTransportInput(null);
        } else {
            updateTransport(newTransport);
            setMappedTransportInput(mapTransportToTransportInput(newTransport, updatedPersonalPickupStore));
        }
    }, [transportValue]);

    useComponentUpdate(() => {
        const newPayment = updatedTransport?.payments.find((payment) => payment.uuid === paymentValue);
        formProviderMethods.setValue('payment', newPayment?.uuid === undefined ? null : newPayment.uuid);

        if (newPayment === undefined) {
            setMappedPaymentInput(null);
        } else {
            setMappedPaymentInput(mapPaymentToPaymentInput(newPayment));
        }
    }, [paymentValue]);

    const isPickupPlaceSelected = () => transportInput !== null && transportInput.pickupPlaceIdentifier !== null;

    const onChangePersonalPickupTransportHandler = (newTransport: TransportType) => {
        if (newTransport?.transportType.code === 'packetery') {
            if (!isPickupPlaceSelected()) {
                const packeteryApiKey = process.env.NEXT_PUBLIC_PACKETERY_API_KEY;
                if (packeteryApiKey !== undefined) {
                    packeteryPick(
                        packeteryApiKey,
                        (point) => {
                            onSelectPacketeryPickupPlaceCallback(point, newTransport);
                        },
                        { language: defaultLocale },
                    );
                }
            }
            return;
        }

        removePacketeryCookie();
        setIsPreSelectingTransport(updatedTransport === null);
    };

    const resetTransportAndPayment = () => {
        formProviderMethods.setValue('transport', null);
        formProviderMethods.setValue('payment', null);
        resetPersonalPickupStore();
    };

    const onChangePersonalPickupStoreHandler = (selectedPersonalPickup: StoreType | null) => {
        setIsPreSelectingTransport(false);
        if (selectedPersonalPickup !== null && updatedTransport !== null) {
            updatePersonalPickupStore(selectedPersonalPickup);
            setMappedTransportInput(mapTransportToTransportInput(updatedTransport, selectedPersonalPickup));
        } else {
            resetPersonalPickupStore();
            removePacketeryCookie();
        }
    };

    const onClosePersonalPickupStorePopupHandler = () => {
        setIsPreSelectingTransport(false);
        resetPersonalPickupStore();
    };

    const resetPersonalPickupStore = () => {
        updateTransport(null);
        updatePersonalPickupStore(null);
        setMappedTransportInput(null);
        removePacketeryCookie();
    };

    const onSelectPacketeryStoreCallback = (
        packeteryPoint: PacketeryExtendedPoint | null,
        packeteryTransport: TransportType,
    ) => {
        if (packeteryPoint !== null && packeteryTransport !== null) {
            const mappedPacketeryPoint = mapPacketeryExtendedPoint(packeteryPoint);
            setPacketeryCookie(mappedPacketeryPoint);
            updateTransport(packeteryTransport);
            updatePersonalPickupStore(mappedPacketeryPoint);
            setMappedTransportInput(mapTransportToTransportInput(packeteryTransport, mappedPacketeryPoint));
        } else {
            formProviderMethods.setValue('transport', null);
        }
    };

    return (
        <>
            <PacketeryContainer />
            <div>
                <div>
                    <Heading type="h3">{t('Choose transport type')}</Heading>
                    <Controller
                        name="transport"
                        render={({ field }) => (
                            <ul>
                                {props.transports.map((transportItem) => (
                                    <ListItemStyled
                                        key={transportItem.uuid}
                                        isActive={transportValue === transportItem.uuid}
                                    >
                                        <Radiobutton
                                            name={field.name}
                                            id={transportItem.uuid}
                                            value={transportItem.uuid}
                                            fieldRef={field}
                                            image={transportItem.image}
                                            disabled={transportValue !== null && transportItem.uuid !== transportValue}
                                            checked={transportValue === transportItem.uuid}
                                            uncheckCallback={resetTransportAndPayment}
                                            label={
                                                <SelectItemLabel
                                                    name={transportItem.name}
                                                    daysUntilDelivery={transportItem.daysUntilDelivery}
                                                    price={transportItem.price}
                                                    description={transportItem.description}
                                                    personalPickupStoreDetail={getSelectedPersonalPickupStore(
                                                        transportItem,
                                                        updatedPersonalPickupStore?.uuid === undefined
                                                            ? null
                                                            : updatedPersonalPickupStore.uuid,
                                                    )}
                                                />
                                            }
                                        />
                                    </ListItemStyled>
                                ))}
                            </ul>
                        )}
                    />
                    {transportValue !== null && (
                        <ResetButtonStyled type="button" onClick={resetTransportAndPayment}>
                            {t('Change transport type')}
                            <Icon iconType="icon" icon="Arrow" />
                        </ResetButtonStyled>
                    )}
                    {updatedTransport !== null && (
                        <PersonalPickupStorePopup
                            isVisible={isPreSelectingTransport}
                            transport={updatedTransport}
                            onChangePersonalPickupStoreCallback={onChangePersonalPickupStoreHandler}
                            onClosePersonalPickupStorePopupCallback={onClosePersonalPickupStorePopupHandler}
                        />
                    )}
                </div>
                {transport !== null && !isPreSelectingTransport && (
                    <PaymentListWrapper>
                        <Heading type="h3">{t('Choose payment type')}</Heading>
                        <Controller
                            name="payment"
                            render={({ field }) => (
                                <ul>
                                    {transport.payments.map((paymentItem) => (
                                        <ListItemStyled
                                            key={paymentItem.uuid}
                                            isActive={paymentValue === paymentItem.uuid}
                                        >
                                            <Radiobutton
                                                name={field.name}
                                                id={paymentItem.uuid}
                                                value={paymentItem.uuid}
                                                fieldRef={field}
                                                image={paymentItem.image}
                                                disabled={paymentValue !== null && paymentValue !== paymentItem.uuid}
                                                checked={paymentValue === paymentItem.uuid}
                                                uncheckCallback={() => formProviderMethods.setValue('payment', null)}
                                                label={
                                                    <SelectItemLabel
                                                        name={paymentItem.name}
                                                        price={paymentItem.price}
                                                        description={paymentItem.description}
                                                    />
                                                }
                                            />
                                        </ListItemStyled>
                                    ))}
                                </ul>
                            )}
                        />
                        {paymentValue !== null && (
                            <ResetButtonStyled
                                type="button"
                                onClick={() => formProviderMethods.setValue('payment', null)}
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
