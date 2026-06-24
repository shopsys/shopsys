import { DatePicker } from 'components/Forms/DatePicker/DatePicker';
import { TextInput } from 'components/Forms/TextInput/TextInput';
import { OrderStatusCount, OrdersStatusTabs } from 'components/Pages/Customer/Orders/OrdersStatusTabs';
import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';
import { UrlQueries } from 'types/urlQueries';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { hasActiveOrderListFiltersFromUrlQuery } from 'utils/orders/getOrdersFilterFromUrlQuery';
import { getStringFromUrlQuery } from 'utils/parsing/getStringFromUrlQuery';
import {
    ORDER_CREATED_AFTER_QUERY_PARAMETER_NAME,
    ORDER_CREATED_BEFORE_QUERY_PARAMETER_NAME,
    ORDER_STATUS_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
    SEARCH_QUERY_PARAMETER_NAME,
} from 'utils/queryParamNames';
import { pushQueries } from 'utils/queryParams/pushQueries';
import { useDebounce } from 'utils/useDebounce';

const ORDER_FILTER_SEARCH_DEBOUNCE_DELAY = 500;

type OrdersFilterFormValues = {
    search: string;
    createdAfter: string;
    createdBefore: string;
};

type OrdersDateFilterName = 'createdAfter' | 'createdBefore';

type OrdersFilterProps = {
    orderStatusCounts: OrderStatusCount[];
};

export const OrdersFilter: FC<OrdersFilterProps> = ({ orderStatusCounts }) => {
    const { t } = useTranslation();
    const router = useRouter();
    const [values, setValues] = useState<OrdersFilterFormValues>(() =>
        getOrdersFilterFormValuesFromUrlQuery(router.query),
    );
    const debouncedSearch = useDebounce(values.search, ORDER_FILTER_SEARCH_DEBOUNCE_DELAY);
    const hasActiveFilters = hasActiveOrderListFiltersFromUrlQuery(router.query);

    useEffect(() => {
        setValues(getOrdersFilterFormValuesFromUrlQuery(router.query));
    }, [
        router.query[ORDER_CREATED_AFTER_QUERY_PARAMETER_NAME],
        router.query[ORDER_CREATED_BEFORE_QUERY_PARAMETER_NAME],
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

    const updateValue = (name: keyof OrdersFilterFormValues, value: string) => {
        const nextValues = {
            ...values,
            [name]: value,
        };

        setValues(nextValues);
    };

    const updateDateValue = (name: OrdersDateFilterName, value: string) => {
        const nextValues = {
            ...values,
            [name]: value,
        };

        setValues(nextValues);
        pushFilterValues(router, nextValues);
    };

    const clearFilters = () => {
        setValues(emptyOrdersFilterFormValues);
        pushFilterValues(router, emptyOrdersFilterFormValues, true);
    };

    return (
        <>
            <div className="rounded-xl bg-background-more p-4">
                <div className="grid vl:grid-cols-2 gap-3 xl:grid-cols-[2fr_1fr_1fr]">
                    <TextInput
                        id="order-search"
                        label={t('Search by number or product')}
                        name="order-search"
                        type="search"
                        value={values.search}
                        onChange={(event) => updateValue('search', event.currentTarget.value)}
                    />

                    <DatePicker
                        id="order-created-after"
                        label={t('Date from')}
                        name="order-created-after"
                        value={values.createdAfter}
                        onChange={(value) => updateDateValue('createdAfter', value)}
                    />

                    <DatePicker
                        id="order-created-before"
                        label={t('Date to')}
                        name="order-created-before"
                        value={values.createdBefore}
                        onChange={(value) => updateDateValue('createdBefore', value)}
                    />
                </div>
            </div>

            <div className="flex items-center gap-3">
                <div className="min-w-0 flex-1">
                    <OrdersStatusTabs orderStatusCounts={orderStatusCounts} />
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

const emptyOrdersFilterFormValues: OrdersFilterFormValues = {
    search: '',
    createdAfter: '',
    createdBefore: '',
};

const getOrdersFilterFormValuesFromUrlQuery = (query: UrlQueries): OrdersFilterFormValues => ({
    search: getStringFromUrlQuery(query[SEARCH_QUERY_PARAMETER_NAME]),
    createdAfter: getStringFromUrlQuery(query[ORDER_CREATED_AFTER_QUERY_PARAMETER_NAME]),
    createdBefore: getStringFromUrlQuery(query[ORDER_CREATED_BEFORE_QUERY_PARAMETER_NAME]),
});

const pushFilterValues = (
    router: ReturnType<typeof useRouter>,
    values: OrdersFilterFormValues,
    shouldClearStatus = false,
) => {
    pushQueries(router, {
        ...(router.query as UrlQueries),
        [PAGE_QUERY_PARAMETER_NAME]: undefined,
        [SEARCH_QUERY_PARAMETER_NAME]: values.search || undefined,
        [ORDER_CREATED_AFTER_QUERY_PARAMETER_NAME]: values.createdAfter || undefined,
        [ORDER_CREATED_BEFORE_QUERY_PARAMETER_NAME]: values.createdBefore || undefined,
        ...(shouldClearStatus ? { [ORDER_STATUS_QUERY_PARAMETER_NAME]: undefined } : {}),
    });
};
