import { InfoIcon, SpinnerIcon } from 'components/Basic/Icon/IconsSvg';
import { Button } from 'components/Forms/Button/Button';
import { Radiobutton } from 'components/Forms/Radiobutton/Radiobutton';
import { GoPayGateway } from 'components/Pages/Order/PaymentConfirmation/Gateways/GoPayGateway';
import { TransportAndPaymentListItem } from 'components/Pages/Order/TransportAndPayment/TransportAndPaymentSelect/TransportAndPaymentListItem';
import { TransportAndPaymentSelectItemLabel } from 'components/Pages/Order/TransportAndPayment/TransportAndPaymentSelect/TransportAndPaymentSelectItemLabel';
import { useChangePaymentInOrder } from 'components/PaymentsInOrderSelect/helpers';
import { useOrderAvailablePaymentsQueryApi } from 'graphql/generated';
import useTranslation from 'next-translate/useTranslation';
import { useEffect, useState } from 'react';
import { PaymentTypeEnum } from 'types/payment';

type PaymentsInOrderSelectProps = {
    orderUuid: string;
    canPaymentBeRepeated: boolean;
    withRedirectAfterChanging?: boolean;
};

export const PaymentsInOrderSelect: FC<PaymentsInOrderSelectProps> = ({
    orderUuid,
    canPaymentBeRepeated,
    withRedirectAfterChanging,
}) => {
    const { t } = useTranslation();
    const { isChangePaymentInOrderFetching, changePaymentInOrderHandler } =
        useChangePaymentInOrder(withRedirectAfterChanging);
    const [selectedPaymentUuidForChange, setSelectedPaymentUuidForChange] = useState<string | undefined>();

    const [{ data: orderAvailablePaymentsData, fetching: isOrderAvailablePaymentsFetching }] =
        useOrderAvailablePaymentsQueryApi({
            variables: { orderUuid },
        });

    const currentOrderPayment = orderAvailablePaymentsData?.orderPayments.currentPayment;

    useEffect(() => {
        setSelectedPaymentUuidForChange(currentOrderPayment?.uuid);
    }, [currentOrderPayment?.uuid]);

    if (isOrderAvailablePaymentsFetching) {
        return <SpinnerIcon className="mx-auto mt-4 block w-12" />;
    }

    if (!orderAvailablePaymentsData || currentOrderPayment?.type !== PaymentTypeEnum.GoPay) {
        return null;
    }

    return (
        <div className="mt-6 flex flex-col items-center gap-6">
            {canPaymentBeRepeated && (
                <div className="flex w-full flex-col items-center">
                    <TransportAndPaymentListItem
                        key={currentOrderPayment.uuid}
                        className="order-none"
                        isActive={selectedPaymentUuidForChange === currentOrderPayment.uuid}
                    >
                        <Radiobutton
                            checked={currentOrderPayment.uuid === selectedPaymentUuidForChange}
                            id={currentOrderPayment.uuid}
                            name="payment"
                            value={currentOrderPayment.uuid}
                            label={
                                <TransportAndPaymentSelectItemLabel
                                    description={currentOrderPayment.description}
                                    image={currentOrderPayment.mainImage}
                                    name={currentOrderPayment.name}
                                    price={currentOrderPayment.price}
                                />
                            }
                            onChangeCallback={() => setSelectedPaymentUuidForChange(currentOrderPayment.uuid)}
                        />
                    </TransportAndPaymentListItem>
                    <GoPayGateway
                        requiresAction
                        className="mt-5"
                        initialButtonText={t('Repeat payment')}
                        isDisabled={selectedPaymentUuidForChange !== currentOrderPayment.uuid}
                        orderUuid={orderUuid}
                    />
                </div>
            )}
            <div className="flex w-full flex-col gap-3">
                <h2>{t('Change order payment')}</h2>
                <ul className="w-full">
                    {orderAvailablePaymentsData.orderPayments.availablePayments.map((payment) => (
                        <TransportAndPaymentListItem
                            key={payment.uuid}
                            isActive={selectedPaymentUuidForChange === payment.uuid}
                        >
                            <Radiobutton
                                checked={payment.uuid === selectedPaymentUuidForChange}
                                id={payment.uuid}
                                name="payment"
                                value={payment.uuid}
                                label={
                                    <TransportAndPaymentSelectItemLabel
                                        description={payment.description}
                                        image={payment.mainImage}
                                        name={payment.name}
                                        price={payment.price}
                                    />
                                }
                                onChangeCallback={() => setSelectedPaymentUuidForChange(payment.uuid)}
                            />
                        </TransportAndPaymentListItem>
                    ))}
                </ul>
                <div className="flex flex-col items-center gap-2">
                    <span className="flex items-center gap-2 text-sm text-greyLight vl:text-base">
                        {t('The price of your order may change by the price of the payment')}
                        <InfoIcon />
                    </span>
                    <Button
                        className="w-fit"
                        isDisabled={
                            !selectedPaymentUuidForChange || selectedPaymentUuidForChange === currentOrderPayment.uuid
                        }
                        onClick={() =>
                            selectedPaymentUuidForChange &&
                            changePaymentInOrderHandler(orderUuid, selectedPaymentUuidForChange)
                        }
                    >
                        {t('Pay with the selected method')}
                        {isChangePaymentInOrderFetching && <SpinnerIcon className="ml-2 w-5" />}
                    </Button>
                </div>
            </div>
        </div>
    );
};
