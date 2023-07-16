import {
    FilterGroupContent,
    FilterGroupContentItem,
    FilterGroupTitle,
    FilterGroupWrapper,
    ShowAllButton,
} from './FilterElements';
import { FilterGroupIcon } from './FilterGroupIcon';
import { RangeSlider } from 'components/Basic/RangeSlider/RangeSlider';
import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import { CheckboxColor } from 'components/Forms/CheckboxColor/CheckboxColor';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useQueryParams } from 'hooks/useQueryParams';
import { useState } from 'react';
import { ParametersType } from 'types/productFilter';
import { useSessionStore } from 'store/zustand/useSessionStore';
import { DefaultProductFiltersMapType } from 'store/zustand/slices/createSeoCategorySlice';

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
    const t = useTypedTranslationFunction();
    const [isGroupCollapsed, setIsGroupCollapsed] = useState(parameter.isCollapsed);
    const {
        filter: { parameters: selectedParametersUuids },
        updateFilterParameters,
    } = useQueryParams();
    const defaultSelectedParameters = useSessionStore((s) => s.defaultProductFiltersMap.parameters);

    const selectedParameter = selectedParametersUuids?.find((p) => p.parameter === parameter.uuid);

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
            <FilterGroupTitle onClick={() => setIsGroupCollapsed((currentGroupVisibility) => !currentGroupVisibility)}>
                {title}
                <FilterGroupIcon isOpen={!isGroupCollapsed} />
            </FilterGroupTitle>
            <FilterGroupContent isOpen={!isGroupCollapsed}>
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

                            return (
                                <FilterGroupContentItem
                                    key={parameterValueOption.uuid}
                                    isDisabled={parameterValueOption.count === 0 && !isChecked}
                                    dataTestId={getDataTestId(parameterIndex) + '-' + index}
                                >
                                    <Checkbox
                                        id={id}
                                        name={id}
                                        label={parameterValueOption.text}
                                        onChange={() =>
                                            updateFilterParameters(parameter.uuid, parameterValueOption.uuid)
                                        }
                                        value={isChecked}
                                        count={parameterValueOption.count}
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
                    <div className="flex flex-wrap">
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
                                    id={id}
                                    name={id}
                                    disabled={parameterValue.count === 0 && !isChecked}
                                    onChange={() => updateFilterParameters(parameter.uuid, parameterValue.uuid)}
                                    value={isChecked}
                                    label={parameterValue.text}
                                />
                            );
                        })}
                    </div>
                )}
                {parameter.__typename === 'ParameterSliderFilterOption' && (
                    <RangeSlider
                        min={parameter.minimalValue}
                        max={parameter.maximalValue}
                        minValue={selectedParameter?.minimalValue ?? parameter.minimalValue}
                        maxValue={selectedParameter?.maximalValue ?? parameter.maximalValue}
                        setMinValueCallback={(value) =>
                            updateFilterParameters(
                                parameter.uuid,
                                undefined,
                                parameter.minimalValue === value ? undefined : value,
                                selectedParameter?.maximalValue,
                            )
                        }
                        setMaxValueCallback={(value) =>
                            updateFilterParameters(
                                parameter.uuid,
                                undefined,
                                selectedParameter?.minimalValue,
                                parameter.maximalValue === value ? undefined : value,
                            )
                        }
                    />
                )}
            </FilterGroupContent>
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
