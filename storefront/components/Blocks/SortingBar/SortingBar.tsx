import { useProductFilterOptions } from '../Product/Filter/FilterContext/useFilterState';
import {
    SortingBarItemLinkStyled,
    SortingBarItemLinkWrapStyled,
    SortingBarItemStyled,
    SortingBarOptionsStyled,
    SortingBarOptionsWrapStyled,
    SortingBarSelectedSortStyled,
    SortingBarSelectedValue,
    SortingBarSeletedSortWrapStyled,
    SortingBarSortIconStyled,
    SortingBarStyled,
    SortingBarTitleStyled,
} from './SortingBar.style';
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
import { FC, useCallback, useEffect, useState } from 'react';
import { useShopsysDispatch } from 'redux/main';

type SortingBarProps = {
    totalCount: number;
    sorting: ProductOrderingModeEnumApi | null;
};

const TEST_IDENTIFIER = 'blocks-sortingbar';

export const SortingBar: FC<SortingBarProps> = ({ sorting, totalCount }) => {
    const router = useRouter();
    const t = useTypedTranslationFunction();
    const dispatch = useShopsysDispatch();
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
                dispatch({ type: 'resetPagination', payload: { pageSize: 24 } });
            }
        },
        [dispatch, updateUrlWithCurrentSort],
    );

    const onSort = useCallback(
        (currentSort: ProductOrderingModeEnumApi | null, newSort: ProductOrderingModeEnumApi) => () => {
            if (isNewSortDifferentThanCurrent(currentSort, newSort)) {
                updateUrlWithCurrentSort(newSort);
                setSelectedSort(newSort);
                dispatch({ type: 'resetPagination', payload: { pageSize: 24 } });
            }
        },
        [dispatch, updateUrlWithCurrentSort],
    );

    return (
        <SortingBarStyled data-testid={TEST_IDENTIFIER}>
            {isMobileSortBarVisible ? (
                <SortingBarOptionsWrapStyled>
                    <SortingBarItemStyled>
                        {sortValues
                            .filter((value) => value.stateValue === selectedSort)
                            .map((value) => (
                                <SortingBarSelectedSortStyled
                                    key={value.stateValue}
                                    onClick={onSelectSortMenu(selectedSort, value.stateValue)}
                                    data-testid={TEST_IDENTIFIER + '-selected'}
                                >
                                    <SortingBarSortIconStyled alt="" iconType="icon" icon="Sort" />
                                    <SortingBarSeletedSortWrapStyled>
                                        <SortingBarTitleStyled>{t('Sort')}</SortingBarTitleStyled>
                                        <SortingBarSelectedValue data-testid={TEST_IDENTIFIER + '-selected-value'}>
                                            {value.displayValue}
                                        </SortingBarSelectedValue>
                                    </SortingBarSeletedSortWrapStyled>
                                </SortingBarSelectedSortStyled>
                            ))}
                    </SortingBarItemStyled>
                    {toggleSortMenu &&
                        sortValues
                            .filter((value) => value.stateValue !== selectedSort)
                            .map((value, index) => {
                                return (
                                    <SortingBarItemStyled key={value.stateValue}>
                                        <SortingBarItemLinkStyled
                                            isActive={selectedSort === value.stateValue}
                                            onClick={onMobileSort(selectedSort, value.stateValue)}
                                            data-testid={TEST_IDENTIFIER + '-' + index}
                                        >
                                            {value.displayValue}
                                        </SortingBarItemLinkStyled>
                                    </SortingBarItemStyled>
                                );
                            })}
                </SortingBarOptionsWrapStyled>
            ) : (
                <SortingBarOptionsWrapStyled>
                    <SortingBarOptionsStyled>
                        {sortValues.map((value, index) => {
                            return (
                                <SortingBarItemStyled
                                    key={value.stateValue}
                                    onClick={onSort(selectedSort, value.stateValue)}
                                    data-testid={TEST_IDENTIFIER + '-' + index}
                                >
                                    <SortingBarItemLinkStyled isActive={selectedSort === value.stateValue}>
                                        <SortingBarItemLinkWrapStyled>
                                            {value.displayValue}
                                        </SortingBarItemLinkWrapStyled>
                                    </SortingBarItemLinkStyled>
                                </SortingBarItemStyled>
                            );
                        })}
                    </SortingBarOptionsStyled>
                    <SortingBarItemStyled>
                        <strong>{totalCount} </strong>
                        {t('Products count', { count: totalCount })}
                    </SortingBarItemStyled>
                </SortingBarOptionsWrapStyled>
            )}
        </SortingBarStyled>
    );
};

const isNewSortDifferentThanCurrent = (
    currentSort: ProductOrderingModeEnumApi | null,
    newSort: ProductOrderingModeEnumApi,
) => currentSort !== newSort;
