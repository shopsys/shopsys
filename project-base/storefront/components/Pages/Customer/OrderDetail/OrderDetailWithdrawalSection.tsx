import { OrderDetailRowInfo } from './OrderDetailBasicInfo';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeOrderDetailFragment } from 'graphql/requests/orders/fragments/OrderDetailFragment.generated';
import { TypeOrderStatusEnum } from 'graphql/types';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useFormatDate } from 'utils/formatting/useFormatDate';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

type OrderDetailWithdrawalSectionProps = {
    order: TypeOrderDetailFragment;
};

export const OrderDetailWithdrawalSection: FC<OrderDetailWithdrawalSectionProps> = ({ order }) => {
    const { t } = useTranslation();
    const { formatDate } = useFormatDate();
    const { canCreateComplaint, canRequestWithdrawal: userCanRequestWithdrawal } = useAuthorization();
    const { url } = useDomainConfig();
    const isUserLoggedIn = useIsUserLoggedIn();

    const orderCanRequestWithdrawal = order.canRequestWithdrawal;
    const canRequestWithdrawal = userCanRequestWithdrawal && orderCanRequestWithdrawal;
    const withdrawalDeadline = order.withdrawalDeadline;
    const withdrawalRequest = order.withdrawalRequest;
    const hasWithdrawalRequested = withdrawalRequest !== null;
    const isCancelled = order.statusType === TypeOrderStatusEnum.Canceled;

    const withdrawalTitle = t('Withdrawal from contract');

    const [newComplaintUrl, withdrawalFormUrl] = getInternationalizedStaticUrls(
        ['/customer/new-complaint', { url: '/order-withdrawal/:orderUrlHash', param: order.urlHash }],
        url,
    );

    if (!canRequestWithdrawal && !hasWithdrawalRequested && !isCancelled && !withdrawalDeadline) {
        return null;
    }

    if (hasWithdrawalRequested) {
        return (
            <OrderDetailRowInfo title={withdrawalTitle}>
                <div className="flex flex-col gap-2">
                    <span>
                        {t('Withdrawal was requested on {{ date }} with the following data:', {
                            date: formatDate(withdrawalRequest.requestedAt),
                        })}
                    </span>
                    <div className="text-sm">
                        <div>
                            <strong>{t('Contact person')}:</strong> {withdrawalRequest.firstName}{' '}
                            {withdrawalRequest.lastName}
                        </div>
                        <div>
                            <strong>{t('Email')}:</strong> {withdrawalRequest.email}
                        </div>
                        {withdrawalRequest.telephone && (
                            <div>
                                <strong>{t('Phone')}:</strong> {withdrawalRequest.telephone}
                            </div>
                        )}
                        {withdrawalRequest.note && (
                            <div className="mt-2">
                                <strong>{t('Note')}:</strong>
                                <div className="mt-1">{withdrawalRequest.note}</div>
                            </div>
                        )}
                    </div>
                </div>
            </OrderDetailRowInfo>
        );
    }

    if (canRequestWithdrawal) {
        return (
            <OrderDetailRowInfo title={withdrawalTitle}>
                <div className="flex flex-col gap-2">
                    <LinkButton href={withdrawalFormUrl} type="order-withdrawal" variant="inverted">
                        {t('Withdraw from contract')}
                    </LinkButton>
                </div>
            </OrderDetailRowInfo>
        );
    }

    if (isCancelled) {
        return (
            <OrderDetailRowInfo title={withdrawalTitle}>
                <div className="flex flex-col gap-2">
                    <span>{t('Withdrawal is not possible for cancelled orders')}</span>
                </div>
            </OrderDetailRowInfo>
        );
    }

    return (
        <OrderDetailRowInfo title={withdrawalTitle}>
            <div className="flex flex-col gap-2">
                <span>
                    {t('Withdrawal deadline expired on {{ date }}', {
                        date: formatDate(withdrawalDeadline!),
                    })}
                </span>
                {isUserLoggedIn && canCreateComplaint && (
                    <ExtendedNextLink
                        aria-label={t('Go to create complaint page', { ns: 'accessibility' })}
                        href={newComplaintUrl}
                        type="complaintNew"
                    >
                        {t('If you want to complain, you can create a complaint')}
                    </ExtendedNextLink>
                )}
            </div>
        </OrderDetailRowInfo>
    );
};
