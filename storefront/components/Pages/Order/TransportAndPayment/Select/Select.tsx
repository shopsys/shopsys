import { Controller, useFormContext } from 'react-hook-form';
import { FC, useEffect, useState } from 'react';
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
};

const Select: FC<SelectProps> = (props) => {
    const t = useTypedTranslationFunction();
    const [selectedTransport, selectTransport] = useState<undefined | TransportType>(undefined);
    const [selectedPersonalPickup, selectPersonalPickup] = useState<undefined | StoreType>(undefined);
    const [selectedPayment, selectPayment] = useState<undefined | PaymentType>(undefined);
    const [isPersonalPickupPopupVisible, setPersonalPickupPopupVisibility] = useState(false);
    const formProviderMethods = useFormContext();
    useEffect(() => {
        const newTransport = props.transports.find(
            (transport) => transport.uuid === formProviderMethods.watch('transport'),
        );

        selectTransport(newTransport || undefined);
    }, [formProviderMethods.watch('transport')]);
    useEffect(() => {
        if (selectedTransport === undefined) {
            return;
        }

        const newPayment = selectedTransport.payments.find(
            (payment) => payment.uuid === formProviderMethods.watch('payment'),
        );

        selectPayment(newPayment || undefined);
    }, [formProviderMethods.watch('payment')]);
    useEffect(() => {
        if (selectedTransport === undefined) {
            return;
        }

        const newPersonalPickup = selectedTransport.stores.find(
            (store) => store.uuid === formProviderMethods.watch('personalPickup'),
        );

        selectPersonalPickup(newPersonalPickup || undefined);
    }, [formProviderMethods.watch('personalPickup')]);
    useEffect(() => {
        if (selectedTransport?.personalPickup) {
            setPersonalPickupPopupVisibility(true);
        }
    }, [selectedTransport?.personalPickup]);

    const resetChoices = (resetFieldNames: ('payment' | 'personalPickup' | 'transport')[]) => {
        for (const name of resetFieldNames) {
            formProviderMethods.setValue(name, undefined);
        }
    };

    const onClosePersonalPickupPopupHandler = () => {
        formProviderMethods.setValue('transport', undefined);
        formProviderMethods.setValue('personalPickup', undefined);
        selectPersonalPickup(undefined);
        setPersonalPickupPopupVisibility(false);
    };

    const onConfirmPersonalPickupHandler = () => {
        setPersonalPickupPopupVisibility(false);
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
                                {props.transports.map((transport) => (
                                    <ListItemStyled
                                        key={transport.uuid}
                                        isActive={selectedTransport?.uuid === transport.uuid}
                                    >
                                        <Radiobutton
                                            name={'transport'}
                                            id={transport.uuid}
                                            value={transport.uuid}
                                            fieldRef={field}
                                            image={transport.image}
                                            disabled={
                                                selectedTransport !== undefined &&
                                                selectedTransport.uuid !== transport.uuid
                                            }
                                            checked={selectedTransport?.uuid === transport.uuid}
                                            onSecondClickCallback={() =>
                                                resetChoices(['payment', 'transport', 'personalPickup'])
                                            }
                                            label={
                                                <SelectItemLabel
                                                    name={transport.name}
                                                    daysUntilDelivery={transport.daysUntilDelivery}
                                                    price={transport.price}
                                                    description={transport.description}
                                                    personalPickup={transport.personalPickup}
                                                    personalPickupStoreDetail={
                                                        isPersonalPickupPopupVisible
                                                            ? undefined
                                                            : selectedPersonalPickup
                                                    }
                                                />
                                            }
                                        />
                                    </ListItemStyled>
                                ))}
                            </ul>
                        )}
                    />
                    {selectedTransport !== undefined && (
                        <ResetButtonStyled
                            type="button"
                            onClick={() => resetChoices(['payment', 'transport', 'personalPickup'])}
                        >
                            {t('Change transport type')}
                            <Icon icon="Arrow" />
                        </ResetButtonStyled>
                    )}
                </div>
                {selectedTransport !== undefined && (
                    <PaymentListWrapper>
                        <Heading type="h3">{t('Choose payment type')}</Heading>
                        <Controller
                            name="payment"
                            render={({ field }) => (
                                <ul>
                                    {selectedTransport.payments.map((payment) => (
                                        <ListItemStyled
                                            key={payment.uuid}
                                            isActive={selectedPayment?.uuid === payment.uuid}
                                        >
                                            <Radiobutton
                                                name={'payment'}
                                                id={payment.uuid}
                                                value={payment.uuid}
                                                fieldRef={field}
                                                image={payment.image}
                                                disabled={
                                                    selectedPayment !== undefined &&
                                                    selectedPayment.uuid !== payment.uuid
                                                }
                                                checked={selectedPayment?.uuid === payment.uuid}
                                                onSecondClickCallback={() => resetChoices(['payment'])}
                                                label={
                                                    <SelectItemLabel
                                                        name={payment.name}
                                                        price={payment.price}
                                                        description={payment.description}
                                                    />
                                                }
                                            />
                                        </ListItemStyled>
                                    ))}
                                </ul>
                            )}
                        />
                        {selectedPayment !== undefined && (
                            <ResetButtonStyled type="button" onClick={() => resetChoices(['payment'])}>
                                {t('Change payment type')}
                                <Icon icon="Arrow" />
                            </ResetButtonStyled>
                        )}
                    </PaymentListWrapper>
                )}
            </div>
            <Popup
                isVisible={isPersonalPickupPopupVisible && selectedTransport !== undefined}
                onCloseCallback={onClosePersonalPickupPopupHandler}
                wrapperComponent={PersonalPickupPopupWrapperStyled}
            >
                <Heading type="h2">{t('Choose the store where you are going to pick up your order')}</Heading>
                <Controller
                    name="personalPickup"
                    render={({ field }) => (
                        <ul>
                            {selectedTransport?.stores.map((store) => (
                                <ListItemStyled key={store.uuid} isActive={selectedPersonalPickup?.uuid === store.uuid}>
                                    <Radiobutton
                                        name={'personalPickup'}
                                        id={store.uuid}
                                        value={store.uuid}
                                        fieldRef={field}
                                        checked={selectedPersonalPickup?.uuid === store.uuid}
                                        label={
                                            <SelectItemLabel name={store.name} storeOpeningHours={store.openingHours} />
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
                        isDisabled={selectedPersonalPickup === undefined}
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
