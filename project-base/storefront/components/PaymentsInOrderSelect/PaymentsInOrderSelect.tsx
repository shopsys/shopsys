import { SpinnerIcon } from 'components/Basic/Icon/SpinnerIcon';
import { Button } from 'components/Forms/Button/Button';
import { GoPayGateway } from 'components/Pages/Order/PaymentConfirmation/Gateways/GoPayGateway';
import { useOrderAvailablePaymentsQuery } from 'graphql/requests/orders/queries/OrderAvailablePaymentsQuery.generated';
import { TypeSimplePaymentFragment } from 'graphql/requests/payments/fragments/SimplePaymentFragment.generated';
import { TypePaymentTypeEnum } from 'graphql/types';
import { useEffect, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';
import { PaymentsInOrderSelectItem } from './PaymentsInOrderSelectItem';
import { useChangePaymentInOrder } from './paymentInOrderSelectUtils';

type PaymentsInOrderSelectProps = {
    orderUuid: string;
    orderUrlHash?: string | null;
    withRedirectAfterChanging?: boolean;
};

export const PaymentsInOrderSelect: FC<PaymentsInOrderSelectProps> = ({
    orderUuid,
    orderUrlHash,
    withRedirectAfterChanging,
    className,
}) => {
    const { t } = useTranslation();

    const { isChangePaymentInOrderFetching, changePaymentInOrderHandler } = useChangePaymentInOrder();
    const [selectedPaymentSwiftForChange, setSelectedPaymentSwiftForChange] = useState<string | undefined | null>();
    const [selectedPaymentForChange, setSelectedPaymentForChange] = useState<TypeSimplePaymentFragment>();
    const [isGoPayVisible, setIsGoPayVisible] = useState(false);

    const [{ data: orderAvailablePaymentsData, fetching: areOrderAvailablePaymentsFetching }] =
        useOrderAvailablePaymentsQuery({
            variables: { orderUuid, orderUrlHash: orderUrlHash ?? null },
        });

    const currentOrderPayment = orderAvailablePaymentsData?.orderPayments.currentPayment;
    const isSelectedPaymentEqualsToOrderPayment =
        currentOrderPayment && selectedPaymentForChange?.uuid === currentOrderPayment.uuid;
    const availablePayments = orderAvailablePaymentsData?.orderPayments.availablePayments;

    useEffect(() => {
        setSelectedPaymentForChange(
            currentOrderPayment && currentOrderPayment.type === TypePaymentTypeEnum.GoPay
                ? currentOrderPayment
                : undefined,
        );
    }, [currentOrderPayment]);

    const changePaymentSubmitHandler = async () => {
        if (selectedPaymentForChange?.uuid) {
            const changePaymentInOrderData = await changePaymentInOrderHandler(
                orderUuid,
                orderUrlHash ?? null,
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

    if (
        (currentOrderPayment && currentOrderPayment.type !== TypePaymentTypeEnum.GoPay) ||
        !orderAvailablePaymentsData
    ) {
        return null;
    }

    if (availablePayments?.length === 0 && !currentOrderPayment) {
        return null;
    }

    return (
        <div className={twMergeCustom('flex w-full flex-col items-center gap-6', className)}>
            <div className="flex w-full flex-col gap-4">
                <span className={twJoin('h3', !currentOrderPayment ? 'text-text-error' : '')}>
                    {currentOrderPayment !== null
                        ? t('Repeat payment or change your payment method')
                        : t('Change order payment')}
                </span>
                <div className="flex w-full flex-col overflow-hidden rounded-md">
                    <ul className="w-full">
                        {currentOrderPayment && (
                            <PaymentsInOrderSelectItem
                                payment={currentOrderPayment}
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
                <div className="flex w-full flex-col vl:items-end items-center gap-2">
                    {isSelectedPaymentEqualsToOrderPayment ? (
                        <GoPayGateway
                            requiresAction
                            className="ml-auto"
                            initialButtonText={t('Repeat payment')}
                            orderUrlHash={orderUrlHash}
                            orderUuid={orderUuid}
                        />
                    ) : (
                        <>
                            <span className="text-text-less text-xs">
                                {t('The price of your order may change by the price of the payment')}
                            </span>

                            <Button
                                className="w-fit"
                                hasDisabledLook={!selectedPaymentForChange}
                                size="xlarge"
                                onClick={changePaymentSubmitHandler}
                            >
                                {t('Pay with the selected method')}
                                {isChangePaymentInOrderFetching && <SpinnerIcon className="ml-2 w-5" />}
                            </Button>
                        </>
                    )}
                    {isGoPayVisible && <GoPayGateway orderUrlHash={orderUrlHash} orderUuid={orderUuid} />}
                </div>
            </div>
        </div>
    );
};
