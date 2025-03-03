import { InfoIconInCircle } from 'components/Basic/Icon/InfoIconInCircle';
import useTranslation from 'next-translate/useTranslation';
import { PaymentTypeEnum } from 'types/payment';
import { twMergeCustom } from 'utils/twMerge';

type OrderPaymentStatusBarProps = {
    orderPaymentType: string;
    orderIsPaid: boolean;
    orderHasPaymentInProcess: boolean;
};

const OrderPaymentStatusContent: FC<{ title: string; iconClassName?: string }> = ({ title, iconClassName }) => (
    <div className="flex items-center gap-2">
        <InfoIconInCircle className={twMergeCustom('text-backgroundWarningMore size-4', iconClassName)} />
        {title}
    </div>
);

const OrderPaymentStatus: FC<{
    orderIsPaid: boolean;
    orderHasPaymentInProcess: boolean;
}> = ({ orderIsPaid, orderHasPaymentInProcess }) => {
    const { t } = useTranslation();

    if (orderIsPaid) {
        return <OrderPaymentStatusContent iconClassName="text-backgroundSuccessMore" title={t('The order was paid')} />;
    }

    if (orderHasPaymentInProcess) {
        return <OrderPaymentStatusContent title={t('The order is awaiting payment verification.')} />;
    }

    return <OrderPaymentStatusContent title={t('The order has not been paid')} />;
};

export const OrderPaymentStatusBar: FC<OrderPaymentStatusBarProps> = ({
    orderPaymentType,
    orderIsPaid,
    className,
    orderHasPaymentInProcess,
}) => {
    if (orderPaymentType !== PaymentTypeEnum.GoPay) {
        return null;
    }

    return (
        <div
            className={twMergeCustom(
                'flex gap-2 rounded-md p-2',
                orderIsPaid ? 'bg-backgroundSuccess text-textInverted' : 'bg-backgroundWarning',
                className,
            )}
        >
            <OrderPaymentStatus orderHasPaymentInProcess={orderHasPaymentInProcess} orderIsPaid={orderIsPaid} />
        </div>
    );
};
