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
import { getProductListSort } from 'helpers/sorting/GetProductListSort';
import { parseProductListSortFromQuery } from 'helpers/sorting/ParseProductListSortFromQuery';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useGetWindowSize } from 'hooks/ui/UseGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/UseResizeWidthEffect';
import { useRouter } from 'next/router';
import { FC, useEffect, useState } from 'react';
import { useShopsysDispatch } from 'redux/main';
import { initialState, userActions } from 'redux/slices/user';

type SortingBarProps = { totalCount: number; sorting: ProductOrderingModeEnumApi | null };

const SortingBar: FC<SortingBarProps> = (props) => {
    const testIdentifier = 'blocks-sortingbar';

    const router = useRouter();
    const t = useTypedTranslationFunction();
    const dispatch = useShopsysDispatch();
    const sortingFromQuery = getProductListSort(parseProductListSortFromQuery(router.query.sort));
    const [selectedSort, setSelectedSort] = useState<ProductOrderingModeEnumApi | null>(
        props.sorting ?? sortingFromQuery ?? ProductOrderingModeEnumApi.PriorityApi,
    );
    const { width } = useGetWindowSize();
    const { totalCount } = props;
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
        setSelectedSort(props.sorting);
    }, [props.sorting]);

    const updateUrlWithCurrentSort = (sort: string) => {
        if (!canUseDom()) {
            return;
        }

        const pathname = router.asPath.split('?')[0];
        const queryParams = router.query;
        delete queryParams.all;
        queryParams.sort = sort;

        router.replace({ pathname, query: queryParams }, undefined, { shallow: true, scroll: false });
    };

    return (
        <SortingBarStyled data-testid={testIdentifier}>
            {isMobileSortBarVisible ? (
                <SortingBarOptionsWrapStyled>
                    <SortingBarItemStyled>
                        {sortValues
                            .filter((value) => value.stateValue === selectedSort)
                            .map((value) => (
                                <SortingBarSelectedSortStyled
                                    key={value.stateValue}
                                    onClick={() => {
                                        setToggleSortMenu(!toggleSortMenu);
                                        updateUrlWithCurrentSort(value.stateValue);
                                        setSelectedSort(value.stateValue);
                                    }}
                                    data-testid={testIdentifier + '-selected'}
                                >
                                    <SortingBarSortIconStyled iconType="icon" icon="Sort" />
                                    <SortingBarSeletedSortWrapStyled>
                                        <SortingBarTitleStyled>{t('Sort')}</SortingBarTitleStyled>
                                        <SortingBarSelectedValue data-testid={testIdentifier + '-selected-value'}>
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
                                            onClick={() => {
                                                setToggleSortMenu(!toggleSortMenu);
                                                updateUrlWithCurrentSort(value.stateValue);
                                                setSelectedSort(value.stateValue);
                                                dispatch(userActions.setPagination({ ...initialState.pagination }));
                                            }}
                                            data-testid={testIdentifier + '-' + index}
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
                                    onClick={() => {
                                        updateUrlWithCurrentSort(value.stateValue);
                                        setSelectedSort(value.stateValue);
                                        dispatch(userActions.setPagination({ ...initialState.pagination }));
                                    }}
                                    data-testid={testIdentifier + '-' + index}
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
