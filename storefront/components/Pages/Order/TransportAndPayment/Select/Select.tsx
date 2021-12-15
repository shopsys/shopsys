import { Controller, ControllerRenderProps, useFormContext, useWatch } from 'react-hook-form';
import { FC, useEffect, useState } from 'react';
import { ListItemStyled, PaymentListWrapper, ResetButtonStyled } from './Select.style';
import { mapPacketeryExtendedPoint, packeteryPick, removePacketeryCookie, setPacketeryCookie } from 'helpers/packetery';
import { mapPaymentToPaymentInput, mapTransportToTransportInput, useLoadCart } from 'connectors/cart/Cart';
import { PaymentInputType, PaymentType } from 'types/payment';
import { TransportInputType, TransportType } from 'types/transport';
import Heading from 'components/Basic/Heading';
import Icon from 'components/Basic/Icon';
import PacketeryContainer from 'components/Pages/Order/TransportAndPayment/PacketeryContainer';
import { PacketeryExtendedPoint } from 'helpers/packetery/types';
import PickupPlacePopup from './PickupPlacePopup/PickupPlacePopup';
import { PickupPlaceType } from 'types/pickupPlace';
import Radiobutton from 'components/Forms/Radiobutton';
import SelectItemLabel from './SelectItemLabel';
import { TransportAndPaymentFormType } from 'types/form';
import { useComponentUpdate } from 'hooks/helpers/UseComponentUpdate';
import { useShopsysSelector } from 'redux/main';
import { useTransportAndPaymentFormMeta } from 'components/Pages/Order/TransportAndPayment/formMeta';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type SelectProps = {
    transports: TransportType[];
};

