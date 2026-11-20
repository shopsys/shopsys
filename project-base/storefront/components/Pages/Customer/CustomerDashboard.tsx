import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { DocumentIcon } from 'components/Basic/Icon/DocumentIcon';
import { OrderIcon } from 'components/Basic/Icon/OrderIcon';
import { Loader } from 'components/Basic/Loader/Loader';
import { StatusBadge } from 'components/Basic/StatusBadge/StatusBadge';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useDashboardComplaintsQuery } from 'graphql/requests/complaints/queries/DashboardComplaintsQuery.generated';
import { useDashboardOrdersQuery } from 'graphql/requests/orders/queries/DashboardOrdersQuery.generated';
import { TypeComplaintStatusEnum, TypeOrderStatusEnum } from 'graphql/types';
import { useFormatDate } from 'utils/formatting/useFormatDate';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { mapConnectionEdges } from 'utils/mappers/connection';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { useUserProfileSectionLabel } from 'utils/user/useUserProfileSectionLabel';

export const CustomerDashboard: FC<{ title: string }> = ({ title }) => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const {
        currentCustomerUserUuid,
        canCreateOrder,
        canViewCompanyOrders,
        canCreateComplaint,
        canViewCompanyComplaints,
        canManageUsers,
    } = useAuthorization();
    const profileLabel = useUserProfileSectionLabel();
    const [ordersUrl, orderDetailUrl, complaintsUrl, complaintDetailUrl, profileUrl, usersUrl] =
        getInternationalizedStaticUrls(
            [
                '/customer/orders',
                '/customer/order-detail',
                '/customer/complaints',
                '/customer/complaint-detail',
                '/customer/edit-profile',
                '/customer/users',
            ],
            url,
        );
    const showOrders = !!currentCustomerUserUuid && (canCreateOrder || canViewCompanyOrders);
    const showComplaints = !!currentCustomerUserUuid && (canCreateComplaint || canViewCompanyComplaints);
    const [orders] = useDashboardOrdersQuery({ pause: !showOrders, requestPolicy: 'cache-and-network' });
    const [complaints] = useDashboardComplaintsQuery({ pause: !showComplaints, requestPolicy: 'cache-and-network' });

    return (
        <>
            <header className="flex flex-wrap items-center justify-between gap-4">
                <h1 className="h2">{title}</h1>
                <div className="flex flex-wrap gap-2">
                    <LinkButton href={profileUrl} size="small" type="editProfile" variant="secondary">
                        {profileLabel}
                    </LinkButton>
                    {canManageUsers && (
                        <LinkButton href={usersUrl} size="small" type="customer-users" variant="secondary">
                            {t('Customer users')}
                        </LinkButton>
                    )}
                </div>
            </header>
            <div className="grid grid-cols-[repeat(auto-fit,minmax(min(100%,19rem),1fr))] gap-4">
                {showOrders && (
                    <DashboardSection
                        title={t('Recent orders')}
                        records={mapConnectionEdges(orders.data?.orders?.edges)}
                        openLabel={t('Open orders')}
                        openCount={orders.data?.orderStatusCounts.reduce(
                            (sum, { status, count }) =>
                                status.type === TypeOrderStatusEnum.New ||
                                status.type === TypeOrderStatusEnum.InProgress
                                    ? sum + count
                                    : sum,
                            0,
                        )}
                        hasError={!!orders.error}
                        fetching={orders.fetching}
                        emptyLabel={t('You have no orders')}
                        listUrl={ordersUrl}
                        listLabel={t('My orders')}
                        detailUrl={orderDetailUrl}
                        numberParam="orderNumber"
                    />
                )}
                {showComplaints && (
                    <DashboardSection
                        title={t('Recent complaints')}
                        records={mapConnectionEdges(complaints.data?.complaints.edges)}
                        openLabel={t('Open complaints')}
                        openCount={complaints.data?.complaintStatusCounts.reduce(
                            (sum, { status, count }) =>
                                status.type === TypeComplaintStatusEnum.Resolved ? sum : sum + count,
                            0,
                        )}
                        hasError={!!complaints.error}
                        fetching={complaints.fetching}
                        emptyLabel={t('You have no complaints')}
                        listUrl={complaintsUrl}
                        listLabel={t('My complaints')}
                        detailUrl={complaintDetailUrl}
                        numberParam="complaintNumber"
                    />
                )}
            </div>
        </>
    );
};

