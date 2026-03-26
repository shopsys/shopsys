import { SpinnerIcon } from 'components/Basic/Icon/SpinnerIcon';
import { Loader } from 'components/Basic/Loader/Loader';
import { Button } from 'components/Forms/Button/Button';
import { GoPayGateway } from 'components/Pages/Order/PaymentConfirmation/Gateways/GoPayGateway';
import { useOrderAvailablePaymentsQuery } from 'graphql/requests/orders/queries/OrderAvailablePaymentsQuery.generated';
import { TypeSimplePaymentFragment } from 'graphql/requests/payments/fragments/SimplePaymentFragment.generated';
import { TypePaymentTypeEnum } from 'graphql/types';
import { useEffect, useMemo, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';
import { PaymentsInOrderSelectItem } from './PaymentsInOrderSelectItem';
import { useChangePaymentInOrder } from './paymentInOrderSelectUtils';

type PaymentsInOrderSelectProps = {
    className?: string;
    orderUuid: string;
    orderNumber?: string;
    paymentTransactionsCount?: number;
    withRedirectAfterChanging?: boolean;
    orderUrlHash?: string;
};

export const PaymentsInOrderSelect: FC<PaymentsInOrderSelectProps> = ({
    orderUuid,
    orderNumber,
    paymentTransactionsCount,
    withRedirectAfterChanging = true,
    className,
    orderUrlHash,
}) => {
    const { t } = useTranslation();

    const { isChangePaymentInOrderFetching, changePaymentInOrderHandler } = useChangePaymentInOrder();
    const [selectedPaymentSwiftForChange, setSelectedPaymentSwiftForChange] = useState<string | undefined | null>();
    const [selectedPaymentForChange, setSelectedPaymentForChange] = useState<TypeSimplePaymentFragment>();
    const [isGoPayVisible, setIsGoPayVisible] = useState(false);
    const [isGoPayMaxTransactionCountReached, setIsGoPayMaxTransactionCountReached] = useState(false);

    const [
        { data: orderAvailablePaymentsData, fetching: areOrderAvailablePaymentsFetching },
        reexecuteOrderAvailablePaymentsQuery,
    ] = useOrderAvailablePaymentsQuery({
        variables: { orderUuid },
    });

    const currentOrderPayment = orderAvailablePaymentsData?.orderPayments.currentPayment;
    const isCurrentOrderGoPayHidden =
        isGoPayMaxTransactionCountReached && currentOrderPayment?.type === TypePaymentTypeEnum.GoPay;
    const visibleCurrentOrderPayment = isCurrentOrderGoPayHidden ? null : currentOrderPayment;
    const availablePayments = useMemo(
        () =>
            orderAvailablePaymentsData?.orderPayments.availablePayments.filter(
                (payment) => !(isGoPayMaxTransactionCountReached && payment.type === TypePaymentTypeEnum.GoPay),
            ),
        [orderAvailablePaymentsData?.orderPayments.availablePayments, isGoPayMaxTransactionCountReached],
    );

    // Same payment UUID AND no SWIFT change = true repeat (same method, same bank)
    // If SWIFT differs, it's a bank change within GoPay → must go through ChangePaymentInOrder
    const isExactSamePaymentSelected =
        visibleCurrentOrderPayment &&
        selectedPaymentForChange?.uuid === visibleCurrentOrderPayment.uuid &&
        !selectedPaymentSwiftForChange;

    useEffect(() => {
        if (isGoPayMaxTransactionCountReached && currentOrderPayment?.type === TypePaymentTypeEnum.GoPay) {
            return;
        }

        setSelectedPaymentForChange(
            currentOrderPayment && currentOrderPayment.type === TypePaymentTypeEnum.GoPay
                ? currentOrderPayment
                : undefined,
        );
    }, [currentOrderPayment, isGoPayMaxTransactionCountReached]);

    useEffect(() => {
        if (!isGoPayMaxTransactionCountReached) {
            return;
        }

        const firstNonGoPayPayment = availablePayments?.find((payment) => payment.type !== TypePaymentTypeEnum.GoPay);

        setSelectedPaymentForChange(firstNonGoPayPayment);
        setSelectedPaymentSwiftForChange(undefined);
    }, [availablePayments, isGoPayMaxTransactionCountReached]);

    const handleMaxTransactionCountReached = () => {
        setIsGoPayVisible(false);
        setIsGoPayMaxTransactionCountReached(true);
        reexecuteOrderAvailablePaymentsQuery({ requestPolicy: 'network-only' });
    };

    const changePaymentSubmitHandler = async () => {
        if (selectedPaymentForChange?.uuid) {
            const changePaymentInOrderData = await changePaymentInOrderHandler(
                orderUuid,
                selectedPaymentForChange.uuid,
                selectedPaymentForChange.name,
                selectedPaymentForChange.type,
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

    if (
        (visibleCurrentOrderPayment && visibleCurrentOrderPayment.type !== TypePaymentTypeEnum.GoPay) ||
        !orderAvailablePaymentsData
    ) {
        return null;
    }

    if (availablePayments?.length === 0 && !visibleCurrentOrderPayment) {
        return null;
    }

    return (
        <div className={twMergeCustom('flex w-full flex-col items-center gap-6', className)}>
            <div className="flex w-full flex-col gap-4">
                <span className={twJoin('h3', !currentOrderPayment ? 'text-text-error' : '')}>
                    {visibleCurrentOrderPayment !== null
                        ? t('Repeat payment or change your payment method')
                        : t('Change order payment')}
                </span>
                <div className="flex w-full flex-col overflow-hidden rounded-md">
                    <ul className="w-full">
                        {visibleCurrentOrderPayment && (
                            <PaymentsInOrderSelectItem
                                payment={visibleCurrentOrderPayment}
                                selectedPaymentForChange={selectedPaymentForChange}
                                setSelectedPaymentForChange={setSelectedPaymentForChange}
                            />
                        )}

                        {availablePayments?.map((payment) => (
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

                {isExactSamePaymentSelected ? (
                    <GoPayGateway
                        requiresAction
                        className="ml-auto"
                        initialButtonText={t('Repeat payment')}
                        orderNumber={orderNumber}
                        orderUrlHash={orderUrlHash}
                        orderUuid={orderUuid}
                        paymentName={visibleCurrentOrderPayment.name}
                        paymentTransactionsCount={paymentTransactionsCount}
                        onMaxTransactionCountReached={handleMaxTransactionCountReached}
                    />
                ) : (
                    <div className="flex w-full flex-wrap items-center justify-center vl:justify-between gap-2 vl:text-left text-center">
                        <span className="text-text-less text-xs">
                            {t('The price of your order may change by the price of the payment')}
                        </span>

                        <div className="relative w-fit">
                            {isChangePaymentInOrderFetching && (
                                <Loader className="absolute inset-0 z-overlay flex h-full w-full items-center justify-center rounded-sm bg-background-more py-2 opacity-50" />
                            )}

                            <Button
                                hasDisabledLook={!selectedPaymentForChange}
                                size="xlarge"
                                onClick={changePaymentSubmitHandler}
                            >
                                {t('Pay with the selected method')}
                            </Button>
                        </div>
                    </div>
                )}

                {isGoPayVisible && (
                    <GoPayGateway
                        orderNumber={orderNumber}
                        orderUrlHash={orderUrlHash}
                        orderUuid={orderUuid}
                        paymentName={selectedPaymentForChange?.name}
                        paymentTransactionsCount={paymentTransactionsCount}
                        onMaxTransactionCountReached={handleMaxTransactionCountReached}
                    />
                )}
            </div>
        </div>
    );
};
