import useTranslation from 'utils/i18n/useTranslationWrapper';
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
                'w-fit self-start rounded-md p-1 font-normal text-text-inverted text-xs',
                orderIsPaid ? 'bg-background-success' : 'bg-background-error',
                className,
            )}
        >
            <OrderPaymentStatus orderHasPaymentInProcess={orderHasPaymentInProcess} orderIsPaid={orderIsPaid} />
        </div>
    );
};
