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
    const { filter, updateFilterParameters } = useQueryParams();

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
            <FilterGroupTitle onClick={() => setIsGroupCollapsed((currentGroupVisibility) => !currentGroupVisibility)}>
                {title}
                <FilterGroupIcon isOpen={!isGroupCollapsed} />
            </FilterGroupTitle>
            <FilterGroupContent isOpen={!isGroupCollapsed}>
                {isCheckboxType && (
                    <>
                        {defaultOptions.map((parameterOption, index) => {
                            const isChecked = !!selectedParameter?.values?.includes(parameterOption.uuid);
                            const id = `parameters.${parameterIndex}.values.${index}.checked`;

                            return (
                                <FilterGroupContentItem
                                    key={parameterOption.uuid}
                                    isDisabled={parameterOption.count === 0 && !isChecked}
                                    dataTestId={getDataTestId(parameterIndex) + '-' + index}
                                >
                                    <Checkbox
                                        id={id}
                                        name={id}
                                        label={parameterOption.text}
                                        onChange={() => updateFilterParameters(parameter.uuid, parameterOption.uuid)}
                                        value={isChecked}
                                        count={parameterOption.count}
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
                        {parameter.values.map((parameterOption, index) => {
                            const isChecked = !!selectedParameter?.values?.includes(parameterOption.uuid);
                            const id = `parameters.${parameterIndex}.values.${index}.checked`;

                            return (
                                <CheckboxColor
                                    key={parameterOption.uuid}
                                    bgColor={parameterOption.rgbHex ?? undefined}
                                    dataTestId={getDataTestId(index)}
                                    id={id}
                                    name={id}
                                    disabled={parameterOption.count === 0 && !isChecked}
                                    onChange={() => updateFilterParameters(parameter.uuid, parameterOption.uuid)}
                                    value={isChecked}
                                    label={parameterOption.text}
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
