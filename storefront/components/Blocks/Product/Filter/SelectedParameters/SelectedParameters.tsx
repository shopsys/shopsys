import { FC, Fragment, useEffect, useState } from 'react';
import { FilterFormType, FilterOptionsType } from 'components/Blocks/Product/Filter/types';
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
import { useFormContext, useWatch } from 'react-hook-form';
import { formatPrice } from 'utils/formatting';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type SelectedParametersProps = {
    productFilterOptions: FilterOptionsType;
    slug: string;
};

const SelectedParameters: FC<SelectedParametersProps> = (props) => {
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext<FilterFormType>();
    const parametersFilterState = useShopsysSelector((state) => state.user.parametersFilter);
    const isOnlyInStock = parametersFilterState !== null && parametersFilterState.onlyInStock;
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
    }, [parametersFilterState.minimalPrice]);

    useEffect(() => {
        setMaximalPriceVisibility(
            parametersFilterState.maximalPrice !== props.productFilterOptions.maximalPrice &&
                parametersFilterState.maximalPrice !== null,
        );
    }, [parametersFilterState.maximalPrice]);

    const onUncheckParameter = (parameterUuid: string, parameterValueUuid: string) => {
        const indexOfParameter = parametersValue.findIndex((item) => item.parameterUuid === parameterUuid);
        const indexOfValue = parametersValue[indexOfParameter]?.values.findIndex(
            (item) => item.uuid === parameterValueUuid,
        );

        formProviderMethods.setValue(`parameters.${indexOfParameter}.values.${indexOfValue}.checked`, false);
    };

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
            {parametersFilterState !== null &&
                (parametersFilterState.flags.length > 0 ||
                    parametersFilterState.brands.length > 0 ||
                    parametersFilterState.parameters.length > 0 ||
                    isMaximalPriceVisible ||
                    isMinimalPriceVisible ||
                    isOnlyInStock) && (
                    <SelectedParametersStyled>
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

                            {parametersFilterState.parameters.length > 0 &&
                                parametersFilterState.parameters.map((parameterItem, parameterItemIndex) => (
                                    <Fragment key={parameterItemIndex}>
                                        {parametersValue
                                            .filter(
                                                (parameter) => parameter.parameterUuid === parameterItem.parameter,
                                            )[0]
                                            ?.values.filter((parameterValue) => parameterValue.checked === true)
                                            .map((parameterValue, index) => (
                                                <SelectedParametersListItemStyled key={index}>
                                                    {parameterValue.text}
                                                    <SelectedParametersListItemRemoveStyled
                                                        iconType="icon"
                                                        icon="RemoveThin"
                                                        onClick={() =>
                                                            onUncheckParameter(
                                                                parameterItem.parameter,
                                                                parameterValue.uuid,
                                                            )
                                                        }
                                                    />
                                                </SelectedParametersListItemStyled>
                                            ))}
                                    </Fragment>
                                ))}

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
