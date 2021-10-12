import { ChangeEvent, FC, useCallback, useEffect, useRef, useState } from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import {
    RangeSliderContainerStyled,
    RangeSliderLeftThumbStyled,
    RangeSliderLeftValueStyled,
    RangeSliderRangeStyled,
    RangeSliderRightThumbStyled,
    RangeSliderRightValueStyled,
    RangeSliderStyled,
    RangeSliderTrackStyled,
} from './RangeSlider.style';
import TextInput from 'components/Forms/TextInput';
import useDebounce from 'hooks/helpers/UseDebounce';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

/*
 * Shopsys slider component inspired by
 * @see: https://dev.to/sandra_lewis/building-a-multi-range-slider-in-react-from-scratch-4dl1
 */
interface RangeSliderProps {
    min: number;
    max: number;
    delay: number;
}

const RangeSlider: FC<RangeSliderProps> = ({ min, max, delay }) => {
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext();

    const [minValue, setMinValue] = useState(min);
    const debouncedMinValue = useDebounce(minValue, delay);
    const minValueRef = useRef(min);

    const [maxValue, setMaxValue] = useState(max);
    const debouncedMaxValue = useDebounce(maxValue, delay);
    const maxValueRef = useRef(max);

    const range = useRef<HTMLDivElement>(null);

    // set values to input
    useEffect(() => {
        formProviderMethods.setValue('minPrice', minValue);
    }, [minValue]);

    useEffect(() => {
        formProviderMethods.setValue('maxPrice', maxValue);
    }, [maxValue]);

    useEffect(() => {
        // TODO PRG
        // eslint-disable-next-line no-console
        console.log('change products');
    }, [debouncedMinValue]);

    useEffect(() => {
        // TODO PRG
        // eslint-disable-next-line no-console
        console.log('change products');
    }, [debouncedMaxValue]);

    // Convert to percentage
    const getPercent = useCallback((value: number) => Math.round(((value - min) / (max - min)) * 100), [min, max]);

    const onChangeMinHanlder = (event: ChangeEvent<HTMLInputElement>) => {
        const value = Math.min(Number(event.target.value), maxValue - 1);
        setMinValue(value);
        minValueRef.current = value;
    };

    const onChangeMaxHanlder = (event: ChangeEvent<HTMLInputElement>) => {
        const value = Math.max(Number(event.target.value), minValue + 1);
        setMaxValue(value);
        maxValueRef.current = value;
    };

    // Set width of the range to decrease from the left side
    useEffect(() => {
        const minPercent = getPercent(minValue);
        const maxPercent = getPercent(maxValueRef.current);

        if (range.current) {
            range.current.style.left = `${minPercent}%`;
            range.current.style.width = `${maxPercent - minPercent}%`;
        }
    }, [minValue]);

    // Set width of the range to decrease from the right side
    useEffect(() => {
        const minPercent = getPercent(minValueRef.current);
        const maxPercent = getPercent(maxValue);

        if (range.current) {
            range.current.style.width = `${maxPercent - minPercent}%`;
        }
    }, [maxValue]);

    return (
        <RangeSliderContainerStyled>
            <RangeSliderLeftThumbStyled
                type="range"
                min={min}
                max={max}
                value={minValue}
                onChange={onChangeMinHanlder}
            />
            <RangeSliderRightThumbStyled
                type="range"
                min={min}
                max={max}
                value={maxValue}
                onChange={onChangeMaxHanlder}
            />
            <RangeSliderStyled>
                <RangeSliderTrackStyled />
                <RangeSliderRangeStyled ref={range} />
                <RangeSliderLeftValueStyled>
                    <Controller
                        name="minPrice"
                        render={({ field }) => (
                            <TextInput
                                name={field.name}
                                label={t('From')}
                                type="text"
                                inputSize={'small'}
                                fieldRef={field}
                            />
                        )}
                    />
                </RangeSliderLeftValueStyled>
                <RangeSliderRightValueStyled>
                    <Controller
                        name="maxPrice"
                        render={({ field }) => (
                            <TextInput
                                name={field.name}
                                label={t('To')}
                                type="text"
                                inputSize={'small'}
                                fieldRef={field}
                            />
                        )}
                    />
                </RangeSliderRightValueStyled>
            </RangeSliderStyled>
        </RangeSliderContainerStyled>
    );
};

export default RangeSlider;
