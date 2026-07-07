import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { CustomerRecordColumnInfo } from 'components/Pages/Customer/CustomerRecordElements';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeOrderDetailFragment } from 'graphql/requests/orders/fragments/OrderDetailFragment.generated';
import { TypeOrderStatusEnum } from 'graphql/types';
import Trans from 'next-translate/Trans';
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

    const [newComplaintUrl, withdrawalFormUrl] = getInternationalizedStaticUrls(
        ['/customer/new-complaint', { url: '/order-withdrawal/:orderUrlHash', param: order.urlHash }],
        url,
    );

    if (!canRequestWithdrawal && !hasWithdrawalRequested && !isCancelled && !withdrawalDeadline) {
        return null;
    }

    if (hasWithdrawalRequested) {
        return (
            <div className="flex flex-col gap-2.5 rounded-xl border-1 border-toast-border-warning bg-toast-bg-warning p-5">
                <p className="h4">{t('Withdrawal request was submitted')}</p>

                <div className="flex vl:flex-row flex-col flex-wrap gap-x-10 gap-y-5">
                    <CustomerRecordColumnInfo title={t('Requested on')}>
                        {formatDate(withdrawalRequest.requestedAt)}
                    </CustomerRecordColumnInfo>
                    <CustomerRecordColumnInfo title={t('Contact person')}>
                        {withdrawalRequest.firstName} {withdrawalRequest.lastName}
                    </CustomerRecordColumnInfo>
                    <CustomerRecordColumnInfo title={t('Email')}>{withdrawalRequest.email}</CustomerRecordColumnInfo>
                    {withdrawalRequest.telephone && (
                        <CustomerRecordColumnInfo title={t('Phone')}>
                            {withdrawalRequest.telephone}
                        </CustomerRecordColumnInfo>
                    )}
                    {withdrawalRequest.note && (
                        <CustomerRecordColumnInfo title={t('Note')}>{withdrawalRequest.note}</CustomerRecordColumnInfo>
                    )}
                </div>
            </div>
        );
    }

    if (canRequestWithdrawal) {
        return (
            <div className="flex flex-col items-center gap-2">
                <p className="mx-auto max-w-[520px] text-balance text-center text-sm">
                    {t('You can withdraw from this order within 14 days of delivery.')}
                </p>
                <LinkButton href={withdrawalFormUrl} size="small" type="order-withdrawal" variant="inverted">
                    {t('Request withdrawal')}
                </LinkButton>
            </div>
        );
    }

    if (isCancelled) {
        return null;
    }

    return (
        <div className="flex items-center gap-2 rounded-xl border-1 border-toast-border-warning bg-toast-bg-warning p-5">
            <InfoIcon className="size-5 text-icon-warning" />
            <p className="text-sm">
                {t('Withdrawal deadline expired on {{ date }}', {
                    date: formatDate(withdrawalDeadline!),
                })}
                .
                {isUserLoggedIn && canCreateComplaint && order.customerUser !== null && (
                    <>
                        {' '}
                        <Trans
                            defaultTrans="If you have an issue with this order, you can create a <lnk>complaint</lnk>."
                            i18nKey="OrderWithdrawalComplaintInfo"
                            components={{
                                lnk: (
                                    <ExtendedNextLink
                                        aria-label={t('Go to create complaint page', { ns: 'accessibility' })}
                                        className="inline font-secondary font-semibold text-sm"
                                        href={newComplaintUrl}
                                        type="complaintNew"
                                    />
                                ),
                            }}
                        />
                    </>
                )}
            </p>
        </div>
    );
};
