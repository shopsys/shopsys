import { DatePicker } from 'components/Forms/DatePicker/DatePicker';
import { TextInput } from 'components/Forms/TextInput/TextInput';
import { ComplaintsStatusTabs } from 'components/Pages/Customer/Complaints/ComplaintsStatusTabs';
import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';
import { UrlQueries } from 'types/urlQueries';
import { hasActiveComplaintListFiltersFromUrlQuery } from 'utils/complaints/getComplaintsFilterFromUrlQuery';
import { ComplaintStatusCount } from 'utils/complaints/useComplaintsData';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getStringFromUrlQuery } from 'utils/parsing/getStringFromUrlQuery';
import {
    COMPLAINT_CREATED_AFTER_QUERY_PARAMETER_NAME,
    COMPLAINT_CREATED_BEFORE_QUERY_PARAMETER_NAME,
    COMPLAINT_STATUS_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
    SEARCH_QUERY_PARAMETER_NAME,
} from 'utils/queryParamNames';
import { pushQueries } from 'utils/queryParams/pushQueries';
import { useDebounce } from 'utils/useDebounce';

const COMPLAINT_FILTER_SEARCH_DEBOUNCE_DELAY = 500;

type ComplaintsFilterFormValues = {
    search: string;
    createdAfter: string;
    createdBefore: string;
};

type ComplaintsDateFilterName = 'createdAfter' | 'createdBefore';

type ComplaintsFilterProps = {
    complaintStatusCounts: ComplaintStatusCount[];
};

export const ComplaintsFilter: FC<ComplaintsFilterProps> = ({ complaintStatusCounts }) => {
    const { t } = useTranslation();
    const router = useRouter();
    const [values, setValues] = useState<ComplaintsFilterFormValues>(() =>
        getComplaintsFilterFormValuesFromUrlQuery(router.query),
    );
    const debouncedSearch = useDebounce(values.search, COMPLAINT_FILTER_SEARCH_DEBOUNCE_DELAY);
    const hasActiveFilters = hasActiveComplaintListFiltersFromUrlQuery(router.query);

    useEffect(() => {
        setValues(getComplaintsFilterFormValuesFromUrlQuery(router.query));
    }, [
        router.query[COMPLAINT_CREATED_AFTER_QUERY_PARAMETER_NAME],
        router.query[COMPLAINT_CREATED_BEFORE_QUERY_PARAMETER_NAME],
        router.query[SEARCH_QUERY_PARAMETER_NAME],
    ]);

    useEffect(() => {
        if (debouncedSearch !== values.search) {
            return;
        }

        if (debouncedSearch === getStringFromUrlQuery(router.query[SEARCH_QUERY_PARAMETER_NAME])) {
            return;
        }

        pushFilterValues(router, {
            ...values,
            search: debouncedSearch,
        });
    }, [debouncedSearch, router, values]);

    const updateValue = (name: keyof ComplaintsFilterFormValues, value: string) => {
        const nextValues = {
            ...values,
            [name]: value,
        };

        setValues(nextValues);
    };

    const updateDateValue = (name: ComplaintsDateFilterName, value: string) => {
        const nextValues = {
            ...values,
            [name]: value,
        };

        setValues(nextValues);
        pushFilterValues(router, nextValues);
    };

    const clearFilters = () => {
        setValues(emptyComplaintsFilterFormValues);
        pushFilterValues(router, emptyComplaintsFilterFormValues, true);
    };

    return (
        <>
            <div className="rounded-xl bg-background-more p-4">
                <div className="grid vl:grid-cols-2 gap-3 xl:grid-cols-[2fr_1fr_1fr]">
                    <TextInput
                        id="complaint-search"
                        label={t('Search by number or product')}
                        name="complaint-search"
                        type="search"
                        value={values.search}
                        onChange={(event) => updateValue('search', event.currentTarget.value)}
                    />

                    <DatePicker
                        id="complaint-created-after"
                        label={t('Date from')}
                        name="complaint-created-after"
                        value={values.createdAfter}
                        onChange={(value) => updateDateValue('createdAfter', value)}
                    />

                    <DatePicker
                        id="complaint-created-before"
                        label={t('Date to')}
                        name="complaint-created-before"
                        value={values.createdBefore}
                        onChange={(value) => updateDateValue('createdBefore', value)}
                    />
                </div>
            </div>

            <div className="flex items-center gap-3">
                <div className="min-w-0 flex-1">
                    <ComplaintsStatusTabs complaintStatusCounts={complaintStatusCounts} />
                </div>

                {hasActiveFilters && (
                    <button
                        className="shrink-0 cursor-pointer rounded-sm font-secondary font-semibold text-link-default text-sm underline hover:text-link-hovered"
                        type="button"
                        onClick={clearFilters}
                    >
                        {t('Clear filters')}
                    </button>
                )}
            </div>
        </>
    );
};

const emptyComplaintsFilterFormValues: ComplaintsFilterFormValues = {
    search: '',
    createdAfter: '',
    createdBefore: '',
};

const getComplaintsFilterFormValuesFromUrlQuery = (query: UrlQueries): ComplaintsFilterFormValues => ({
    search: getStringFromUrlQuery(query[SEARCH_QUERY_PARAMETER_NAME]),
    createdAfter: getStringFromUrlQuery(query[COMPLAINT_CREATED_AFTER_QUERY_PARAMETER_NAME]),
    createdBefore: getStringFromUrlQuery(query[COMPLAINT_CREATED_BEFORE_QUERY_PARAMETER_NAME]),
});

const pushFilterValues = (
    router: ReturnType<typeof useRouter>,
    values: ComplaintsFilterFormValues,
    shouldClearStatus = false,
) => {
    pushQueries(router, {
        ...(router.query as UrlQueries),
        [PAGE_QUERY_PARAMETER_NAME]: undefined,
        [SEARCH_QUERY_PARAMETER_NAME]: values.search || undefined,
        [COMPLAINT_CREATED_AFTER_QUERY_PARAMETER_NAME]: values.createdAfter || undefined,
        [COMPLAINT_CREATED_BEFORE_QUERY_PARAMETER_NAME]: values.createdBefore || undefined,
        ...(shouldClearStatus ? { [COMPLAINT_STATUS_QUERY_PARAMETER_NAME]: undefined } : {}),
    });
};
