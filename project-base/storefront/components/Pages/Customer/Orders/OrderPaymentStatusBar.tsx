import useTranslation from 'next-translate/useTranslation';
import { twMergeCustom } from 'utils/twMerge';

type OrderPaymentStatusBarProps = {
    orderHasExternalPayment: boolean;
    orderIsPaid: boolean;
    orderHasPaymentInProcess: boolean;
};

const OrderPaymentStatus: FC<{
    orderIsPaid: boolean;
    orderHasPaymentInProcess: boolean;
}> = ({ orderIsPaid, orderHasPaymentInProcess }) => {
    const { t } = useTranslation();

    if (orderIsPaid) {
        return t('Paid');
    }

    if (orderHasPaymentInProcess) {
        return t('Processing');
    }

    return t('Not paid');
};

export const OrderPaymentStatusBar: FC<OrderPaymentStatusBarProps> = ({
    orderHasExternalPayment,
    orderIsPaid,
    className,
    orderHasPaymentInProcess,
}) => {
    if (!orderHasExternalPayment) {
        return null;
    }

    return (
        <div
            className={twMergeCustom(
                'text-textInverted self-start rounded-md p-1 text-xs font-normal',
                orderIsPaid ? 'bg-backgroundSuccess' : 'bg-backgroundError',
                className,
            )}
        >
            <OrderPaymentStatus orderHasPaymentInProcess={orderHasPaymentInProcess} orderIsPaid={orderIsPaid} />
        </div>
    );
};
