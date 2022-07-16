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
import { getIsProductFilterEmpty } from 'helpers/filterOptions/GetIsProductFilterEmpty';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { useFormContext } from 'react-hook-form';
import {
    FilterFormBrandType,
    FilterFormFlagType,
    FilterFormParameterType,
    FilterFormType,
    FilterOptionsType,
} from 'types/productFilter';

type SelectedParametersProps = {
    productFilterOptions: FilterOptionsType;
    checkedBrands: FilterFormBrandType[];
    checkedFlags: FilterFormFlagType[];
    checkedParameters: FilterFormParameterType[];
    isOnlyInStock: boolean;
    minimalPrice: number;
    maximalPrice: number;
};

const TEST_IDENTIFIER = 'blocks-product-filter-selectedparameters';

export const SelectedParameters: FC<SelectedParametersProps> = ({
    productFilterOptions,
    checkedBrands,
    checkedFlags,
    checkedParameters,
    isOnlyInStock,
    minimalPrice,
    maximalPrice,
}) => {
    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();
    const formProviderMethods = useFormContext<FilterFormType>();

    const isMinimalPriceVisible = minimalPrice !== productFilterOptions.minimalPrice;
    const isMaximalPriceVisible = maximalPrice !== productFilterOptions.maximalPrice;

    const onUncheckFlag = (uuid: string) => () => {
        const indexOfValue = productFilterOptions.flags.findIndex((item) => item.flag.uuid === uuid);

        formProviderMethods.setValue(`flags.${indexOfValue}`, { ...checkedFlags[indexOfValue], checked: false });
    };

    const onUncheckBrand = (uuid: string) => () => {
        const indexOfValue = productFilterOptions.brands.findIndex((item) => item.brand.uuid === uuid);

        formProviderMethods.setValue(`brands.${indexOfValue}`, { ...checkedBrands[indexOfValue], checked: false });
    };

    const onResetPrices = () => {
        formProviderMethods.setValue('minimalPrice', productFilterOptions.minimalPrice);
        formProviderMethods.setValue('maximalPrice', productFilterOptions.maximalPrice);
    };

    const onResetAllParameters = () => {
        formProviderMethods.reset();
    };

    if (
        getIsProductFilterEmpty(
            checkedBrands,
            checkedFlags,
            minimalPrice,
            maximalPrice,
            isOnlyInStock,
            checkedParameters,
            productFilterOptions,
        )
    ) {
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
                                    iconType="icon"
                                    icon="RemoveThin"
                                    onClick={onUncheckBrand(filterFormBrand.uuid)}
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
                                    iconType="icon"
                                    icon="RemoveThin"
                                    onClick={onUncheckFlag(filterFormFlag.uuid)}
                                />
                            </SelectedParametersListItemStyled>
                        ))}
                    </SelectedParametersListStyled>
                )}

                <Parameters checkedParameters={checkedParameters} filterOptions={productFilterOptions} />

                {isOnlyInStock && (
                    <SelectedParametersListStyled>
                        <SelectedParametersNameStyled>{t('Availability')}:</SelectedParametersNameStyled>
                        <SelectedParametersListItemStyled>
                            {t('Only goods in stock')}
                            <SelectedParametersListItemRemoveStyled
                                iconType="icon"
                                icon="RemoveThin"
                                onClick={() => {
                                    formProviderMethods.setValue('onlyInStock', false);
                                }}
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
                                iconType="icon"
                                icon="RemoveThin"
                                onClick={onResetPrices}
                            />
                        </SelectedParametersListItemStyled>
                    </SelectedParametersListStyled>
                )}
            </SelectedParametersBlockStyled>
            <SelectedParametersResetStyled onClick={onResetAllParameters}>
                <SelectedParametersResetTextStyled>{t('Clear all')}</SelectedParametersResetTextStyled>
                <SelectedParametersResetRemoveStyled iconType="icon" icon="Remove" />
            </SelectedParametersResetStyled>
        </SelectedParametersStyled>
    );
};
