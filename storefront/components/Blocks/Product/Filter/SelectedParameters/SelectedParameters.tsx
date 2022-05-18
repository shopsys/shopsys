import Parameters from './Parameters';
import {
    SelectedParametersListItemRemoveStyled,
    SelectedParametersListItemStyled,
    SelectedParametersListStyled,
    SelectedParametersResetRemoveStyled,
    SelectedParametersResetStyled,
    SelectedParametersResetTextStyled,
    SelectedParametersStyled,
    SelectedParametersTitleStyled,
} from './SelectedParameters.style';
import { isProductFilterWithoutChanges } from 'helpers/IsProductFilterWithoutChanges';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC, Fragment, useEffect, useState } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { useShopsysSelector } from 'redux/main';
import { FilterFormType, FilterOptionsType } from 'types/productFilter';
import { formatPrice } from 'utils/formatting';

type SelectedParametersProps = {
    productFilterOptions: FilterOptionsType;
};

const SelectedParameters: FC<SelectedParametersProps> = (props) => {
    const testIdentifier = 'blocks-product-filter-selectedparameters';

    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext<FilterFormType>();
    const parametersFilterState = useShopsysSelector((state) => state.optionsFilter);
    const isOnlyInStock = parametersFilterState.onlyInStock;
    const [brandsValue, flagsValue, parametersValue] = useWatch({
        name: ['brands', 'flags', 'parameters'],
        control: formProviderMethods.control,
    });
    const [isMinimalPriceVisible, setMinimalPriceVisibility] = useState(false);
    const [isMaximalPriceVisible, setMaximalPriceVisibility] = useState(false);

    useEffect(() => {
        setMinimalPriceVisibility(
            parametersFilterState.minimalPrice !== props.productFilterOptions.minimalPrice &&
                parametersFilterState.minimalPrice !== null,
        );
    }, [parametersFilterState.minimalPrice, props.productFilterOptions.minimalPrice]);

    useEffect(() => {
        setMaximalPriceVisibility(
            parametersFilterState.maximalPrice !== props.productFilterOptions.maximalPrice &&
                parametersFilterState.maximalPrice !== null,
        );
    }, [parametersFilterState.maximalPrice, props.productFilterOptions.maximalPrice]);

    const onUncheckFlag = (uuid: string) => {
        const indexOfValue = flagsValue.findIndex((item) => item.uuid === uuid);

        formProviderMethods.setValue(`flags.${indexOfValue}.checked`, false);
    };

    const onUncheckBrand = (uuid: string) => {
        const indexOfValue = brandsValue.findIndex((item) => item.uuid === uuid);

        formProviderMethods.setValue(`brands.${indexOfValue}.checked`, false);
    };

    const onResetPrices = () => {
        formProviderMethods.setValue('minimalPrice', props.productFilterOptions.minimalPrice);
        formProviderMethods.setValue('maximalPrice', props.productFilterOptions.maximalPrice);
    };

    const onResetAllParameters = () => {
        flagsValue.forEach((_, index) => formProviderMethods.setValue(`flags.${index}.checked`, false));
        brandsValue.forEach((_, index) => formProviderMethods.setValue(`brands.${index}.checked`, false));
        parametersValue.forEach((parameterItem, parameterIndex) =>
            parameterItem.values.forEach((_, itemIndex) =>
                formProviderMethods.setValue(`parameters.${parameterIndex}.values.${itemIndex}.checked`, false),
            ),
        );
        formProviderMethods.setValue(`onlyInStock`, false);
        onResetPrices();
    };

    return (
        <>
            {!isProductFilterWithoutChanges(parametersFilterState, props.productFilterOptions) && (
                <SelectedParametersStyled data-testid={testIdentifier}>
                    <SelectedParametersTitleStyled type="h4">{t('Selected filters')}</SelectedParametersTitleStyled>
                    <SelectedParametersListStyled>
                        {parametersFilterState.brands.map((brandUuid) => (
                            <SelectedParametersListItemStyled key={brandUuid}>
                                {brandsValue.find((value) => value.uuid === brandUuid)?.name}
                                <SelectedParametersListItemRemoveStyled
                                    iconType="icon"
                                    icon="RemoveThin"
                                    onClick={() => onUncheckBrand(brandUuid)}
                                />
                            </SelectedParametersListItemStyled>
                        ))}

                        {parametersFilterState.flags.map((flagUuid) => (
                            <SelectedParametersListItemStyled key={flagUuid}>
                                {flagsValue.find((value) => value.uuid === flagUuid)?.name}
                                <SelectedParametersListItemRemoveStyled
                                    iconType="icon"
                                    icon="RemoveThin"
                                    onClick={() => onUncheckFlag(flagUuid)}
                                />
                            </SelectedParametersListItemStyled>
                        ))}

                        <Parameters />

                        {isOnlyInStock !== false && (
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
                        )}

                        {(isMinimalPriceVisible || isMaximalPriceVisible) && (
                            <SelectedParametersListItemStyled>
                                {isMinimalPriceVisible && (
                                    <>
                                        <span>{t('from')}&nbsp;</span>
                                        {parametersFilterState.minimalPrice !== null &&
                                            formatPrice(
                                                parametersFilterState.minimalPrice,
                                                props.productFilterOptions.currencyCode,
                                                t,
                                            )}
                                        &nbsp;
                                    </>
                                )}
                                {isMaximalPriceVisible && (
                                    <>
                                        <span>{t('to')}&nbsp;</span>
                                        {parametersFilterState.maximalPrice !== null &&
                                            formatPrice(
                                                parametersFilterState.maximalPrice,
                                                props.productFilterOptions.currencyCode,
                                                t,
                                            )}
                                        &nbsp;
                                    </>
                                )}
                                <SelectedParametersListItemRemoveStyled
                                    iconType="icon"
                                    icon="RemoveThin"
                                    onClick={() => onResetPrices()}
                                />
                            </SelectedParametersListItemStyled>
                        )}
                    </SelectedParametersListStyled>
                    <SelectedParametersResetStyled onClick={onResetAllParameters}>
                        <SelectedParametersResetTextStyled>{t('Clear all')}</SelectedParametersResetTextStyled>
                        <SelectedParametersResetRemoveStyled iconType="icon" icon="Remove" />
                    </SelectedParametersResetStyled>
                </SelectedParametersStyled>
            )}
        </>
    );
};

export default SelectedParameters;
