import { RangeSlider } from 'components/Basic/RangeSlider/RangeSlider';
import { useFilterState } from 'components/Blocks/Product/Filter/FilterContext/useFilterState';
import { FC, useCallback, useMemo } from 'react';

type SliderFilterProps = {
    parameterParentIndex: number;
    min: number;
    max: number;
};

export const SliderFilter: FC<SliderFilterProps> = ({ min, max, parameterParentIndex }) => {
    const [state, dispatch] = useFilterState();
    const minimalValue = useMemo(
        () => state.selected.parameters[parameterParentIndex]?.minimalValue,
        [parameterParentIndex, state.selected.parameters],
    );
    const maximalValue = useMemo(
        () => state.selected.parameters[parameterParentIndex]?.maximalValue,
        [parameterParentIndex, state.selected.parameters],
    );

    const setMinimalPrice = useCallback(
        (value: number) => {
            if (minimalValue !== value) {
                dispatch({
                    type: 'setSliderParameter',
                    payload: {
                        value: value !== min ? value : null,
                        type: 'minimalValue',
                        index: parameterParentIndex,
                    },
                });
            }
        },
        [dispatch, min, minimalValue, parameterParentIndex],
    );

    const setMaximalPrice = useCallback(
        (value: number) => {
            if (maximalValue !== value) {
                dispatch({
                    type: 'setSliderParameter',
                    payload: {
                        value: value !== max ? value : null,
                        type: 'maximalValue',
                        index: parameterParentIndex,
                    },
                });
            }
        },
        [dispatch, max, maximalValue, parameterParentIndex],
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
