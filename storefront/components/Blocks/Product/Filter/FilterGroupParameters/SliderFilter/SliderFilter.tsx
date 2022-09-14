import { RangeSlider } from 'components/Basic/RangeSlider/RangeSlider';
import { FC, useCallback } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { FilterFormType } from 'types/productFilter';

type SliderFilterProps = {
    parameterParentIndex: number;
    min: number;
    max: number;
};

export const SliderFilter: FC<SliderFilterProps> = ({ min, max, parameterParentIndex }) => {
    const minValueName = `parameters.${parameterParentIndex}.minimalValue` as const;
    const maxValueName = `parameters.${parameterParentIndex}.maximalValue` as const;

    const { control, setValue } = useFormContext<FilterFormType>();
    const [minimalValue, maximalValue] = useWatch({
        name: [minValueName, maxValueName],
        control,
    });

    const setMinimalPrice = useCallback(
        (value: number) => {
            setValue(minValueName, value !== min ? value : null);
        },
        [min, minValueName, setValue],
    );

    const setMaximalPrice = useCallback(
        (value: number) => {
            setValue(maxValueName, value !== max ? value : null);
        },
        [max, maxValueName, setValue],
    );

    return (
        <RangeSlider
            min={min}
            max={max}
            minValue={minimalValue ?? min}
            maxValue={maximalValue ?? max}
            setMinValueCallback={setMinimalPrice}
            setMaxValueCallback={setMaximalPrice}
        />
    );
};
