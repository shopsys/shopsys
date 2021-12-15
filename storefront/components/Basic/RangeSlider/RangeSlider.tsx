import { ChangeEvent, FC, useCallback, useEffect, useRef, useState } from 'react';
import { Controller, useFormContext, useWatch } from 'react-hook-form';
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
import { FilterFormType } from 'types/productFilter';
import TextInput from 'components/Forms/TextInput';
import useDebounce from 'hooks/helpers/UseDebounce';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

/*
 * Shopsys slider component inspired by
 * @see: https://dev.to/sandra_lewis/building-a-multi-range-slider-in-react-from-scratch-4dl1
 */
type RangeSliderProps = {
    min: number;
    max: number;
    delay: number;
};

const RangeSlider: FC<RangeSliderProps> = (props) => {
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext<FilterFormType>();
    const [minimalPriceValue, maximalPriceValue] = useWatch({
        name: ['minimalPrice', 'maximalPrice'],
        control: formProviderMethods.control,
    });

    const [minValue, setMinValue] = useState(props.min);
    const debouncedMinValue = useDebounce(minValue, props.delay);

    const [maxValue, setMaxValue] = useState(props.max);
    const debouncedMaxValue = useDebounce(maxValue, props.delay);

    const range = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (minValue !== minimalPriceValue) {
            formProviderMethods.setValue('minimalPrice', minValue);
        }
    }, [debouncedMinValue]);

    useEffect(() => {
        if (maxValue !== maximalPriceValue) {
            formProviderMethods.setValue('maximalPrice', maxValue);
        }
    }, [debouncedMaxValue]);

    useEffect(() => {
        if (minimalPriceValue < props.min) {
            setMinValue(props.min);
            formProviderMethods.setValue('minimalPrice', props.min);
        } else if (minimalPriceValue > maximalPriceValue) {
            setMinValue(maximalPriceValue - 1);
            formProviderMethods.setValue('minimalPrice', maximalPriceValue - 1);
        } else {
            setMinValue(minimalPriceValue);
            formProviderMethods.setValue('minimalPrice', minimalPriceValue);
        }
    }, [minimalPriceValue]);

    useEffect(() => {
        if (maximalPriceValue > props.max) {
            setMaxValue(props.max);
            formProviderMethods.setValue('maximalPrice', props.max);
        } else if (maximalPriceValue < minimalPriceValue) {
            setMaxValue(minimalPriceValue + 1);
            formProviderMethods.setValue('maximalPrice', minimalPriceValue + 1);
        } else {
            setMaxValue(maximalPriceValue);
            formProviderMethods.setValue('maximalPrice', maximalPriceValue);
        }
    }, [maximalPriceValue]);

    // Convert to percentage
    const getPercent = useCallback(
        (value: number) => Math.round(((value - props.min) / (props.max - props.min)) * 100),
        [props.min, props.max],
    );

    const onChangeMinHanlder = (event: ChangeEvent<HTMLInputElement>) => {
        const value = Math.min(Number(event.target.value), maxValue - 1);
        setMinValue(value);
    };

    const onChangeMaxHanlder = (event: ChangeEvent<HTMLInputElement>) => {
        const value = Math.max(Number(event.target.value), minValue + 1);
        setMaxValue(value);
    };

    // Set width of the range to decrease from the left side
    useEffect(() => {
        const minPercent = getPercent(minValue);
        const maxPercent = getPercent(maxValue);

        if (range.current) {
            range.current.style.left = `${minPercent}%`;
            range.current.style.width = `${maxPercent - minPercent}%`;
        }
    }, [minValue]);

    // Set width of the range to decrease from the right side
    useEffect(() => {
        const minPercent = getPercent(minValue);
        const maxPercent = getPercent(maxValue);

        if (range.current) {
            range.current.style.width = `${maxPercent - minPercent}%`;
        }
    }, [maxValue]);

    return (
        <RangeSliderContainerStyled>
            <RangeSliderLeftThumbStyled
                type="range"
                min={props.min}
                max={props.max}
                value={minValue}
                onChange={onChangeMinHanlder}
            />
            <RangeSliderRightThumbStyled
                type="range"
                min={props.min}
                max={props.max}
                value={maxValue}
                onChange={onChangeMaxHanlder}
            />
            <RangeSliderStyled>
                <RangeSliderTrackStyled />
                <RangeSliderRangeStyled ref={range} />
                <RangeSliderLeftValueStyled>
                    <Controller
                        name="minimalPrice"
                        render={({ field }) => (
                            <TextInput
                                name={field.name}
                                label={t('from')}
                                type="number"
                                inputSize={'small'}
                                fieldRef={field}
                                value={minValue}
                            />
                        )}
                    />
                </RangeSliderLeftValueStyled>
                <RangeSliderRightValueStyled>
                    <Controller
                        name="maximalPrice"
                        render={({ field }) => (
                            <TextInput
                                name={field.name}
                                label={t('to')}
                                type="number"
                                inputSize={'small'}
                                fieldRef={field}
                                value={maxValue}
                            />
                        )}
                    />
                </RangeSliderRightValueStyled>
            </RangeSliderStyled>
        </RangeSliderContainerStyled>
    );
};

export default RangeSlider;
