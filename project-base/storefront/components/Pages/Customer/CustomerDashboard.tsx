import { CountBadge } from 'components/Basic/CountBadge/CountBadge';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { DocumentIcon } from 'components/Basic/Icon/DocumentIcon';
import { OrderIcon } from 'components/Basic/Icon/OrderIcon';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { CustomerRecordColumnInfo } from 'components/Pages/Customer/CustomerRecordElements';
import { CustomerRecordStatusBadge } from 'components/Pages/Customer/CustomerRecordStatusBadge';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import {
    TypeDashboardComplaintsQuery,
    useDashboardComplaintsQuery,
} from 'graphql/requests/complaints/queries/DashboardComplaintsQuery.generated';
import {
    TypeDashboardOrdersQuery,
    useDashboardOrdersQuery,
} from 'graphql/requests/orders/queries/DashboardOrdersQuery.generated';
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
    const [{ data: ordersData, fetching: areOrdersFetching, error: ordersError }] = useDashboardOrdersQuery({
        pause: !showOrders,
        requestPolicy: 'cache-and-network',
    });
    const [{ data: complaintsData, fetching: areComplaintsFetching, error: complaintsError }] =
        useDashboardComplaintsQuery({ pause: !showComplaints, requestPolicy: 'cache-and-network' });

    return (
        <>
            <header className="flex flex-wrap items-center justify-between gap-4">
                <h1 className="h2">{title}</h1>
                <div className="flex flex-wrap gap-2">
                    <LinkButton href={profileUrl} size="large" type="editProfile" variant="secondary">
                        {profileLabel}
                    </LinkButton>
                    {canManageUsers && (
                        <LinkButton href={usersUrl} size="large" type="customer-users" variant="secondary">
                            {t('Customer users')}
                        </LinkButton>
                    )}
                </div>
            </header>
            <div className="grid grid-cols-[repeat(auto-fit,minmax(min(100%,19rem),1fr))] gap-4">
                {showOrders && (
                    <DashboardSection
                        title={t('Recent orders')}
                        records={mapConnectionEdges(ordersData?.orders?.edges)}
                        openLabel={t('Open orders')}
                        openCount={ordersData && countOpenOrders(ordersData.orderStatusCounts)}
                        hasError={!!ordersError}
                        fetching={areOrdersFetching}
                        emptyLabel={t('You have no orders')}
                        listUrl={ordersUrl}
                        listLabel={t('My orders')}
                        detailUrl={orderDetailUrl}
                        type="order"
                        numberLabel={t('Order number')}
                        dateLabel={t('Date of order')}
                        statusLabel={t('State')}
                        getDetailLabel={(orderNumber) =>
                            t('Go to order detail number {{ orderNumber }}', { ns: 'accessibility', orderNumber })
                        }
                    />
                )}
                {showComplaints && (
                    <DashboardSection
                        title={t('Recent complaints')}
                        records={mapConnectionEdges(complaintsData?.complaints.edges)}
                        openLabel={t('Open complaints')}
                        openCount={complaintsData && countOpenComplaints(complaintsData.complaintStatusCounts)}
                        hasError={!!complaintsError}
                        fetching={areComplaintsFetching}
                        emptyLabel={t('You have no complaints')}
                        listUrl={complaintsUrl}
                        listLabel={t('My complaints')}
                        detailUrl={complaintDetailUrl}
                        type="complaint"
                        numberLabel={t('Complaint number')}
                        dateLabel={t('Creation date')}
                        statusLabel={t('Status')}
                        getDetailLabel={(complaintNumber) =>
                            t('Go to complaint number {{complaintNumber}}', { ns: 'accessibility', complaintNumber })
                        }
                    />
                )}
            </div>
        </>
    );
};

type DashboardSectionProps = {
    title: string;
    records?: {
        number: string;
        status: string;
        statusType: TypeOrderStatusEnum | TypeComplaintStatusEnum;
        date: string;
    }[];
    openLabel: string;
    openCount?: number;
    hasError: boolean;
    fetching: boolean;
    emptyLabel: string;
    listUrl: string;
    listLabel: string;
    detailUrl: string;
    type: 'order' | 'complaint';
    numberLabel: string;
    dateLabel: string;
    statusLabel: string;
    getDetailLabel: (number: string) => string;
};

