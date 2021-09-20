import { FC, useState } from 'react';
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
import { SortType, userActions } from 'redux/store/UserStore';
import { useShopsysDispatch, useShopsysSelector } from 'redux/store';
import { getIsElementVisible } from 'components/Helpers/GetIsItemVisible';
import { mobileFirstSizes } from 'components/Theme/mediaQueries';
import { useGetWindowSize } from 'hooks/ui/UseGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/UseResizeWidthEffect';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

export interface SortValues {
    stateValue: SortType;
    displayValue: string;
}

const SortingBar: FC = () => {
    const t = useTypedTranslationFunction();
    const dispatch = useShopsysDispatch();
    const { width } = useGetWindowSize();
    const [isMobileSortBarVisible, setMobileSortBarVisible] = useState(true);
    useResizeWidthEffect(
        width,
        mobileFirstSizes.vl,
        () => setMobileSortBarVisible(false),
        () => setMobileSortBarVisible(true),
        () => setMobileSortBarVisible(getIsElementVisible([{ min: 0, max: 1024 }], width)),
    );
    const selectedSort = useShopsysSelector((state) => state.user.sort);
    const [toggleSortMenu, setToggleSortMenu] = useState(false);
    const sortValues: SortValues[] = [
        { stateValue: 'PRIORITY', displayValue: t('priority') },
        { stateValue: 'PRICE_ASC', displayValue: t('price ascending') },
        { stateValue: 'PRICE_DESC', displayValue: t('price descending') },
    ];

    return (
        <SortingBarStyled>
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
                                        dispatch(userActions.setSort({ sort: value.stateValue }));
                                    }}
                                >
                                    <SortingBarSortIconStyled icon="Sort" />
                                    <SortingBarSeletedSortWrapStyled>
                                        <SortingBarTitleStyled>{t('Sort')}</SortingBarTitleStyled>
                                        <SortingBarSelectedValue>
                                            {t(value.displayValue.toString())}
                                        </SortingBarSelectedValue>
                                    </SortingBarSeletedSortWrapStyled>
                                </SortingBarSelectedSortStyled>
                            ))}
                    </SortingBarItemStyled>
                    {toggleSortMenu &&
                        sortValues
                            .filter((value) => value.stateValue !== selectedSort)
                            .map((value) => {
                                return (
                                    <SortingBarItemStyled key={value.stateValue}>
                                        <SortingBarItemLinkStyled
                                            isActive={selectedSort === value.stateValue}
                                            onClick={() => {
                                                setToggleSortMenu(!toggleSortMenu);
                                                dispatch(userActions.setSort({ sort: value.stateValue }));
                                            }}
                                        >
                                            {t(value.displayValue.toString())}
                                        </SortingBarItemLinkStyled>
                                    </SortingBarItemStyled>
                                );
                            })}
                </SortingBarOptionsWrapStyled>
            ) : (
                <SortingBarOptionsWrapStyled>
                    <SortingBarOptionsStyled>
                        {sortValues.map((value) => {
                            return (
                                <SortingBarItemStyled
                                    key={value.stateValue}
                                    onClick={() => {
                                        dispatch(userActions.setSort({ sort: value.stateValue }));
                                    }}
                                >
                                    <SortingBarItemLinkStyled isActive={selectedSort === value.stateValue}>
                                        <SortingBarItemLinkWrapStyled>
                                            {t(value.displayValue.toString())}
                                        </SortingBarItemLinkWrapStyled>
                                    </SortingBarItemLinkStyled>
                                </SortingBarItemStyled>
                            );
                        })}
                    </SortingBarOptionsStyled>
                    <SortingBarItemStyled>
                        {/* TODO PRG: connect to actual products */}
                        <strong>4 </strong>
                        {t('Products')}
                    </SortingBarItemStyled>
                </SortingBarOptionsWrapStyled>
            )}
        </SortingBarStyled>
    );
};

export default SortingBar;
