import { Controller, useFormContext, useWatch } from 'react-hook-form';
import { FC, useEffect, useState } from 'react';
import { ListItemStyled, PaymentListWrapper, ResetButtonStyled } from './Select.style';
import { loadCart, mapPaymentToPaymentInput, mapTransportToTransportInput } from 'connectors/cart/Cart';
import { PaymentInputType, PaymentType } from 'connectors/payments/types';
import { StoreType, TransportInputType, TransportType } from 'connectors/transports/types';
import Heading from 'components/Basic/Heading';
import Icon from 'components/Basic/Icon';
import PersonalPickupStorePopup from './PersonalPickupStorePopup/PersonalPickupStorePopup';
import Radiobutton from 'components/Forms/Radiobutton';
import SelectItemLabel from './SelectItemLabel';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type SelectProps = {
    transports: TransportType[];
};

const Select: FC<SelectProps> = (props) => {
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext();

    const transportValue = useWatch({ name: 'transport' });
    const paymentValue = useWatch({ name: 'payment' });

    const { payment, transport, personalPickupStore } = useShopsysSelector((state) => state.user);
    const transportInput = useShopsysSelector((state) => state.cartInput.transport);
    const paymentInput = useShopsysSelector((state) => state.cartInput.payment);
    const { cartUuid, promoCode } = useShopsysSelector((state) => state.cartInput);

    const [preSelectedTransport, preSelectTransport] = useState<TransportType | null>(null);

    const [mappedTransportInput, setMappedTransportInput] = useState<TransportInputType | null>(transportInput);
    const [mappedPaymentInput, setMappedPaymentInput] = useState<PaymentInputType | null>(paymentInput);

    const [updatedTransport, updateTransport] = useState<TransportType | null>(transport);
    const [updatedPersonalPickupStore, updatePersonalPickupStore] = useState<StoreType | null>(personalPickupStore);
    const [updatedPayment, updatePayment] = useState<PaymentType | null>(payment);

    loadCart(cartUuid, mappedTransportInput, mappedPaymentInput, promoCode);

    useEffect(() => {
        setMappedTransportInput(
            updatedTransport === null
                ? null
                : mapTransportToTransportInput(updatedTransport, updatedPersonalPickupStore),
        );
    }, [updatedTransport, updatedPersonalPickupStore]);

    useEffect(() => {
        setMappedPaymentInput(updatedPayment === null ? null : mapPaymentToPaymentInput(updatedPayment));
    }, [updatedPayment]);

    useEffect(() => {
        formProviderMethods.setValue('transport', transport?.uuid === undefined ? null : transport.uuid);
        formProviderMethods.setValue('payment', payment?.uuid === undefined ? null : payment.uuid);
        updateTransport(transport);
        updatePersonalPickupStore(personalPickupStore);
        updatePayment(payment);
    }, [transport, personalPickupStore, payment]);

    useEffect(() => {
        const newTransport = props.transports.find((transport) => transport.uuid === transportValue);

        if (newTransport?.hasPersonalPickup && updatedTransport === null && paymentValue === null) {
            preSelectTransport(newTransport === undefined ? null : newTransport);
        } else {
            updateTransport(newTransport === undefined ? null : newTransport);
        }
    }, [transportValue]);

    useEffect(() => {
        const newPayment = updatedTransport?.payments.find((payment) => payment.uuid === paymentValue);
        updatePayment(newPayment === undefined ? null : newPayment);
    }, [paymentValue]);

    const onChangePersonalPickupStoreHandler = (selectedPersonalPickup: StoreType | null) => {
        if (selectedPersonalPickup !== null) {
            updateTransport(preSelectedTransport);
            updatePersonalPickupStore(selectedPersonalPickup);
        } else {
            formProviderMethods.setValue('transport', null);
        }
        preSelectTransport(null);
    };

    const resetTransportAndPayment = () => {
        formProviderMethods.setValue('transport', null);
        formProviderMethods.setValue('payment', null);
        formProviderMethods.setValue('personalPickup', null);
    };

    const resetPayment = () => {
        formProviderMethods.setValue('payment', null);
    };
    return (
        <>
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
                                            disabled={transport !== null && transportItem.uuid !== transportValue}
                                            checked={transportValue === transportItem.uuid}
                                            uncheckCallback={resetTransportAndPayment}
                                            label={
                                                <SelectItemLabel
                                                    name={transportItem.name}
                                                    daysUntilDelivery={transportItem.daysUntilDelivery}
                                                    price={transportItem.price}
                                                    description={transportItem.description}
                                                    hasPersonalPickup={transportItem.hasPersonalPickup}
                                                    personalPickupStoreDetail={
                                                        updatedPersonalPickupStore === null
                                                            ? undefined
                                                            : updatedPersonalPickupStore
                                                    }
                                                />
                                            }
                                        />
                                    </ListItemStyled>
                                ))}
                            </ul>
                        )}
                    />
                    {transport !== null && (
                        <ResetButtonStyled type="button" onClick={resetTransportAndPayment}>
                            {t('Change transport type')}
                            <Icon icon="Arrow" />
                        </ResetButtonStyled>
                    )}
                    <PersonalPickupStorePopup
                        isVisible={preSelectedTransport !== null}
                        transport={preSelectedTransport as TransportType}
                        onChangePersonalPickupStoreCallback={onChangePersonalPickupStoreHandler}
                    />
                </div>
                {transport !== null && (
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
                                                disabled={payment !== null && paymentValue !== paymentItem.uuid}
                                                checked={paymentValue === paymentItem.uuid}
                                                uncheckCallback={resetPayment}
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
                        {payment !== null && (
                            <ResetButtonStyled type="button" onClick={resetPayment}>
                                {t('Change payment type')}
                                <Icon icon="Arrow" />
                            </ResetButtonStyled>
                        )}
                    </PaymentListWrapper>
                )}
            </div>
        </>
    );
};

export default Select;
