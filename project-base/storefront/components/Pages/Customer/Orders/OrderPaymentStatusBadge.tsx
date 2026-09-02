import { CheckmarkIcon } from 'components/Basic/Icon/CheckmarkIcon';
import { ClockIcon } from 'components/Basic/Icon/ClockIcon';
import { CloseIcon } from 'components/Basic/Icon/CloseIcon';
import { StatusBadge, StatusBadgeVariant } from 'components/Basic/StatusBadge/StatusBadge';
import { type ElementType } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type OrderPaymentStatusBadgeProps = {
    orderHasExternalPayment: boolean;
    orderIsPaid: boolean;
    orderHasPaymentInProcess: boolean;
};

export const OrderPaymentStatusBadge: FC<OrderPaymentStatusBadgeProps> = ({
    orderHasExternalPayment,
    orderIsPaid,
    orderHasPaymentInProcess,
    className,
}) => {
    const { t } = useTranslation();

    if (!orderHasExternalPayment) {
        return null;
    }

    let icon: ElementType = CloseIcon;
    let label = t('Not paid');
    let variant: StatusBadgeVariant = 'error';

    if (orderIsPaid) {
        icon = CheckmarkIcon;
        label = t('Paid');
        variant = 'success';
    } else if (orderHasPaymentInProcess) {
        icon = ClockIcon;
        label = t('Processing');
        variant = 'warning';
    }

    return (
        <StatusBadge className={className} icon={icon} variant={variant}>
            {label}
        </StatusBadge>
    );
};
