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
import { canUseDom } from 'helpers/canUseDom';
import { getFilterUrlQueryForSortingInSeoCategory } from 'helpers/filterOptions/GetFilterUrlQueryForSortingInSeoCategory';
import { getProductListSort } from 'helpers/sorting/GetProductListSort';
import { parseProductListSortFromQuery } from 'helpers/sorting/ParseProductListSortFromQuery';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useGetWindowSize } from 'hooks/ui/UseGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/UseResizeWidthEffect';
import { useRouter } from 'next/router';
import { FC, useCallback, useEffect, useMemo, useState } from 'react';
import { useShopsysDispatch } from 'redux/main';
import { initialState, userActions } from 'redux/slices/user';
import { FilterOptionsType } from 'types/productFilter';

type SortingBarProps = {
    totalCount: number;
    sorting: ProductOrderingModeEnumApi | null;
    productFilterOptions?: FilterOptionsType;
};

const TEST_IDENTIFIER = 'blocks-sortingbar';

const SortingBar: FC<SortingBarProps> = ({ sorting, totalCount, productFilterOptions }) => {
    const router = useRouter();
    const t = useTypedTranslationFunction();
    const dispatch = useShopsysDispatch();
    const sortingFromQuery = getProductListSort(parseProductListSortFromQuery(router.query.sort));
    const [selectedSort, setSelectedSort] = useState<ProductOrderingModeEnumApi | null>(sorting ?? sortingFromQuery);
    const { width } = useGetWindowSize();
    const [isMobileSortBarVisible, setMobileSortBarVisible] = useState(true);
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

    const deepComparedProductFilterOptions = useMemo(
        () => productFilterOptions ?? null,
        // eslint-disable-next-line react-hooks/exhaustive-deps
        [JSON.stringify(productFilterOptions)],
    );

    const updateUrlWithCurrentSort = useCallback(
        (sort: string) => {
            if (!canUseDom()) {
                return;
            }

            const pathname = router.asPath.split('?')[0];
            const queryParams = router.query;
            delete queryParams.all;
            queryParams.sort = sort;

            const filterUrlQuery =
                deepComparedProductFilterOptions !== null
                    ? getFilterUrlQueryForSortingInSeoCategory(deepComparedProductFilterOptions)
                    : null;
            if (filterUrlQuery !== null) {
                queryParams.filter = filterUrlQuery;
            }

            router.replace({ pathname, query: queryParams }, undefined, { shallow: true, scroll: false });
        },
        [router, deepComparedProductFilterOptions],
    );

    const onSelectSortMenu = useCallback(
        (value: ProductOrderingModeEnumApi) => () => {
            setToggleSortMenu((prev) => !prev);
            updateUrlWithCurrentSort(value);
            setSelectedSort(value);
        },
        [updateUrlWithCurrentSort],
    );

    const onMobileSort = useCallback(
        (value: ProductOrderingModeEnumApi) => () => {
            setToggleSortMenu((prev) => !prev);
            updateUrlWithCurrentSort(value);
            setSelectedSort(value);
            dispatch(userActions.setPagination({ ...initialState.pagination }));
        },
        [dispatch, updateUrlWithCurrentSort],
    );

    const onSort = useCallback(
        (value: ProductOrderingModeEnumApi) => () => {
            updateUrlWithCurrentSort(value);
            setSelectedSort(value);
            dispatch(userActions.setPagination({ ...initialState.pagination }));
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
                                    onClick={onSelectSortMenu(value.stateValue)}
                                    data-testid={TEST_IDENTIFIER + '-selected'}
                                >
                                    <SortingBarSortIconStyled iconType="icon" icon="Sort" />
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
                                            onClick={onMobileSort(value.stateValue)}
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
                                    onClick={onSort(value.stateValue)}
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

export default SortingBar;