type DashboardSectionProps = {
    title: string;
    records?: { number: string; status: string; date: string }[];
    openLabel: string;
    openCount?: number;
    hasError: boolean;
    fetching: boolean;
    emptyLabel: string;
    listUrl: string;
    listLabel: string;
    detailUrl: string;
    numberParam: 'orderNumber' | 'complaintNumber';
};

const DashboardSection: FC<DashboardSectionProps> = ({
    title,
    records,
    openLabel,
    openCount,
    hasError,
    fetching,
    emptyLabel,
    listUrl,
    listLabel,
    detailUrl,
    numberParam,
}) => {
    const { t } = useTranslation();
    const { formatDate } = useFormatDate();
    const Icon = numberParam === 'orderNumber' ? OrderIcon : DocumentIcon;

    return (
        <section className="flex min-w-0 flex-col rounded-2xl border border-border-less">
            <div className="flex items-center justify-between gap-3 rounded-t-2xl bg-background-accent-less px-5 py-5 sm:px-6">
                <p className="flex flex-col gap-1 font-secondary font-semibold text-sm">
                    {openLabel}:{' '}
                    <strong className="text-4xl tabular-nums tracking-tight">
                        {hasError || openCount === undefined ? '—' : openCount}
                    </strong>
                </p>
                <span className="flex size-12 items-center justify-center rounded-xl bg-background-default text-icon-accent">
                    <Icon aria-hidden="true" className="size-7" />
                </span>
            </div>
            <div className="flex flex-1 flex-col gap-2 px-5 pt-5 pb-3 sm:px-6">
                <h2 className="h4">{title}</h2>
                {hasError ? (
                    <p role="alert">{t('Something went wrong.')}</p>
                ) : fetching && !records ? (
                    <div role="status">
                        <Loader className="size-5" />
                        <span className="sr-only">{t('Page loading', { ns: 'accessibility' })}</span>
                    </div>
                ) : records?.length ? (
                    <ul className="divide-y divide-border-less">
                        {records.map((record) => (
                            <li
                                key={record.number}
                                className="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-x-3 gap-y-1 py-3"
                            >
                                <ExtendedNextLink
                                    type={numberParam === 'orderNumber' ? 'orderDetail' : 'complaintDetail'}
                                    aria-label={
                                        numberParam === 'orderNumber'
                                            ? t('Go to order detail number {{ orderNumber }}', {
                                                  ns: 'accessibility',
                                                  orderNumber: record.number,
                                              })
                                            : t('Go to complaint number {{complaintNumber}}', {
                                                  ns: 'accessibility',
                                                  complaintNumber: record.number,
                                              })
                                    }
                                    href={{ pathname: detailUrl, query: { [numberParam]: record.number } }}
                                >
                                    {record.number}
                                </ExtendedNextLink>
                                <time className="col-start-1 row-start-2 text-sm text-text-less" dateTime={record.date}>
                                    {formatDate(record.date)}
                                </time>
                                <StatusBadge
                                    className="col-start-2 row-span-2 row-start-1 max-w-36 text-wrap break-words"
                                    variant="neutral"
                                >
                                    {record.status}
                                </StatusBadge>
                            </li>
                        ))}
                    </ul>
                ) : (
                    <p>{emptyLabel}</p>
                )}
            </div>
            <ExtendedNextLink
                className="flex items-center justify-between gap-3 rounded-b-2xl border-border-less border-t px-5 py-4 font-semibold no-underline hover:bg-background-more hover:underline sm:px-6"
                href={listUrl}
                type={numberParam === 'orderNumber' ? 'orderList' : 'complaintList'}
            >
                {listLabel}
                <ArrowSecondaryIcon aria-hidden="true" className="size-4 -rotate-90" />
            </ExtendedNextLink>
        </section>
    );
};