const Select: FC<SelectProps> = (props) => {
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext<TransportAndPaymentFormType>();
    const formMeta = useTransportAndPaymentFormMeta(formProviderMethods);
    const { defaultLocale } = useShopsysSelector((state) => state.domain);
    const [transportValue, paymentValue] = useWatch({
        name: [formMeta.fields.transport.name, formMeta.fields.payment.name],
        control: formProviderMethods.control,
    });

    const {
        payment,
        transport,
        pickupPlace,
        cartInput: { cartUuid, promoCode, transport: transportInput, payment: paymentInput },
    } = useShopsysSelector((state) => state.cart);

    const [isPreSelectingTransport, setIsPreSelectingTransport] = useState(false);

    const [mappedTransportInput, setMappedTransportInput] = useState<TransportInputType | null>(transportInput);
    const [mappedPaymentInput, setMappedPaymentInput] = useState<PaymentInputType | null>(paymentInput);

    const [updatedTransport, updateTransport] = useState<TransportType | null>(transport);
    const [updatedPickupPlace, updatePickupPlace] = useState<PickupPlaceType | null>(pickupPlace);

    useLoadCart(cartUuid, mappedTransportInput, mappedPaymentInput, promoCode);

    useEffect(() => {
        formProviderMethods.setValue(
            formMeta.fields.transport.name,
            transport?.uuid === undefined ? null : transport.uuid,
        );
        formProviderMethods.setValue(formMeta.fields.payment.name, payment?.uuid === undefined ? null : payment.uuid);
        updateTransport(transport);
        updatePickupPlace(pickupPlace);
    }, [transport, pickupPlace, payment]);

    useComponentUpdate(() => {
        const newTransport = props.transports.find((transport) => transport.uuid === transportValue);
        formProviderMethods.setValue(
            formMeta.fields.transport.name,
            newTransport?.uuid === undefined ? null : newTransport.uuid,
        );

        if (newTransport?.isPersonalPickup === true) {
            onChangePersonalPickupTransportHandler(newTransport);
        }

        if (newTransport === undefined) {
            updateTransport(null);
            setMappedTransportInput(null);
        } else {
            updateTransport(newTransport);
            setMappedTransportInput(mapTransportToTransportInput(newTransport, updatedPickupPlace));
        }
    }, [transportValue]);

    useComponentUpdate(() => {
        const newPayment = updatedTransport?.payments.find((payment) => payment.uuid === paymentValue);
        formProviderMethods.setValue(
            formMeta.fields.payment.name,
            newPayment?.uuid === undefined ? null : newPayment.uuid,
        );

        if (newPayment === undefined) {
            setMappedPaymentInput(null);
        } else {
            setMappedPaymentInput(mapPaymentToPaymentInput(newPayment));
        }
    }, [paymentValue]);

    const isPickupPlaceSelected = () => transportInput !== null && transportInput.pickupPlaceIdentifier !== null;

    const onChangePersonalPickupTransportHandler = (newTransport: TransportType) => {
        if (newTransport.transportType.code === 'packetery') {
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
        formProviderMethods.setValue(formMeta.fields.transport.name, null);
        formProviderMethods.setValue(formMeta.fields.payment.name, null);
        resetPickupPlace();
    };

    const onChangePickupPlaceHandler = (selectedPickupPlace: PickupPlaceType | null) => {
        setIsPreSelectingTransport(false);
        if (selectedPickupPlace !== null && updatedTransport !== null) {
            updatePickupPlace(selectedPickupPlace);
            setMappedTransportInput(mapTransportToTransportInput(updatedTransport, selectedPickupPlace));
        } else {
            resetPickupPlace();
            removePacketeryCookie();
        }
    };

    const onClosePickupPlacePopupHandler = () => {
        setIsPreSelectingTransport(false);
        resetPickupPlace();
    };

    const resetPickupPlace = () => {
        updateTransport(null);
        updatePickupPlace(null);
        setMappedTransportInput(null);
        removePacketeryCookie();
    };

    const onSelectPacketeryPickupPlaceCallback = (
        packeteryPoint: PacketeryExtendedPoint | null,
        packeteryTransport: TransportType,
    ) => {
        if (packeteryPoint !== null) {
            const mappedPacketeryPoint = mapPacketeryExtendedPoint(packeteryPoint);
            setPacketeryCookie(mappedPacketeryPoint);
            updateTransport(packeteryTransport);
            updatePickupPlace(mappedPacketeryPoint);
            setMappedTransportInput(mapTransportToTransportInput(packeteryTransport, mappedPacketeryPoint));
        } else {
            formProviderMethods.setValue(formMeta.fields.transport.name, null);
        }
    };

    const renderTransportListItem = (
        transportItem: TransportType,
        isActive: boolean,
        fieldRef: ControllerRenderProps,
    ) => {
        return (
            <ListItemStyled key={transportItem.uuid} isActive={isActive}>
                <Radiobutton
                    name={formMeta.fields.transport.name}
                    id={transportItem.uuid}
                    value={transportItem.uuid}
                    fieldRef={fieldRef}
                    image={transportItem.image}
                    checked={isActive}
                    uncheckCallback={resetTransportAndPayment}
                    label={
                        <SelectItemLabel
                            name={transportItem.name}
                            daysUntilDelivery={transportItem.daysUntilDelivery}
                            price={transportItem.price}
                            description={transportItem.description}
                            pickupPlaceDetail={
                                transportValue === transportItem.uuid &&
                                transportItem.stores.some((store) => store.identifier === pickupPlace?.identifier)
                                    ? pickupPlace
                                    : null
                            }
                        />
                    }
                />
            </ListItemStyled>
        );
    };

    const renderPaymentListItem = (paymentItem: PaymentType, isActive: boolean, fieldRef: ControllerRenderProps) => {
        return (
            <ListItemStyled key={paymentItem.uuid} isActive={isActive}>
                <Radiobutton
                    name={formMeta.fields.payment.name}
                    id={paymentItem.uuid}
                    value={paymentItem.uuid}
                    fieldRef={fieldRef}
                    image={paymentItem.image}
                    checked={isActive}
                    uncheckCallback={() => formProviderMethods.setValue(formMeta.fields.payment.name, null)}
                    label={
                        <SelectItemLabel
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
            <div>
                <div>
                    <Heading type="h3">{formMeta.fields.transport.label}</Heading>
                    <Controller
                        name={formMeta.fields.transport.name}
                        render={({ field }) => (
                            <ul>
                                {transportValue !== null && updatedTransport !== null
                                    ? renderTransportListItem(updatedTransport, true, field)
                                    : props.transports.map((transportItem) =>
                                          renderTransportListItem(transportItem, false, field),
                                      )}
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
                        <PickupPlacePopup
                            isVisible={isPreSelectingTransport}
                            transport={updatedTransport}
                            onChangePickupPlaceCallback={onChangePickupPlaceHandler}
                            onClosePickupPlacePopupCallback={onClosePickupPlacePopupHandler}
                        />
                    )}
                </div>
                {transport !== null &&
                    transportValue !== null &&
                    transport.uuid === transportValue &&
                    !isPreSelectingTransport && (
                        <PaymentListWrapper>
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
                            {paymentValue !== null && payment !== null && (
                                <ResetButtonStyled
                                    type="button"
                                    onClick={() => formProviderMethods.setValue(formMeta.fields.payment.name, null)}
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
