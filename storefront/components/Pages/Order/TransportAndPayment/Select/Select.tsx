import { Controller, useFormContext, useWatch } from 'react-hook-form';
import { FC, useEffect, useState } from 'react';
import { initUserDataCookie, updateUserDataCookie } from 'helpers/Cookies';
import {
    ListItemStyled,
    PaymentListWrapper,
    PersonalPickupPopupWrapperStyled,
    PopupButtonWrapperStyled,
    ResetButtonStyled,
} from './Select.style';
import { PaymentType, StoreType, TransportType } from 'connectors/transports/types';
import Button from 'components/Forms/Button';
import Heading from 'components/Basic/Heading';
import Icon from 'components/Basic/Icon';
import Popup from 'components/Layout/Popup';
import Radiobutton from 'components/Forms/Radiobutton';
import SelectItemLabel from './SelectItemLabel';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type SelectProps = {
    transports: TransportType[];
    transportUuid?: string;
    personalPickupUuid?: string;
    paymentUuid?: string;
};

const Select: FC<SelectProps> = (props) => {
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext();
    const [transport, setTransport] = useState<undefined | TransportType>(undefined);
    const [personalPickup, setPersonalPickup] = useState<undefined | StoreType>(undefined);
    const [payment, setPayment] = useState<undefined | PaymentType>(undefined);
    const [isSelecting, setIsSelecting] = useState(false);

    const transportValue = useWatch({ name: 'transport' });
    const personalPickupValue = useWatch({ name: 'personalPickup' });
    const paymentValue = useWatch({ name: 'payment' });

    useEffect(() => {
        const initialTransport = props.transports.find((transport) => transport.uuid === props.transportUuid);
        if (initialTransport?.personalPickup && props.personalPickupUuid === undefined) {
            updateUserDataCookie(initUserDataCookie());
            formProviderMethods.setValue('transport', undefined);
            formProviderMethods.setValue('personalPickup', undefined);
            formProviderMethods.setValue('payment', undefined);
        } else {
            setTransport(initialTransport);
            setPersonalPickup(initialTransport?.stores.find((store) => store.uuid === props.personalPickupUuid));
            setPayment(initialTransport?.payments.find((payment) => payment.uuid === props.paymentUuid));
            formProviderMethods.setValue('transport', props.transportUuid);
            formProviderMethods.setValue('personalPickup', props.personalPickupUuid);
            formProviderMethods.setValue('payment', props.paymentUuid);
        }
    }, []);

    useEffect(() => {
        const currentTransportUuid = transportValue;
        if (currentTransportUuid !== transport?.uuid) {
            handleTransportChange(transportValue);
        }
    }, [transportValue]);

    useEffect(() => {
        const currentPaymentUuid = paymentValue;
        if (currentPaymentUuid !== payment?.uuid) {
            handlePaymentChange(paymentValue);
        }
    }, [paymentValue]);

    useEffect(() => {
        const currentPersonalPickupUuid = personalPickupValue;
        if (currentPersonalPickupUuid !== personalPickup?.uuid) {
            handlePersonalPickupChange(personalPickupValue);
        }
    }, [personalPickupValue]);

    const handleTransportChange = (newTransportUuid: string | undefined) => {
        const newTransport = props.transports.find((transport) => transport.uuid === newTransportUuid);

        setTransport(newTransport);
        if (newTransport?.personalPickup && !isSelecting) {
            setIsSelecting(true);
        } else {
            updateUserDataCookie({ transportUuid: newTransportUuid });
        }
    };

    const handlePaymentChange = (newPaymentUuid: string | undefined) => {
        setPayment(transport?.payments.find((payment) => payment.uuid === newPaymentUuid));
        updateUserDataCookie({ paymentUuid: newPaymentUuid });
    };

    const handlePersonalPickupChange = (newPersonalPickupUuid: string | undefined) => {
        setPersonalPickup(transport?.stores.find((personalPickup) => personalPickup.uuid === newPersonalPickupUuid));
    };

    const onClosePersonalPickupPopupHandler = () => {
        formProviderMethods.setValue('transport', undefined);
        formProviderMethods.setValue('personalPickup', undefined);
        setIsSelecting(false);
    };

    const onConfirmPersonalPickupHandler = () => {
        updateUserDataCookie({ transportUuid: transportValue, personalPickupUuid: personalPickupValue });
        setIsSelecting(false);
    };

    const resetTransportAndPayment = () => {
        formProviderMethods.setValue('transport', undefined);
        formProviderMethods.setValue('payment', undefined);
        formProviderMethods.setValue('personalPickup', undefined);
        updateUserDataCookie({ personalPickupUuid: undefined });
    };

    const resetPayment = () => {
        formProviderMethods.setValue('payment', undefined);
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
                                        isActive={transport?.uuid === transportItem.uuid}
                                    >
                                        <Radiobutton
                                            name={'transport'}
                                            id={transportItem.uuid}
                                            value={transportItem.uuid}
                                            fieldRef={field}
                                            image={transportItem.image}
                                            disabled={
                                                transport?.uuid !== undefined && transportItem.uuid !== transport.uuid
                                            }
                                            checked={transport?.uuid === transportItem.uuid}
                                            uncheckCallback={resetTransportAndPayment}
                                            label={
                                                <SelectItemLabel
                                                    name={transportItem.name}
                                                    daysUntilDelivery={transportItem.daysUntilDelivery}
                                                    price={transportItem.price}
                                                    description={transportItem.description}
                                                    personalPickup={transportItem.personalPickup}
                                                    personalPickupStoreDetail={isSelecting ? undefined : personalPickup}
                                                />
                                            }
                                        />
                                    </ListItemStyled>
                                ))}
                            </ul>
                        )}
                    />
                    {transport !== undefined && (
                        <ResetButtonStyled type="button" onClick={resetTransportAndPayment}>
                            {t('Change transport type')}
                            <Icon icon="Arrow" />
                        </ResetButtonStyled>
                    )}
                </div>
                {transport !== undefined && (
                    <PaymentListWrapper>
                        <Heading type="h3">{t('Choose payment type')}</Heading>
                        <Controller
                            name="payment"
                            render={({ field }) => (
                                <ul>
                                    {transport.payments.map((paymentItem) => (
                                        <ListItemStyled
                                            key={paymentItem.uuid}
                                            isActive={payment?.uuid === paymentItem.uuid}
                                        >
                                            <Radiobutton
                                                name={'payment'}
                                                id={paymentItem.uuid}
                                                value={paymentItem.uuid}
                                                fieldRef={field}
                                                image={paymentItem.image}
                                                disabled={
                                                    payment?.uuid !== undefined && payment?.uuid !== paymentItem.uuid
                                                }
                                                checked={payment?.uuid === paymentItem.uuid}
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
                        {payment !== undefined && (
                            <ResetButtonStyled type="button" onClick={resetPayment}>
                                {t('Change payment type')}
                                <Icon icon="Arrow" />
                            </ResetButtonStyled>
                        )}
                    </PaymentListWrapper>
                )}
            </div>
            <Popup
                isVisible={transport !== undefined && isSelecting}
                onCloseCallback={onClosePersonalPickupPopupHandler}
                wrapperComponent={PersonalPickupPopupWrapperStyled}
            >
                <Heading type="h2">{t('Choose the store where you are going to pick up your order')}</Heading>
                <Controller
                    name="personalPickup"
                    render={({ field }) => (
                        <ul>
                            {transport?.stores.map((storeItem) => (
                                <ListItemStyled key={storeItem.uuid} isActive={personalPickup?.uuid === storeItem.uuid}>
                                    <Radiobutton
                                        name={'personalPickup'}
                                        id={storeItem.uuid}
                                        value={storeItem.uuid}
                                        fieldRef={field}
                                        checked={personalPickup?.uuid === storeItem.uuid}
                                        label={
                                            <SelectItemLabel
                                                name={storeItem.name}
                                                storeOpeningHours={storeItem.openingHours}
                                            />
                                        }
                                    />
                                </ListItemStyled>
                            ))}
                        </ul>
                    )}
                />
                <PopupButtonWrapperStyled>
                    <Button type="button" onClick={onClosePersonalPickupPopupHandler}>
                        {t('Close')}
                    </Button>
                    <Button
                        type="button"
                        isDisabled={personalPickup === undefined}
                        onClick={onConfirmPersonalPickupHandler}
                    >
                        {t('Confirm')}
                    </Button>
                </PopupButtonWrapperStyled>
            </Popup>
        </>
    );
};

export default Select;
