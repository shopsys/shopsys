import { usePaginationContext } from '../Pagination/usePaginationContext';
import { useProductFilterOptions } from '../Product/Filter/FilterContext/useFilterState';
import { Icon } from 'components/Basic/Icon/Icon';
import { isElementVisible } from 'components/Helpers/isElementVisible';
import { mobileFirstSizes } from 'components/Theme/mediaQueries';
import { ProductOrderingModeEnumApi } from 'graphql/generated';
import { getFilterUrlQueryForSortingInSeoCategory } from 'helpers/filterOptions/getFilterUrlQueryForSortingInSeoCategory';
import { getQueryWithoutAllParameter } from 'helpers/filterOptions/getQueryWithoutAllParameter';
import { shallowReplaceIfDifferent } from 'helpers/filterOptions/shallowReplaceIfDifferent';
import { FILTER_QUERY_PARAMETER_NAME, SORT_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
import { getProductListSort } from 'helpers/sorting/getProductListSort';
import { parseProductListSortFromQuery } from 'helpers/sorting/parseProductListSortFromQuery';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useGetWindowSize } from 'hooks/ui/useGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/useResizeWidthEffect';
import { useRouter } from 'next/router';
import { useCallback, useEffect, useState } from 'react';
import { twJoin } from 'tailwind-merge';

type SortingBarProps = {
    totalCount: number;
    sorting: ProductOrderingModeEnumApi | null;
};

const TEST_IDENTIFIER = 'blocks-sortingbar';

