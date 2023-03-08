import {
    useCheckedBrands,
    useCheckedFlags,
    useFilterState,
    useIsProductFilterEmpty,
} from '../FilterContext/useFilterState';
import { SelectedParametersList, SelectedParametersListItem, SelectedParametersName } from '../FilterElements';
import { Parameters } from './Parameters';
import { SelectedParametersIcon } from './SelectedParametersIcon';
import { Heading } from 'components/Basic/Heading/Heading';
import { Icon } from 'components/Basic/Icon/Icon';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useMemo } from 'react';

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
        <div
            className="z-aboveOverlay mb-5 rounded-xl border border-greyLight bg-blueLight px-4 pt-7 pb-4 vl:z-[0] vl:border-none"
            data-testid={TEST_IDENTIFIER}
        >
            <Heading type="h4" className="uppercase">
                {t('Selected filters')}
            </Heading>
            <div className="mb-4 -mr-2">
                {checkedBrands.length > 0 && (
                    <SelectedParametersList>
                        <SelectedParametersName>{t('Brands')}:</SelectedParametersName>
                        {checkedBrands.map((filterFormBrand) => (
                            <SelectedParametersListItem key={filterFormBrand.uuid}>
                                {filterFormBrand.name}
                                <SelectedParametersIcon
                                    onClick={() => dispatch({ type: 'uncheckBrand', payload: filterFormBrand.uuid })}
                                />
                            </SelectedParametersListItem>
                        ))}
                    </SelectedParametersList>
                )}

                {checkedFlags.length > 0 && (
                    <SelectedParametersList>
                        <SelectedParametersName>{t('Flags')}:</SelectedParametersName>
                        {checkedFlags.map((filterFormFlag) => (
                            <SelectedParametersListItem key={filterFormFlag.uuid}>
                                {filterFormFlag.name}
                                <SelectedParametersIcon
                                    onClick={() => dispatch({ type: 'uncheckFlag', payload: filterFormFlag.uuid })}
                                />
                            </SelectedParametersListItem>
                        ))}
                    </SelectedParametersList>
                )}
                <Parameters />
                {isOnlyInStock && (
                    <SelectedParametersList>
                        <SelectedParametersName>{t('Availability')}:</SelectedParametersName>
                        <SelectedParametersListItem>
                            {t('Only goods in stock')}
                            <SelectedParametersIcon
                                onClick={() => dispatch({ type: 'setOnlyInStock', payload: false })}
                            />
                        </SelectedParametersListItem>
                    </SelectedParametersList>
                )}

                {(isMinimalPriceVisible || isMaximalPriceVisible) && (
                    <SelectedParametersList>
                        <SelectedParametersName>{t('Price')}:</SelectedParametersName>
                        <SelectedParametersListItem>
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
                            <SelectedParametersIcon onClick={() => dispatch({ type: 'resetPrices' })} />
                        </SelectedParametersListItem>
                    </SelectedParametersList>
                )}
            </div>
            <div
                className="flex cursor-pointer items-center text-sm text-greyLight"
                onClick={() => dispatch({ type: 'resetAllParameters' })}
            >
                <div className="font-bold uppercase">{t('Clear all')}</div>
                <Icon iconType="icon" icon="Remove" className="ml-2 cursor-pointer text-greenLight" />
            </div>
        </div>
    );
};
