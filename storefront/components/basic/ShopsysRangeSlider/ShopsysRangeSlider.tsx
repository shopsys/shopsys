// eslint-disable-next-line no-use-before-define
import React, { ChangeEvent, FC, useCallback, useEffect, useRef, useState } from 'react';

import {
    ShopsysRangeSliderContainerStyled,
    ShopsysRangeSliderLeftThumbStyled,
    ShopsysRangeSliderLeftValueStyled,
    ShopsysRangeSliderRangeStyled,
    ShopsysRangeSliderRightThumbStyled,
    ShopsysRangeSliderRightValueStyled,
    ShopsysRangeSliderStyled,
    ShopsysRangeSliderTrackStyled,
} from './ShopsysRangeSlider.style';

/*
 * Shopsys slider component inspired by
 * @see: https://dev.to/sandra_lewis/building-a-multi-range-slider-in-react-from-scratch-4dl1
 */

interface ShopsysRangeSliderProps {
    min: number;
    max: number;
    onChange: Function;
}

const ShopsysRangeSlider: FC<ShopsysRangeSliderProps> = ({ min, max, onChange }) => {
    const [minVal, setMinVal] = useState(min);
    const [maxVal, setMaxVal] = useState(max);
    const minValRef = useRef(min);
    const maxValRef = useRef(max);
    const range = useRef<HTMLDivElement>(null);

    // Convert to percentage
    const getPercent = useCallback((value: number) => Math.round(((value - min) / (max - min)) * 100), [min, max]);

    // Set width of the range to decrease from the left side
    useEffect(() => {
        const minPercent = getPercent(minVal);
        const maxPercent = getPercent(maxValRef.current);

        if (range.current) {
            range.current.style.left = `${minPercent}%`;
            range.current.style.width = `${maxPercent - minPercent}%`;
        }
    }, [minVal, getPercent]);

    // Set width of the range to decrease from the right side
    useEffect(() => {
        const minPercent = getPercent(minValRef.current);
        const maxPercent = getPercent(maxVal);

        if (range.current) {
            range.current.style.width = `${maxPercent - minPercent}%`;
        }
    }, [maxVal, getPercent]);

    // Get min and max values when their state changes
    useEffect(() => {
        onChange({ min: minVal, max: maxVal });
    }, [minVal, maxVal, onChange]);

    return (
        <ShopsysRangeSliderContainerStyled>
            <ShopsysRangeSliderLeftThumbStyled
                type="range"
                min={min}
                max={max}
                value={minVal}
                onChange={(event: ChangeEvent<HTMLInputElement>) => {
                    const value = Math.min(Number(event.target.value), maxVal - 1);
                    setMinVal(value);
                    minValRef.current = value;
                }}
                style={{ zIndex: minVal > max - 100 && '5' }}
            />
            <ShopsysRangeSliderRightThumbStyled
                type="range"
                min={min}
                max={max}
                value={maxVal}
                onChange={(event: ChangeEvent<HTMLInputElement>) => {
                    const value = Math.max(Number(event.target.value), minVal + 1);
                    setMaxVal(value);
                    maxValRef.current = value;
                }}
            />

            <ShopsysRangeSliderStyled>
                <ShopsysRangeSliderTrackStyled />
                <ShopsysRangeSliderRangeStyled ref={range} />
                <ShopsysRangeSliderLeftValueStyled>{minVal}</ShopsysRangeSliderLeftValueStyled>
                <ShopsysRangeSliderRightValueStyled>{maxVal}</ShopsysRangeSliderRightValueStyled>
            </ShopsysRangeSliderStyled>
        </ShopsysRangeSliderContainerStyled>
    );
};

export default ShopsysRangeSlider;
