import {
    useCheckedBrands,
    useCheckedFlags,
    useFilterState,
    useIsProductFilterEmpty,
} from '../FilterContext/useFilterState';
import { Parameters } from './Parameters/Parameters';
import {
    SelectedParametersBlockStyled,
    SelectedParametersListItemRemoveStyled,
    SelectedParametersListItemStyled,
    SelectedParametersListStyled,
    SelectedParametersNameStyled,
    SelectedParametersResetRemoveStyled,
    SelectedParametersResetStyled,
    SelectedParametersResetTextStyled,
    SelectedParametersStyled,
    SelectedParametersTitleStyled,
} from './SelectedParameters.style';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC, useMemo } from 'react';

const TEST_IDENTIFIER = 'blocks-product-filter-selectedparameters';

export const SelectedParameters: FC = () => {
    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();
    const [state, dispatch] = useFilterState();
    const checkedBrands = useCheckedBrands();
    const checkedFlags = useCheckedFlags();
    const isOnlyInStock = useMemo(() => state.selected.onlyInStock, [state.selected.onlyInStock]);
    const isProductFilterEmpty = useIsProductFilterEmpty();
    const minimalPrice = useMemo(() => state.selected.minimalPrice, [state.selected.minimalPrice]);
    const maximalPrice = useMemo(() => state.selected.maximalPrice, [state.selected.maximalPrice]);
    const isMinimalPriceVisible = useMemo(
        () => state.selected.minimalPrice !== state.options.minimalPrice,
        [state.options.minimalPrice, state.selected.minimalPrice],
    );
    const isMaximalPriceVisible = useMemo(
        () => state.selected.maximalPrice !== state.options.maximalPrice,
        [state.options.maximalPrice, state.selected.maximalPrice],
    );

    if (isProductFilterEmpty) {
        return null;
    }

    return (
        <SelectedParametersStyled data-testid={TEST_IDENTIFIER}>
            <SelectedParametersTitleStyled type="h4">{t('Selected filters')}</SelectedParametersTitleStyled>
            <SelectedParametersBlockStyled>
                {checkedBrands.length > 0 && (
                    <SelectedParametersListStyled>
                        <SelectedParametersNameStyled>{t('Brands')}:</SelectedParametersNameStyled>
                        {checkedBrands.map((filterFormBrand) => (
                            <SelectedParametersListItemStyled key={filterFormBrand.uuid}>
                                {filterFormBrand.name}
                                <SelectedParametersListItemRemoveStyled
                                    alt=""
                                    iconType="icon"
                                    icon="RemoveThin"
                                    onClick={() => dispatch({ type: 'uncheckBrand', payload: filterFormBrand.uuid })}
                                />
                            </SelectedParametersListItemStyled>
                        ))}
                    </SelectedParametersListStyled>
                )}

                {checkedFlags.length > 0 && (
                    <SelectedParametersListStyled>
                        <SelectedParametersNameStyled>{t('Flags')}:</SelectedParametersNameStyled>
                        {checkedFlags.map((filterFormFlag) => (
                            <SelectedParametersListItemStyled key={filterFormFlag.uuid}>
                                {filterFormFlag.name}
                                <SelectedParametersListItemRemoveStyled
                                    alt=""
                                    iconType="icon"
                                    icon="RemoveThin"
                                    onClick={() => dispatch({ type: 'uncheckFlag', payload: filterFormFlag.uuid })}
                                />
                            </SelectedParametersListItemStyled>
                        ))}
                    </SelectedParametersListStyled>
                )}
                <Parameters />
                {isOnlyInStock && (
                    <SelectedParametersListStyled>
                        <SelectedParametersNameStyled>{t('Availability')}:</SelectedParametersNameStyled>
                        <SelectedParametersListItemStyled>
                            {t('Only goods in stock')}
                            <SelectedParametersListItemRemoveStyled
                                alt=""
                                iconType="icon"
                                icon="RemoveThin"
                                onClick={() => dispatch({ type: 'setOnlyInStock', payload: false })}
                            />
                        </SelectedParametersListItemStyled>
                    </SelectedParametersListStyled>
                )}

                {(isMinimalPriceVisible || isMaximalPriceVisible) && (
                    <SelectedParametersListStyled>
                        <SelectedParametersNameStyled>{t('Price')}:</SelectedParametersNameStyled>
                        <SelectedParametersListItemStyled>
                            {isMinimalPriceVisible && (
                                <>
                                    <span>{t('from')}&nbsp;</span>
                                    {formatPrice(minimalPrice)}
                                    {isMaximalPriceVisible ? ' ' : ''}
                                </>
                            )}
                            {isMaximalPriceVisible && (
                                <>
                                    <span>{t('to')}&nbsp;</span>
                                    {formatPrice(maximalPrice)}
                                </>
                            )}
                            <SelectedParametersListItemRemoveStyled
                                alt=""
                                iconType="icon"
                                icon="RemoveThin"
                                onClick={() => dispatch({ type: 'resetPrices' })}
                            />
                        </SelectedParametersListItemStyled>
                    </SelectedParametersListStyled>
                )}
            </SelectedParametersBlockStyled>
            <SelectedParametersResetStyled onClick={() => dispatch({ type: 'resetAllParameters' })}>
                <SelectedParametersResetTextStyled>{t('Clear all')}</SelectedParametersResetTextStyled>
                <SelectedParametersResetRemoveStyled alt="" iconType="icon" icon="Remove" />
            </SelectedParametersResetStyled>
        </SelectedParametersStyled>
    );
};
