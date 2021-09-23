import { Controller, useFormContext } from 'react-hook-form';
import { FC, useEffect, useState } from 'react';
import { ListItemStyled, PaymentListWrapper, ResetButtonStyled } from './Select.style';
import Heading from 'components/Basic/Heading';
import Icon from 'components/Basic/Icon';
import Radiobutton from 'components/Forms/Radiobutton';
import SelectItemLabel from './SelectItemLabel';

type SelectProps = {};

const Select: FC<SelectProps> = (props) => {
    const [selectedTransport, selectTransport] = useState<undefined | typeof transports[0]>(undefined);
    const [selectedPayment, selectPayment] = useState<any>(undefined);
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

        const newpayment = selectedTransport.payments.find(
            (payment) => payment.uuid === formProviderMethods.watch('payment'),
        );
        selectPayment(newpayment || undefined);
    }, [formProviderMethods.watch('payment')]);

    const resetChoices = (all?: boolean) => {
        formProviderMethods.setValue('payment', undefined);
        if (all) {
            formProviderMethods.setValue('transport', undefined);
        }
    };

    return (
        <div>
            <div>
                <Heading type="h3">Vyberte způsob dopravy</Heading>
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
                                            selectedTransport !== undefined && selectedTransport.uuid !== transport.uuid
                                        }
                                        checked={selectedTransport?.uuid === transport.uuid}
                                        onSecondClickCallback={() => resetChoices(true)}
                                        label={
                                            <SelectItemLabel
                                                name={transport.name}
                                                daysUntilDelivery={transport.daysUntilDelivery}
                                                price={transport.price}
                                                personalPickup={transport.personalPickup}
                                                description={transport.description}
                                            />
                                        }
                                    />
                                </ListItemStyled>
                            ))}
                        </ul>
                    )}
                />
                {selectedTransport !== undefined && (
                    <ResetButtonStyled type="button" onClick={() => resetChoices(true)}>
                        Změnit dopravu <Icon icon="Arrow" />
                    </ResetButtonStyled>
                )}
            </div>
            {selectedTransport !== undefined && (
                <PaymentListWrapper>
                    <Heading type="h3">Vyberte způsob dopravy</Heading>
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
                                                selectedPayment !== undefined && selectedPayment.uuid !== payment.uuid
                                            }
                                            checked={selectedPayment?.uuid === payment.uuid}
                                            onSecondClickCallback={() => resetChoices()}
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
                        <ResetButtonStyled type="button" onClick={() => resetChoices()}>
                            Změnit platbu <Icon icon="Arrow" />
                        </ResetButtonStyled>
                    )}
                </PaymentListWrapper>
            )}
        </div>
    );
};

export default Select;
