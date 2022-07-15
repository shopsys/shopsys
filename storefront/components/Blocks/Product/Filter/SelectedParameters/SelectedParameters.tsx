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
import { FC, useMemo } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { FilterFormParameterType, FilterFormType, FilterOptionsType } from 'types/productFilter';

type SelectedParametersProps = {
    productFilterOptions: FilterOptionsType;
};

const TEST_IDENTIFIER = 'blocks-product-filter-selectedparameters';

export const SelectedParameters: FC<SelectedParametersProps> = ({ productFilterOptions }) => {
    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();
    const formProviderMethods = useFormContext<FilterFormType>();

    const [brandsValue, flagsValue, parametersValue, isOnlyInStock, minimalPrice, maximalPrice] = useWatch({
        name: ['brands', 'flags', 'parameters', 'onlyInStock', 'minimalPrice', 'maximalPrice'],
        control: formProviderMethods.control,
    });

    const isMinimalPriceVisible = minimalPrice !== productFilterOptions.minimalPrice;
    const isMaximalPriceVisible = maximalPrice !== productFilterOptions.maximalPrice;

    const checkedBrands = useMemo(() => brandsValue.filter((brand) => brand.checked), [brandsValue]);
    const checkedFlags = useMemo(() => flagsValue.filter((brand) => brand.checked), [flagsValue]);
    const checkedParameters = useMemo(() => {
        const newCheckedParameters: FilterFormParameterType[] = [];

        parametersValue.forEach((currentParameterWithFilteredValues) => {
            const filteredValues = currentParameterWithFilteredValues.values.filter((value) => value.checked);

            if (filteredValues.length > 0) {
                newCheckedParameters.push({ ...currentParameterWithFilteredValues, values: filteredValues });
            }
        });

        return newCheckedParameters;
    }, [parametersValue]);

    const onUncheckFlag = (uuid: string) => () => {
        const indexOfValue = flagsValue.findIndex((item) => item.uuid === uuid);

        formProviderMethods.setValue(`flags.${indexOfValue}`, { ...flagsValue[indexOfValue], checked: false });
    };

    const onUncheckBrand = (uuid: string) => () => {
        const indexOfValue = brandsValue.findIndex((item) => item.uuid === uuid);

        formProviderMethods.setValue(`brands.${indexOfValue}`, { ...brandsValue[indexOfValue], checked: false });
    };

    const onResetPrices = () => {
        formProviderMethods.setValue('minimalPrice', productFilterOptions.minimalPrice);
        formProviderMethods.setValue('maximalPrice', productFilterOptions.maximalPrice);
    };

    const onResetAllParameters = () => {
        flagsValue.forEach((flag, index) =>
            formProviderMethods.setValue(`flags.${index}`, { ...flag, checked: false }),
        );
        brandsValue.forEach((brand, index) =>
            formProviderMethods.setValue(`brands.${index}`, { ...brand, checked: false }),
        );
        parametersValue.forEach((parameterItem, parameterIndex) => {
            formProviderMethods.setValue(`parameters.${parameterIndex}`, {
                ...parameterItem,
                minimalValue: null,
                maximalValue: null,
                values: parameterItem.values.map((value) => ({ ...value, checked: false })),
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
