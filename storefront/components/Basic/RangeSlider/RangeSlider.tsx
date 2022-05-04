import { ChangeEvent, FC, FocusEvent, KeyboardEvent, useCallback, useEffect, useRef, useState } from 'react';
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
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { FilterFormType } from 'types/productFilter';
import { optionsFilterActions } from 'redux/slices/optionsFilter';
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
    const testIdentifier = 'basic-rangeslider';

    const t = useTypedTranslationFunction();
    const { control, setValue } = useFormContext<FilterFormType>();
    const parametersFilterState = useShopsysSelector((state) => state.optionsFilter);
    const [minimalPriceValue, maximalPriceValue] = useWatch({
        name: ['minimalPrice', 'maximalPrice'],
        control,
    });
    const dispatch = useShopsysDispatch();

    const [minValueInput, setMinValueInput] = useState(props.min);
    const [minValueThumb, setMinValueThumb] = useState(props.min);
    const debouncedMinValue = useDebounce(minValueThumb, props.delay);

    const [maxValueInput, setMaxValueInput] = useState(props.max);
    const [maxValueThumb, setMaxValueThumb] = useState(props.max);
    const debouncedMaxValue = useDebounce(maxValueThumb, props.delay);

    const range = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (minValueThumb !== minimalPriceValue) {
            setValue('minimalPrice', minValueThumb);
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [debouncedMinValue, setValue]);

    useEffect(() => {
        if (maxValueThumb !== maximalPriceValue) {
            setValue('maximalPrice', maxValueThumb);
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [debouncedMaxValue, setValue]);

    useEffect(() => {
        if (minimalPriceValue < props.min) {
            setMinValueThumb(props.min);
            setMinValueInput(props.min);
            setValue('minimalPrice', props.min);
        } else if (minimalPriceValue > maximalPriceValue) {
            setMinValueThumb(maximalPriceValue - 1);
            setMinValueInput(maximalPriceValue - 1);
            setValue('minimalPrice', maximalPriceValue - 1);
        } else {
            setMinValueThumb(minimalPriceValue);
            setMinValueInput(minimalPriceValue);
            setValue('minimalPrice', minimalPriceValue);
        }
    }, [maximalPriceValue, minimalPriceValue, props.min, setValue]);

    useEffect(() => {
        if (maximalPriceValue > props.max) {
            setMaxValueThumb(props.max);
            setMaxValueInput(props.max);
            setValue('maximalPrice', props.max);
        } else if (maximalPriceValue < minimalPriceValue) {
            setMaxValueThumb(minimalPriceValue + 1);
            setMaxValueInput(minimalPriceValue + 1);
            setValue('maximalPrice', minimalPriceValue + 1);
        } else {
            setMaxValueThumb(maximalPriceValue);
            setMaxValueInput(maximalPriceValue);
            setValue('maximalPrice', maximalPriceValue);
        }
    }, [maximalPriceValue, minimalPriceValue, props.max, setValue]);

    // Convert to percentage
    const getPercent = useCallback(
        (value: number) => Math.round(((value - props.min) / (props.max - props.min)) * 100),
        [props.min, props.max],
    );

    const onBlurMinHandler = (value: number) => {
        if (
            (parametersFilterState.minimalPrice === null || parametersFilterState.minimalPrice === props.min) &&
            (value < props.min || Number.isNaN(value))
        ) {
            setMinValueThumb(props.min);
            setMinValueInput(props.min);
        } else if (
            parametersFilterState.minimalPrice !== null &&
            parametersFilterState.minimalPrice > props.min &&
            (value < props.min || Number.isNaN(value))
        ) {
            dispatch(optionsFilterActions.setMinimalPriceFilter(props.min));
            setMinValueThumb(props.min);
            setMinValueInput(props.min);
        } else {
            setValue('minimalPrice', value);
        }
    };

    const onBlurMaxHandler = (value: number) => {
        if (
            (parametersFilterState.maximalPrice === null || parametersFilterState.maximalPrice === props.max) &&
            (value > props.max || Number.isNaN(value))
        ) {
            setMaxValueThumb(props.max);
            setMaxValueInput(props.max);
        } else if (
            parametersFilterState.maximalPrice !== null &&
            parametersFilterState.maximalPrice < props.max &&
            (value > props.max || Number.isNaN(value))
        ) {
            dispatch(optionsFilterActions.setMaximalPriceFilter(props.max));
            setMaxValueThumb(props.max);
            setMaxValueInput(props.max);
        } else {
            setValue('maximalPrice', value);
        }
    };

    const onChangeMinHandler = (event: ChangeEvent<HTMLInputElement>) => {
        const value = Math.min(Number(event.target.value), maxValueThumb - 1);
        setMinValueThumb(value);
        setMinValueInput(value);
    };

    const onChangeMaxHandler = (event: ChangeEvent<HTMLInputElement>) => {
        const value = Math.max(Number(event.target.value), minValueThumb + 1);
        setMaxValueThumb(value);
        setMaxValueInput(value);
    };

    // Set width of the range to decrease from the left side
    useEffect(() => {
        const minPercent = getPercent(minValueThumb);
        const maxPercent = getPercent(maxValueThumb);

        if (range.current) {
            range.current.style.left = `${minPercent}%`;
            range.current.style.width = `${maxPercent - minPercent}%`;
        }
    }, [getPercent, maxValueThumb, minValueThumb]);

    // Set width of the range to decrease from the right side
    useEffect(() => {
        const minPercent = getPercent(minValueThumb);
        const maxPercent = getPercent(maxValueThumb);

        if (range.current) {
            range.current.style.width = `${maxPercent - minPercent}%`;
        }
    }, [getPercent, maxValueThumb, minValueThumb]);

    return (
        <RangeSliderContainerStyled data-testid={testIdentifier}>
            <RangeSliderLeftThumbStyled
                type="range"
                min={props.min}
                max={props.max}
                value={minValueThumb}
                onChange={onChangeMinHandler}
                data-testid={testIdentifier + '-left-thumb'}
            />
            <RangeSliderRightThumbStyled
                type="range"
                min={props.min}
                max={props.max}
                value={maxValueThumb}
                onChange={onChangeMaxHandler}
                data-testid={testIdentifier + '-right-thumb'}
            />
            <RangeSliderStyled>
                <RangeSliderTrackStyled />
                <RangeSliderRangeStyled ref={range} />
                <RangeSliderLeftValueStyled data-testid={testIdentifier + '-left-value'}>
                    <Controller
                        name="minimalPrice"
                        render={({ field }) => (
                            <TextInput
                                name={field.name}
                                label={t('from')}
                                type="number"
                                inputSize={'small'}
                                fieldRef={field}
                                value={minValueInput}
                                onChange={(e: FocusEvent<HTMLInputElement>) =>
                                    setMinValueInput(parseFloat(e.currentTarget.value))
                                }
                                onBlurCapture={(event) => onBlurMinHandler(parseFloat(event.currentTarget.value))}
                                onKeyPress={(event: KeyboardEvent<HTMLInputElement>) =>
                                    event.key === 'Enter' && event.currentTarget.blur()
                                }
                            />
                        )}
                    />
                </RangeSliderLeftValueStyled>
                <RangeSliderRightValueStyled data-testid={testIdentifier + '-right-value'}>
                    <Controller
                        name="maximalPrice"
                        render={({ field }) => (
                            <TextInput
                                name={field.name}
                                label={t('to')}
                                type="number"
                                inputSize={'small'}
                                fieldRef={field}
                                value={maxValueInput}
                                onChange={(e: FocusEvent<HTMLInputElement>) =>
                                    setMaxValueInput(parseFloat(e.currentTarget.value))
                                }
                                onBlurCapture={(event) => onBlurMaxHandler(parseFloat(event.currentTarget.value))}
                                onKeyPress={(event: KeyboardEvent<HTMLInputElement>) =>
                                    event.key === 'Enter' && event.currentTarget.blur()
                                }
                            />
                        )}
                    />
                </RangeSliderRightValueStyled>
            </RangeSliderStyled>
        </RangeSliderContainerStyled>
    );
};

export default RangeSlider;
