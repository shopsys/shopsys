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
        const indexOfValueInChecked = checkedFlags.findIndex((item) => item.uuid === uuid);

        formProviderMethods.setValue(`flags.${indexOfValue}`, {
            ...checkedFlags[indexOfValueInChecked],
            checked: false,
        });
    };

    const onUncheckBrand = (uuid: string) => () => {
        const indexOfValue = productFilterOptions.brands.findIndex((item) => item.brand.uuid === uuid);
        const indexOfValueInChecked = checkedBrands.findIndex((item) => item.uuid === uuid);

        formProviderMethods.setValue(`brands.${indexOfValue}`, {
            ...checkedBrands[indexOfValueInChecked],
            checked: false,
        });
    };

    const onResetPrices = () => {
        formProviderMethods.setValue('minimalPrice', productFilterOptions.minimalPrice);
        formProviderMethods.setValue('maximalPrice', productFilterOptions.maximalPrice);
    };

    const onResetAllParameters = () => {
        productFilterOptions.flags.forEach((flag, index) =>
            formProviderMethods.setValue(`flags.${index}`, { ...flag.flag, checked: false }),
        );
        productFilterOptions.brands.forEach((brand, index) =>
            formProviderMethods.setValue(`brands.${index}`, { ...brand.brand, checked: false }),
        );
        productFilterOptions.parameters?.forEach((parameterItem, parameterIndex) => {
            formProviderMethods.setValue(`parameters.${parameterIndex}`, {
                parameterName: parameterItem.name,
                selectedValue: 'selectedValue' in parameterItem ? parameterItem.selectedValue : null,
                parameterUuid: parameterItem.uuid,
                unit: 'unit' in parameterItem ? parameterItem.unit : null,
                isCollapsed: parameterItem.isCollapsed,
                minimalValue: null,
                maximalValue: null,
                values:
                    'values' in parameterItem
                        ? parameterItem.values.map((value) => ({
                              ...value,
                              rgbHex: 'rgbHex' in value ? value.rgbHex : null,
                              checked: false,
                          }))
                        : [],
            });
        });
        formProviderMethods.setValue(`onlyInStock`, false);
        onResetPrices();
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
