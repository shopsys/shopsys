import { HorizontalScrollHint } from 'components/Basic/HorizontalScrollHint/HorizontalScrollHint';
import { Tag } from 'components/Basic/Tag/Tag';
import { useRouter } from 'next/router';
import { UrlQueries } from 'types/urlQueries';
import { getComplaintStatusCodeFromUrlQuery } from 'utils/complaints/getComplaintsFilterFromUrlQuery';
import { ComplaintStatusCount } from 'utils/complaints/useComplaintsData';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { COMPLAINT_STATUS_QUERY_PARAMETER_NAME, PAGE_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';
import { pushQueries } from 'utils/queryParams/pushQueries';
import { twMergeCustom } from 'utils/twMerge';

type ComplaintsStatusTabsProps = {
    complaintStatusCounts: ComplaintStatusCount[];
};

export const ComplaintsStatusTabs: FC<ComplaintsStatusTabsProps> = ({ complaintStatusCounts }) => {
    const { t } = useTranslation();
    const router = useRouter();
    const activeStatusCode = getComplaintStatusCodeFromUrlQuery(router.query[COMPLAINT_STATUS_QUERY_PARAMETER_NAME]);
    const statusTabs = getStatusTabs(t, complaintStatusCounts, activeStatusCode);

    const changeStatus = (statusCode: string | null) => {
        pushQueries(router, {
            ...(router.query as UrlQueries),
            [PAGE_QUERY_PARAMETER_NAME]: undefined,
            [COMPLAINT_STATUS_QUERY_PARAMETER_NAME]: statusCode ?? undefined,
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
                                <span
                                    className={twMergeCustom(
                                        'rounded-full px-2 py-0.5 text-xs',
                                        isActive
                                            ? 'bg-background-default text-link-default'
                                            : 'bg-fill-accent-less text-link-default',
                                    )}
                                >
                                    {statusTab.count}
                                </span>
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
    complaintStatusCounts: ComplaintStatusCount[],
    activeStatusCode: string | null,
) => {
    const allComplaintsCount = complaintStatusCounts.reduce((totalCount, { count }) => totalCount + count, 0);

    return [
        { statusCode: null, label: t('All'), count: allComplaintsCount },
        ...complaintStatusCounts
            .filter(({ statusCode, count }) => count > 0 || statusCode === activeStatusCode)
            .map(({ statusCode, label, count }) => ({
                statusCode,
                label,
                count,
            })),
    ];
};
