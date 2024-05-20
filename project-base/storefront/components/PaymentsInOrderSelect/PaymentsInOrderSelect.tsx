import { PaymentsInOrderSelectItem } from './PaymentsInOrderSelectItem';
import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { SpinnerIcon } from 'components/Basic/Icon/SpinnerIcon';
import { Button } from 'components/Forms/Button/Button';
import { GoPayGateway } from 'components/Pages/Order/PaymentConfirmation/Gateways/GoPayGateway';
import { useChangePaymentInOrder } from 'components/PaymentsInOrderSelect/utils';
import { useOrderAvailablePaymentsQuery } from 'graphql/requests/orders/queries/OrderAvailablePaymentsQuery.generated';
import { TypeSimplePaymentFragment } from 'graphql/requests/payments/fragments/SimplePaymentFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { useEffect, useState } from 'react';
import { PaymentTypeEnum } from 'types/payment';
import { useIsPaymentByCardAvailable } from 'utils/cart/useIsPaymentByCardAvailable';

type PaymentsInOrderSelectProps = {
    orderUuid: string;
    withRedirectAfterChanging?: boolean;
    paymentTransactionCount: number;
};

export const PaymentsInOrderSelect: FC<PaymentsInOrderSelectProps> = ({
    orderUuid,
    withRedirectAfterChanging,
    paymentTransactionCount,
}) => {
    const { t } = useTranslation();

    const { isChangePaymentInOrderFetching, changePaymentInOrderHandler } = useChangePaymentInOrder();
    const [selectedPaymentSwiftForChange, setSelectedPaymentSwiftForChange] = useState<string | undefined | null>();
    const [selectedPaymentForChange, setSelectedPaymentForChange] = useState<TypeSimplePaymentFragment>();
    const [isGoPayVisible, setIsGoPayVisible] = useState(false);

    const [{ data: orderAvailablePaymentsData, fetching: areOrderAvailablePaymentsFetching }] =
        useOrderAvailablePaymentsQuery({
            variables: { orderUuid },
        });

    const isPaymentByCardAvailable = useIsPaymentByCardAvailable(paymentTransactionCount);
    const currentOrderPayment = orderAvailablePaymentsData?.orderPayments.currentPayment;
    const isSelectedPaymentEqualToOrderPayment = selectedPaymentForChange?.uuid === currentOrderPayment?.uuid;
    const filteredAvailablePayments = orderAvailablePaymentsData?.orderPayments.availablePayments.filter(
        (payment) => payment.type !== PaymentTypeEnum.GoPay || isPaymentByCardAvailable,
    );

    useEffect(() => {
        setSelectedPaymentForChange(currentOrderPayment);
    }, [currentOrderPayment?.uuid]);

    const changePaymentSubmitHandler = async () => {
        if (selectedPaymentForChange?.uuid) {
            const changePaymentInOrderData = await changePaymentInOrderHandler(
                orderUuid,
                selectedPaymentForChange.uuid,
                selectedPaymentSwiftForChange,
                selectedPaymentForChange.type !== PaymentTypeEnum.GoPay && withRedirectAfterChanging,
            );
            if (
                selectedPaymentForChange.type === PaymentTypeEnum.GoPay &&
                changePaymentInOrderData?.ChangePaymentInOrder
            ) {
                setIsGoPayVisible(true);
            }
        }
    };

    if (areOrderAvailablePaymentsFetching) {
        return <SpinnerIcon className="mx-auto mt-4 block w-12" />;
    }

    if (currentOrderPayment?.type !== PaymentTypeEnum.GoPay || !orderAvailablePaymentsData) {
        return null;
    }

    return (
        <div className="mt-6 flex w-full flex-col items-center gap-6">
            {isPaymentByCardAvailable && (
                <div className="flex w-full flex-col items-center">
                    <PaymentsInOrderSelectItem
                        payment={currentOrderPayment}
                        selectedPaymentForChange={selectedPaymentForChange}
                        setSelectedPaymentForChange={setSelectedPaymentForChange}
                    />
                    <GoPayGateway
                        requiresAction
                        className="mt-5"
                        initialButtonText={t('Repeat payment')}
                        isDisabled={selectedPaymentForChange?.uuid !== currentOrderPayment.uuid}
                        orderUuid={orderUuid}
                    />
                </div>
            )}
            {!!filteredAvailablePayments?.length && (
                <div className="flex w-full flex-col gap-3">
                    <h2>{t('Change order payment')}</h2>
                    <ul className="w-full">
                        {filteredAvailablePayments.map((payment) => (
                            <PaymentsInOrderSelectItem
                                key={payment.uuid}
                                payment={payment}
                                selectedPaymentForChange={selectedPaymentForChange}
                                selectedPaymentSwiftForChange={selectedPaymentSwiftForChange}
                                setSelectedPaymentForChange={setSelectedPaymentForChange}
                                setSelectedPaymentSwiftForChange={setSelectedPaymentSwiftForChange}
                            />
                        ))}
                    </ul>
                    <div className="flex flex-col items-center gap-2">
                        <span className="flex items-center gap-2 text-sm text-graySlate vl:text-base">
                            {t('The price of your order may change by the price of the payment')}
                            <InfoIcon />
                        </span>
                        <Button
                            className="w-fit"
                            isDisabled={!selectedPaymentForChange || isSelectedPaymentEqualToOrderPayment}
                            onClick={changePaymentSubmitHandler}
                        >
                            {t('Pay with the selected method')}
                            {isChangePaymentInOrderFetching && <SpinnerIcon className="ml-2 w-5" />}
                        </Button>
                        {isGoPayVisible && <GoPayGateway orderUuid={orderUuid} />}
                    </div>
                </div>
            )}
        </div>
    );
};