export const SortingBar: FC<SortingBarProps> = ({ sorting, totalCount }) => {
    const router = useRouter();
    const t = useTypedTranslationFunction();
    const [, dispatch] = usePaginationContext();
    const sortingFromQuery = getProductListSort(parseProductListSortFromQuery(router.query[SORT_QUERY_PARAMETER_NAME]));
    const [selectedSort, setSelectedSort] = useState<ProductOrderingModeEnumApi | null>(sorting ?? sortingFromQuery);
    const { width } = useGetWindowSize();
    const [isMobileSortBarVisible, setMobileSortBarVisible] = useState(true);
    const productFilterOptions = useProductFilterOptions();
    useResizeWidthEffect(
        width,
        mobileFirstSizes.vl,
        () => setMobileSortBarVisible(false),
        () => setMobileSortBarVisible(true),
        () => setMobileSortBarVisible(isElementVisible([{ min: 0, max: 1024 }], width)),
    );
    const [toggleSortMenu, setToggleSortMenu] = useState(false);
    const sortValues = [
        { stateValue: ProductOrderingModeEnumApi.PriorityApi, displayValue: t('priority') },
        { stateValue: ProductOrderingModeEnumApi.PriceAscApi, displayValue: t('price ascending') },
        { stateValue: ProductOrderingModeEnumApi.PriceDescApi, displayValue: t('price descending') },
    ];

    const isNewSortDifferentThanCurrent = (
        currentSort: ProductOrderingModeEnumApi | null,
        newSort: ProductOrderingModeEnumApi,
    ) => currentSort !== newSort;

    useEffect(() => {
        setSelectedSort(sorting);
    }, [sorting]);

    const updateUrlWithCurrentSort = useCallback(
        (sort: string) => {
            const pathname = router.asPath.split('?')[0];
            const queryParams = getQueryWithoutAllParameter(router);

            queryParams[SORT_QUERY_PARAMETER_NAME] = sort;

            const filterUrlQuery =
                productFilterOptions !== null ? getFilterUrlQueryForSortingInSeoCategory(productFilterOptions) : null;

            if (filterUrlQuery !== null) {
                queryParams[FILTER_QUERY_PARAMETER_NAME] = filterUrlQuery;
            }

            shallowReplaceIfDifferent(router, { pathname, query: queryParams });
        },
        [router, productFilterOptions],
    );

    const onSelectSortMenu = useCallback(
        (currentSort: ProductOrderingModeEnumApi | null, newSort: ProductOrderingModeEnumApi) => () => {
            setToggleSortMenu((prev) => !prev);
            if (isNewSortDifferentThanCurrent(currentSort, newSort)) {
                updateUrlWithCurrentSort(newSort);
                setSelectedSort(newSort);
            }
        },
        [updateUrlWithCurrentSort],
    );

    const onMobileSort = useCallback(
        (currentSort: ProductOrderingModeEnumApi | null, newSort: ProductOrderingModeEnumApi) => () => {
            if (isNewSortDifferentThanCurrent(currentSort, newSort)) {
                setToggleSortMenu((prev) => !prev);
                updateUrlWithCurrentSort(newSort);
                setSelectedSort(newSort);
                dispatch({ type: 'resetPagination' });
            }
        },
        [dispatch, updateUrlWithCurrentSort],
    );

    const onSort = useCallback(
        (currentSort: ProductOrderingModeEnumApi | null, newSort: ProductOrderingModeEnumApi) => () => {
            if (isNewSortDifferentThanCurrent(currentSort, newSort)) {
                updateUrlWithCurrentSort(newSort);
                setSelectedSort(newSort);
                dispatch({ type: 'resetPagination' });
            }
        },
        [dispatch, updateUrlWithCurrentSort],
    );

    return (
        <div className="relative h-12 w-full sm:w-44 vl:inline-block vl:h-9 vl:w-full" data-testid={TEST_IDENTIFIER}>
            <div className="absolute top-0 left-0 z-above flex w-full flex-col rounded-xl bg-border vl:top-1 vl:flex-row vl:items-center vl:justify-between vl:rounded-none vl:bg-opacity-0">
                {isMobileSortBarVisible ? (
                    <>
                        <SortingBarItem>
                            {sortValues
                                .filter((value) => value.stateValue === selectedSort)
                                .map((value) => (
                                    <div
                                        className="flex items-center justify-center py-1"
                                        key={value.stateValue}
                                        onClick={onSelectSortMenu(selectedSort, value.stateValue)}
                                        data-testid={TEST_IDENTIFIER + '-selected'}
                                    >
                                        <Icon
                                            iconType="icon"
                                            icon="Sort"
                                            width={21}
                                            height={14}
                                            className="align-middle"
                                        />
                                        <div className="pl-2 text-justify font-bold text-dark">
                                            <div className="uppercase">{t('Sort')}</div>
                                            <div
                                                className="text-sm text-primary"
                                                data-testid={TEST_IDENTIFIER + '-selected-value'}
                                            >
                                                {value.displayValue}
                                            </div>
                                        </div>
                                    </div>
                                ))}
                        </SortingBarItem>
                        {toggleSortMenu &&
                            sortValues
                                .filter((value) => value.stateValue !== selectedSort)
                                .map((value, index) => {
                                    return (
                                        <SortingBarItem key={value.stateValue}>
                                            <SortingBarItemLink
                                                isActive={selectedSort === value.stateValue}
                                                onClick={onMobileSort(selectedSort, value.stateValue)}
                                                dataTestId={TEST_IDENTIFIER + '-' + index}
                                            >
                                                {value.displayValue}
                                            </SortingBarItemLink>
                                        </SortingBarItem>
                                    );
                                })}{' '}
                    </>
                ) : (
                    <>
                        <div className="-ml-8 flex">
                            {sortValues.map((value, index) => (
                                <SortingBarItem
                                    key={value.stateValue}
                                    onClick={onSort(selectedSort, value.stateValue)}
                                    dataTestId={TEST_IDENTIFIER + '-' + index}
                                >
                                    <SortingBarItemLink isActive={selectedSort === value.stateValue}>
                                        <span>{value.displayValue}</span>
                                    </SortingBarItemLink>
                                </SortingBarItem>
                            ))}
                        </div>
                        <SortingBarItem>
                            <strong>{totalCount} </strong>
                            {t('Products count', { count: totalCount })}
                        </SortingBarItem>
                    </>
                )}
            </div>
        </div>
    );
};

const SortingBarItem: FC<{ onClick?: () => void }> = ({ dataTestId, children, onClick }) => (
    <div className="relative vl:ml-7" data-testid={dataTestId} onClick={onClick}>
        {children}
    </div>
);

const SortingBarItemLink: FC<{ isActive: boolean; onClick?: () => void }> = ({
    isActive,
    children,
    dataTestId,
    onClick,
}) => (
    <a
        className={twJoin(
            'block py-4 px-2 text-center text-xs uppercase text-dark no-underline transition after:absolute after:left-0 after:bottom-0 after:hidden after:h-[2px] after:w-full after:cursor-auto after:bg-primary after:content-[""] hover:bg-primary hover:text-dark hover:no-underline vl:py-2 vl:px-0 vl:hover:bg-opacity-0',
            !isActive ? 'vl:after:hidden' : 'after:hidden vl:after:block',
        )}
        data-testid={dataTestId}
        onClick={onClick}
    >
        {children}
    </a>
);
