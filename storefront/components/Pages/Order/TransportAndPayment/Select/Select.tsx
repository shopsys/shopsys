import { Controller, useFormContext, useWatch } from 'react-hook-form';
import { FC, useEffect, useState } from 'react';
import { ListItemStyled, PaymentListWrapper, ResetButtonStyled } from './Select.style';
import { mapPacketeryExtendedPoint, packeteryPick, removePacketeryCookie, setPacketeryCookie } from 'helpers/packetery';
import { mapPaymentToPaymentInput, mapTransportToTransportInput, useLoadCart } from 'connectors/cart/Cart';
import { TransportInputType, TransportType } from 'types/transport';
import Heading from 'components/Basic/Heading';
import Icon from 'components/Basic/Icon';
import PacketeryContainer from 'components/Pages/Order/TransportAndPayment/PacketeryContainer';
import { PacketeryExtendedPoint } from 'helpers/packetery/types';
import { PaymentInputType } from 'types/payment';
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
        isCartEmpty,
        cartInput: { cartUuid, promoCode, transport: transportInput, payment: paymentInput },
    } = useShopsysSelector((state) => state.cart);

    const [isPreSelectingTransport, setIsPreSelectingTransport] = useState(false);

    const [mappedTransportInput, setMappedTransportInput] = useState<TransportInputType | null>(transportInput);
    const [mappedPaymentInput, setMappedPaymentInput] = useState<PaymentInputType | null>(paymentInput);

    const [updatedTransport, updateTransport] = useState<TransportType | null>(transport);
    const [updatedPickupPlace, updatePickupPlace] = useState<PickupPlaceType | null>(pickupPlace);

    useLoadCart(cartUuid, isCartEmpty, mappedTransportInput, mappedPaymentInput, promoCode);

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
        if (packeteryPoint !== null && packeteryTransport !== null) {
            const mappedPacketeryPoint = mapPacketeryExtendedPoint(packeteryPoint);
            setPacketeryCookie(mappedPacketeryPoint);
            updateTransport(packeteryTransport);
            updatePickupPlace(mappedPacketeryPoint);
            setMappedTransportInput(mapTransportToTransportInput(packeteryTransport, mappedPacketeryPoint));
        } else {
            formProviderMethods.setValue(formMeta.fields.transport.name, null);
        }
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
                                {transportValue !== null && updatedTransport !== null ? (
                                    <ListItemStyled key={updatedTransport.uuid} isActive={true}>
                                        <Radiobutton
                                            name={formMeta.fields.transport.name}
                                            id={updatedTransport.uuid}
                                            value={updatedTransport.uuid}
                                            fieldRef={field}
                                            image={updatedTransport.image}
                                            checked={true}
                                            uncheckCallback={resetTransportAndPayment}
                                            label={
                                                <SelectItemLabel
                                                    name={updatedTransport.name}
                                                    daysUntilDelivery={updatedTransport.daysUntilDelivery}
                                                    price={updatedTransport.price}
                                                    description={updatedTransport.description}
                                                    pickupPlaceDetail={
                                                        transportValue === updatedTransport.uuid &&
                                                        updatedTransport.stores.some(
                                                            (store) => store.identifier === pickupPlace?.identifier,
                                                        )
                                                            ? pickupPlace
                                                            : null
                                                    }
                                                />
                                            }
                                        />
                                    </ListItemStyled>
                                ) : (
                                    props.transports.map((transportItem) => (
                                        <ListItemStyled key={transportItem.uuid} isActive={false}>
                                            <Radiobutton
                                                name={formMeta.fields.transport.name}
                                                id={transportItem.uuid}
                                                value={transportItem.uuid}
                                                fieldRef={field}
                                                image={transportItem.image}
                                                checked={false}
                                                uncheckCallback={resetTransportAndPayment}
                                                label={
                                                    <SelectItemLabel
                                                        name={transportItem.name}
                                                        daysUntilDelivery={transportItem.daysUntilDelivery}
                                                        price={transportItem.price}
                                                        description={transportItem.description}
                                                        pickupPlaceDetail={
                                                            transportValue === transportItem.uuid &&
                                                            transportItem.stores.some(
                                                                (store) => store.identifier === pickupPlace?.identifier,
                                                            )
                                                                ? pickupPlace
                                                                : null
                                                        }
                                                    />
                                                }
                                            />
                                        </ListItemStyled>
                                    ))
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
                                        {paymentValue !== null && payment !== null ? (
                                            <ListItemStyled key={payment.uuid} isActive={true}>
                                                <Radiobutton
                                                    name={formMeta.fields.payment.name}
                                                    id={payment.uuid}
                                                    value={payment.uuid}
                                                    fieldRef={field}
                                                    image={payment.image}
                                                    checked={true}
                                                    uncheckCallback={() =>
                                                        formProviderMethods.setValue(formMeta.fields.payment.name, null)
                                                    }
                                                    label={
                                                        <SelectItemLabel
                                                            name={payment.name}
                                                            price={payment.price}
                                                            description={payment.description}
                                                        />
                                                    }
                                                />
                                            </ListItemStyled>
                                        ) : (
                                            transport.payments.map((paymentItem) => (
                                                <ListItemStyled key={paymentItem.uuid} isActive={false}>
                                                    <Radiobutton
                                                        name={formMeta.fields.payment.name}
                                                        id={paymentItem.uuid}
                                                        value={paymentItem.uuid}
                                                        fieldRef={field}
                                                        image={paymentItem.image}
                                                        checked={false}
                                                        uncheckCallback={() =>
                                                            formProviderMethods.setValue(
                                                                formMeta.fields.payment.name,
                                                                null,
                                                            )
                                                        }
                                                        label={
                                                            <SelectItemLabel
                                                                name={paymentItem.name}
                                                                price={paymentItem.price}
                                                                description={paymentItem.description}
                                                            />
                                                        }
                                                    />
                                                </ListItemStyled>
                                            ))
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