const dashboardRowClassName =
    'grid grid-cols-[repeat(auto-fit,minmax(min(100%,6rem),1fr))] items-center gap-3 rounded-xl bg-background-default p-4';

const DashboardRecordsSkeleton: FC = () => {
    const { t } = useTranslation();

    return (
        <div role="status">
            <span className="sr-only">{t('Page loading', { ns: 'accessibility' })}</span>
            <div aria-hidden="true" className="flex flex-col gap-2">
                {[0, 1, 2].map((row) => (
                    <div key={row} className={dashboardRowClassName}>
                        {['number', 'date', 'status'].map((column) => (
                            <div key={column} className="flex flex-col gap-2">
                                <Skeleton className="h-3 w-3/4" />
                                <Skeleton className="h-4 w-2/3" />
                            </div>
                        ))}
                    </div>
                ))}
            </div>
        </div>
    );
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
    type,
    numberLabel,
    dateLabel,
    statusLabel,
    getDetailLabel,
}) => {
    const { t } = useTranslation();
    const { formatDate } = useFormatDate();
    const Icon = type === 'order' ? OrderIcon : DocumentIcon;

    const isLoading = !hasError && fetching && !records;
    const hasRecords = !hasError && !!records?.length;
    const isEmpty = !hasError && !isLoading && !hasRecords;

    return (
        <section className="flex min-w-0 flex-col gap-5 rounded-xl bg-background-more p-5">
            <div className="flex flex-wrap items-center justify-between gap-3">
                <div className="flex items-center gap-3">
                    <span className="flex size-12 shrink-0 items-center justify-center rounded-xl bg-background-default text-icon-accent">
                        <Icon aria-hidden="true" className="size-6" />
                    </span>
                    <div>
                        <h2 className="h4">{title}</h2>
                        <p className="mt-1 flex items-center gap-2 font-secondary font-semibold text-sm text-text-less">
                            {openLabel}{' '}
                            <CountBadge className="bg-background-accent-less text-icon-accent">
                                {hasError || openCount === undefined ? '—' : openCount}
                            </CountBadge>
                        </p>
                    </div>
                </div>
                <LinkButton href={listUrl} size="large" type={`${type}List`} variant="secondary">
                    {listLabel}
                    <ArrowSecondaryIcon aria-hidden="true" className="size-4 -rotate-90" />
                </LinkButton>
            </div>
            {hasError && <p role="alert">{t('Something went wrong.')}</p>}
            {isLoading && <DashboardRecordsSkeleton />}
            {hasRecords && (
                <ul className="flex flex-col gap-2">
                    {records.map((record) => (
                        <li key={record.number} className={dashboardRowClassName}>
                            <CustomerRecordColumnInfo className="min-w-0 break-words" title={numberLabel}>
                                <ExtendedNextLink
                                    className="font-semibold"
                                    type={`${type}Detail`}
                                    aria-label={getDetailLabel(record.number)}
                                    href={{ pathname: detailUrl, query: { [`${type}Number`]: record.number } }}
                                >
                                    {record.number}
                                </ExtendedNextLink>
                            </CustomerRecordColumnInfo>
                            <CustomerRecordColumnInfo className="min-w-0" title={dateLabel}>
                                <time dateTime={record.date}>{formatDate(record.date)}</time>
                            </CustomerRecordColumnInfo>
                            <CustomerRecordColumnInfo className="min-w-0" title={statusLabel}>
                                <CustomerRecordStatusBadge status={record.status} statusType={record.statusType} />
                            </CustomerRecordColumnInfo>
                        </li>
                    ))}
                </ul>
            )}
            {isEmpty && <p>{emptyLabel}</p>}
        </section>
    );
};

const countOpenOrders = (counts: TypeDashboardOrdersQuery['orderStatusCounts']) =>
    counts.reduce(
        (sum, { status, count }) =>
            status.type === TypeOrderStatusEnum.New || status.type === TypeOrderStatusEnum.InProgress
                ? sum + count
                : sum,
        0,
    );

const countOpenComplaints = (counts: TypeDashboardComplaintsQuery['complaintStatusCounts']) =>
    counts.reduce(
        (sum, { status, count }) => (status.type !== TypeComplaintStatusEnum.Resolved ? sum + count : sum),
        0,
    );
