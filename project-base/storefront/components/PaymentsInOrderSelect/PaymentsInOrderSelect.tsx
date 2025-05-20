import { PaymentsInOrderSelectItem } from './PaymentsInOrderSelectItem';
import { useChangePaymentInOrder } from './paymentInOrderSelectUtils';
import { SpinnerIcon } from 'components/Basic/Icon/SpinnerIcon';
import { Button } from 'components/Forms/Button/Button';
import { GoPayGateway } from 'components/Pages/Order/PaymentConfirmation/Gateways/GoPayGateway';
import { useOrderAvailablePaymentsQuery } from 'graphql/requests/orders/queries/OrderAvailablePaymentsQuery.generated';
import { TypeSimplePaymentFragment } from 'graphql/requests/payments/fragments/SimplePaymentFragment.generated';
import { TypePaymentTypeEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { useEffect, useState } from 'react';
import { useIsPaymentByCardAvailable } from 'utils/cart/useIsPaymentByCardAvailable';
import { twMergeCustom } from 'utils/twMerge';

type PaymentsInOrderSelectProps = {
    orderUuid: string;
    withRedirectAfterChanging?: boolean;
    paymentTransactionCount: number;
};

export const PaymentsInOrderSelect: FC<PaymentsInOrderSelectProps> = ({
    orderUuid,
    withRedirectAfterChanging,
    paymentTransactionCount,
    className,
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
    const isSelectedPaymentEqualsToOrderPayment = selectedPaymentForChange?.uuid === currentOrderPayment?.uuid;
    const filteredAvailablePayments = orderAvailablePaymentsData?.orderPayments.availablePayments.filter(
        (payment) => payment.type !== TypePaymentTypeEnum.GoPay || isPaymentByCardAvailable,
    );

    useEffect(() => {
        setSelectedPaymentForChange(
            !isPaymentByCardAvailable && currentOrderPayment?.type === TypePaymentTypeEnum.GoPay
                ? undefined
                : currentOrderPayment,
        );
    }, [currentOrderPayment?.uuid]);

    const changePaymentSubmitHandler = async () => {
        if (selectedPaymentForChange?.uuid) {
            const changePaymentInOrderData = await changePaymentInOrderHandler(
                orderUuid,
                selectedPaymentForChange.uuid,
                selectedPaymentForChange.name,
                selectedPaymentSwiftForChange,
                selectedPaymentForChange.type !== TypePaymentTypeEnum.GoPay && withRedirectAfterChanging,
            );
            if (
                selectedPaymentForChange.type === TypePaymentTypeEnum.GoPay &&
                changePaymentInOrderData?.ChangePaymentInOrder
            ) {
                setIsGoPayVisible(true);
            }
        }
    };

    if (areOrderAvailablePaymentsFetching) {
        return <SpinnerIcon className="mx-auto mt-4 block w-12" />;
    }

    if (currentOrderPayment?.type !== TypePaymentTypeEnum.GoPay || !orderAvailablePaymentsData) {
        return null;
    }

    return (
        <div className={twMergeCustom('flex w-full flex-col items-center gap-6', className)}>
            {!!filteredAvailablePayments?.length && (
                <div className="flex w-full flex-col gap-4">
                    <h3 className={isPaymentByCardAvailable ? 'text-text-error' : ''}>
                        {isPaymentByCardAvailable
                            ? t('Repeat payment or change your payment method')
                            : t('Change order payment')}
                    </h3>

                    <div className="flex w-full flex-col overflow-hidden rounded-md">
                        <ul className="w-full">
                            {isPaymentByCardAvailable && (
                                <PaymentsInOrderSelectItem
                                    payment={currentOrderPayment}
                                    selectedPaymentForChange={selectedPaymentForChange}
                                    setSelectedPaymentForChange={setSelectedPaymentForChange}
                                />
                            )}

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
                    </div>

                    <div className="vl:justify-between vl:text-left flex w-full flex-wrap items-center justify-center gap-2 text-center">
                        {isSelectedPaymentEqualsToOrderPayment ? (
                            <GoPayGateway
                                requiresAction
                                className="ml-auto"
                                initialButtonText={t('Repeat payment')}
                                orderUuid={orderUuid}
                            />
                        ) : (
                            <>
                                <span className="text-text-less text-xs">
                                    {t('The price of your order may change by the price of the payment')}
                                </span>

                                <Button
                                    className="w-fit"
                                    isDisabled={!selectedPaymentForChange}
                                    size="xlarge"
                                    onClick={changePaymentSubmitHandler}
                                >
                                    {t('Pay with the selected method')}
                                    {isChangePaymentInOrderFetching && <SpinnerIcon className="ml-2 w-5" />}
                                </Button>
                            </>
                        )}
                        {isGoPayVisible && <GoPayGateway orderUuid={orderUuid} />}
                    </div>
                </div>
            )}
        </div>
    );
};
