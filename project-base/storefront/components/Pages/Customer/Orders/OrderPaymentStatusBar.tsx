import { InfoIconInCircle } from 'components/Basic/Icon/InfoIconInCircle';
import useTranslation from 'next-translate/useTranslation';
import { PaymentTypeEnum } from 'types/payment';
import { twMergeCustom } from 'utils/twMerge';

type OrderPaymentStatusBarProps = {
    orderPaymentType: string;
    orderIsPaid: boolean;
};

export const OrderPaymentStatusBar: FC<OrderPaymentStatusBarProps> = ({ orderPaymentType, orderIsPaid, className }) => {
    const { t } = useTranslation();
    return (
        <>
            {orderPaymentType === PaymentTypeEnum.GoPay && (
                <div
                    className={twMergeCustom(
                        'p-2 rounded-md flex gap-2',
                        orderIsPaid ? 'bg-backgroundSuccess text-textInverted' : 'bg-backgroundWarning',
                        className,
                    )}
                >
                    {orderIsPaid ? (
                        <>
                            <InfoIconInCircle className="w-4 text-backgroundSuccessMore" />
                            {t('The order was paid')}
                        </>
                    ) : (
                        <>
                            <InfoIconInCircle className="w-4 text-backgroundWarningMore" />
                            {t('The order has not been paid')}
                        </>
                    )}
                </div>
            )}
        </>
    );
};
