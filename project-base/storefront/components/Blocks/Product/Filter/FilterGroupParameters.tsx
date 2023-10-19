import {
    FilterGroupContent,
    FilterGroupContentItem,
    FilterGroupTitle,
    FilterGroupWrapper,
    ShowAllButton,
} from './FilterElements';
import { RangeSlider } from 'components/Basic/RangeSlider/RangeSlider';
import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import { CheckboxColor } from 'components/Forms/CheckboxColor/CheckboxColor';
import { useQueryParams } from 'hooks/useQueryParams';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';
import { DefaultProductFiltersMapType } from 'store/slices/createSeoCategorySlice';
import { useSessionStore } from 'store/useSessionStore';
import { ParametersType } from 'types/productFilter';

type FilterGroupParametersProps = {
    title: string;
    parameterIndex: number;
    parameter: ParametersType;
    defaultNumberOfShownParameters: number;
};

const getDataTestId = (index: number) => 'blocks-product-filter-filtergroup-parameters-' + index;

export const FilterGroupParameters: FC<FilterGroupParametersProps> = ({
    title,
    parameter,
    defaultNumberOfShownParameters,
    dataTestId,
    parameterIndex,
}) => {
    const { t } = useTranslation();
    const [isGroupCollapsed, setIsGroupCollapsed] = useState(parameter.isCollapsed);
    const { filter, updateFilterParameters } = useQueryParams();
    const defaultSelectedParameters = useSessionStore((s) => s.defaultProductFiltersMap.parameters);

    const selectedParameter = filter?.parameters?.find((p) => p.parameter === parameter.uuid);

    const isCheckboxType = parameter.__typename === 'ParameterCheckboxFilterOption';

    // we need to check everywhere for isCheckboxType, otherwise Typescript doesn't know if .values exists
    // that's because it is sure only about overlaps within types of parameter
    const hiddenOptions = isCheckboxType
        ? parameter.values.slice(defaultNumberOfShownParameters, parameter.values.length)
        : [];

    const isWithHiddenCheckedItem = isCheckboxType
        ? hiddenOptions.some((o) => !!selectedParameter?.values?.includes(o.uuid))
        : false;

    const [isWithAllItemsShown, setIsWithAllItemsShown] = useState(isWithHiddenCheckedItem);

    const shownOptions = isCheckboxType ? parameter.values.slice(0, defaultNumberOfShownParameters) : [];
    const defaultOptions = isCheckboxType ? (isWithAllItemsShown ? parameter.values : shownOptions) : [];

    return (
        <FilterGroupWrapper dataTestId={dataTestId}>
            <FilterGroupTitle
                isOpen={!isGroupCollapsed}
                title={title}
                onClick={() => setIsGroupCollapsed(!isGroupCollapsed)}
            />

            {!isGroupCollapsed && (
                <FilterGroupContent>
                    {isCheckboxType && (
                        <>
                            {defaultOptions.map((parameterValueOption, index) => {
                                const isChecked = getIsSelectedParameterValue(
                                    defaultSelectedParameters,
                                    selectedParameter?.values,
                                    parameter.uuid,
                                    parameterValueOption.uuid,
                                );
                                const id = `parameters.${parameterIndex}.values.${index}.checked`;
                                const isDisabled = parameterValueOption.count === 0 && !isChecked;

                                return (
                                    <FilterGroupContentItem
                                        key={parameterValueOption.uuid}
                                        dataTestId={getDataTestId(parameterIndex) + '-' + index}
                                        isDisabled={isDisabled}
                                    >
                                        <Checkbox
                                            count={parameterValueOption.count}
                                            disabled={isDisabled}
                                            id={id}
                                            label={parameterValueOption.text}
                                            name={id}
                                            value={isChecked}
                                            onChange={() =>
                                                updateFilterParameters(parameter.uuid, parameterValueOption.uuid)
                                            }
                                        />
                                    </FilterGroupContentItem>
                                );
                            })}

                            {!!hiddenOptions.length && (
                                <ShowAllButton onClick={() => setIsWithAllItemsShown((prev) => !prev)}>
                                    {isWithAllItemsShown ? t('show less') : t('show more')}
                                </ShowAllButton>
                            )}
                        </>
                    )}

                    {parameter.__typename === 'ParameterColorFilterOption' && (
                        <div className="flex flex-wrap gap-1">
                            {parameter.values.map((parameterValue, index) => {
                                const isChecked = getIsSelectedParameterValue(
                                    defaultSelectedParameters,
                                    selectedParameter?.values,
                                    parameter.uuid,
                                    parameterValue.uuid,
                                );
                                const id = `parameters.${parameterIndex}.values.${index}.checked`;

                                return (
                                    <CheckboxColor
                                        key={parameterValue.uuid}
                                        bgColor={parameterValue.rgbHex ?? undefined}
                                        dataTestId={getDataTestId(index)}
                                        disabled={parameterValue.count === 0 && !isChecked}
                                        id={id}
                                        label={parameterValue.text}
                                        name={id}
                                        value={isChecked}
                                        onChange={() => updateFilterParameters(parameter.uuid, parameterValue.uuid)}
                                    />
                                );
                            })}
                        </div>
                    )}
                    {parameter.__typename === 'ParameterSliderFilterOption' && (
                        <RangeSlider
                            isDisabled={!parameter.isSelectable}
                            max={parameter.maximalValue}
                            maxValue={selectedParameter?.maximalValue ?? parameter.maximalValue}
                            min={parameter.minimalValue}
                            minValue={selectedParameter?.minimalValue ?? parameter.minimalValue}
                            setMaxValueCallback={(value) =>
                                updateFilterParameters(
                                    parameter.uuid,
                                    undefined,
                                    selectedParameter?.minimalValue,
                                    parameter.maximalValue === value ? undefined : value,
                                )
                            }
                            setMinValueCallback={(value) =>
                                updateFilterParameters(
                                    parameter.uuid,
                                    undefined,
                                    parameter.minimalValue === value ? undefined : value,
                                    selectedParameter?.maximalValue,
                                )
                            }
                        />
                    )}
                </FilterGroupContent>
            )}
        </FilterGroupWrapper>
    );
};

const getIsSelectedParameterValue = (
    defaultSelectedParameters: DefaultProductFiltersMapType['parameters'],
    parameterValues: string[] | undefined,
    parameterUuid: string,
    parameterValueUuid: string,
) => {
    const isSelectedByDefault = !!defaultSelectedParameters.get(parameterUuid)?.has(parameterValueUuid);
    const isSelectedByUser = !!parameterValues?.includes(parameterValueUuid) || isSelectedByDefault;
    return isSelectedByDefault || isSelectedByUser;
};
