import { CountBadge } from 'components/Basic/CountBadge/CountBadge';
import { HorizontalScrollHint } from 'components/Basic/HorizontalScrollHint/HorizontalScrollHint';
import { Tag } from 'components/Basic/Tag/Tag';
import { useRouter } from 'next/router';
import { UrlQueries } from 'types/urlQueries';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getOrderStatusCodeFromUrlQuery } from 'utils/orders/getOrdersFilterFromUrlQuery';
import { ORDER_STATUS_QUERY_PARAMETER_NAME, PAGE_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';
import { pushQueries } from 'utils/queryParams/pushQueries';

export type OrderStatusCount = {
    statusCode: string;
    label: string;
    count: number;
};

type OrdersStatusTabsProps = {
    orderStatusCounts: OrderStatusCount[];
};

export const OrdersStatusTabs: FC<OrdersStatusTabsProps> = ({ orderStatusCounts }) => {
    const { t } = useTranslation();
    const router = useRouter();
    const activeStatusCode = getOrderStatusCodeFromUrlQuery(router.query[ORDER_STATUS_QUERY_PARAMETER_NAME]);
    const statusTabs = getStatusTabs(t, orderStatusCounts, activeStatusCode);

    const changeStatus = (statusCode: string | null) => {
        pushQueries(router, {
            ...(router.query as UrlQueries),
            [PAGE_QUERY_PARAMETER_NAME]: undefined,
            [ORDER_STATUS_QUERY_PARAMETER_NAME]: statusCode ?? undefined,
        });
    };

    return (
        <HorizontalScrollHint
            render={(scrollContainerRef) => (
                <div ref={scrollContainerRef} className="flex gap-3 overflow-x-auto">
                    {statusTabs.map((statusTab) => {
                        const isActive = statusTab.statusCode === activeStatusCode;

                        return (
                            <Tag
                                key={statusTab.statusCode ?? 'all'}
                                ariaPressed={isActive}
                                isActive={isActive}
                                onClick={() => changeStatus(statusTab.statusCode)}
                            >
                                <span>{statusTab.label}</span>
                                <CountBadge
                                    className={
                                        isActive
                                            ? 'bg-background-default text-link-default'
                                            : 'bg-fill-accent-less text-link-default'
                                    }
                                >
                                    {statusTab.count ?? 0}
                                </CountBadge>
                            </Tag>
                        );
                    })}
                </div>
            )}
        />
    );
};

const getStatusTabs = (
    t: ReturnType<typeof useTranslation>['t'],
    orderStatusCounts: OrderStatusCount[],
    activeStatusCode: string | null,
) => {
    const allOrdersCount = orderStatusCounts.reduce((totalCount, { count }) => totalCount + count, 0);

    return [
        { statusCode: null, label: t('All'), count: allOrdersCount },
        ...orderStatusCounts
            .filter(({ statusCode, count }) => count > 0 || statusCode === activeStatusCode)
            .map(({ statusCode, label, count }) => ({
                statusCode,
                label,
                count,
            })),
    ];
};
