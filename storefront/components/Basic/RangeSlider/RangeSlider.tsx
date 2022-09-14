import {
    RangeSliderContainerStyled,
    RangeSliderLeftValueStyled,
    RangeSliderRangeStyled,
    RangeSliderRightValueStyled,
    RangeSliderStyled,
    RangeSliderThumbStyled,
    RangeSliderTrackStyled,
} from './RangeSlider.style';
import { TextInput } from 'components/Forms/TextInput/TextInput';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import {
    ChangeEvent,
    ChangeEventHandler,
    FC,
    FocusEventHandler,
    KeyboardEventHandler,
    useCallback,
    useEffect,
    useRef,
    useState,
} from 'react';

/*
 * Inspired by
 * @see: https://dev.to/sandra_lewis/building-a-multi-range-slider-in-react-from-scratch-4dl1
 */
type RangeSliderProps = {
    min: number;
    max: number;
    delay?: number;
    minValue: number;
    maxValue: number;
    setMinValueCallback: (value: number) => void;
    setMaxValueCallback: (value: number) => void;
};

const TEST_IDENTIFIER = 'basic-rangeslider';

export const RangeSlider: FC<RangeSliderProps> = ({
    min,
    max,
    delay = 300,
    minValue,
    maxValue,
    setMinValueCallback,
    setMaxValueCallback,
}) => {
    const t = useTypedTranslationFunction();

    const [minValueInput, setMinValueInput] = useState(min);
    const [minValueThumb, setMinValueThumb] = useState(min);

    const [maxValueInput, setMaxValueInput] = useState(max);
    const [maxValueThumb, setMaxValueThumb] = useState(max);

    const range = useRef<HTMLDivElement>(null);

    useEffect(() => {
        const timer = setTimeout(() => {
            if (minValueThumb !== minValue) {
                setMinValueCallback(minValueThumb);
            }
        }, delay);

        return () => clearTimeout(timer);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [minValueThumb, delay]);

    useEffect(() => {
        const timer = setTimeout(() => {
            if (maxValueThumb !== maxValue) {
                setMaxValueCallback(maxValueThumb);
            }
        }, delay);

        return () => clearTimeout(timer);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [maxValueThumb, delay]);

    useEffect(() => {
        if (minValue < min) {
            setMinValueThumb(min);
            setMinValueInput(min);
            setMinValueCallback(min);
        } else if (minValue > maxValue) {
            setMinValueThumb(maxValue);
            setMinValueInput(maxValue);
            setMinValueCallback(maxValue);
        } else {
            setMinValueThumb(minValue);
            setMinValueInput(minValue);
            setMinValueCallback(minValue);
        }
    }, [maxValue, minValue, min, setMinValueCallback]);

    useEffect(() => {
        if (maxValue > max) {
            setMaxValueThumb(max);
            setMaxValueInput(max);
            setMaxValueCallback(max);
        } else if (maxValue < minValue) {
            setMaxValueThumb(minValue);
            setMaxValueInput(minValue);
            setMaxValueCallback(minValue);
        } else {
            setMaxValueThumb(maxValue);
            setMaxValueInput(maxValue);
            setMaxValueCallback(maxValue);
        }
    }, [maxValue, minValue, max, setMaxValueCallback]);

    const getPercent = useCallback((value: number) => Math.round(((value - min) / (max - min)) * 100), [min, max]);

    const onBlurMinHandler = useCallback<FocusEventHandler<HTMLInputElement>>(
        (event) => {
            const value = parseFloat(event.currentTarget.value);
            if (value < min || Number.isNaN(value)) {
                setMinValueThumb(min);
                setMinValueInput(min);
            } else {
                setMinValueCallback(value);
            }
        },
        [min, setMinValueCallback],
    );

    const onBlurMaxHandler = useCallback<FocusEventHandler<HTMLInputElement>>(
        (event) => {
            const value = parseFloat(event.currentTarget.value);
            if (value > max || Number.isNaN(value)) {
                setMaxValueThumb(max);
                setMaxValueInput(max);
            } else {
                setMaxValueCallback(value);
            }
        },
        [max, setMaxValueCallback],
    );

    const onChangeMaxInputHandler = useCallback<ChangeEventHandler<HTMLInputElement>>(
        (event) => setMaxValueInput(parseFloat(event.currentTarget.value)),
        [],
    );

    const onChangeMinInputHandler = useCallback<ChangeEventHandler<HTMLInputElement>>(
        (event) => setMinValueInput(parseFloat(event.currentTarget.value)),
        [],
    );

    const onKeyPressHandler = useCallback<KeyboardEventHandler<HTMLInputElement>>(
        (event) => event.key === 'Enter' && event.currentTarget.blur(),
        [],
    );

    const onChangeMinHandler = (event: ChangeEvent<HTMLInputElement>) => {
        const value = Math.min(Number(event.target.value), maxValueThumb);
        setMinValueThumb(value);
        setMinValueInput(value);
    };

    const onChangeMaxHandler = (event: ChangeEvent<HTMLInputElement>) => {
        const value = Math.max(Number(event.target.value), minValueThumb);
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
        <RangeSliderContainerStyled data-testid={TEST_IDENTIFIER}>
            <RangeSliderThumbStyled
                active={minValueThumb !== min}
                type="range"
                min={min}
                max={max}
                value={minValueThumb}
                onChange={onChangeMinHandler}
                data-testid={TEST_IDENTIFIER + '-left-thumb'}
            />
            <RangeSliderThumbStyled
                active={maxValueThumb !== max}
                type="range"
                min={min}
                max={max}
                value={maxValueThumb}
                onChange={onChangeMaxHandler}
                data-testid={TEST_IDENTIFIER + '-right-thumb'}
            />
            <RangeSliderStyled>
                <RangeSliderTrackStyled />
                <RangeSliderRangeStyled ref={range} />
                <RangeSliderLeftValueStyled data-testid={TEST_IDENTIFIER + '-left-value'}>
                    <TextInput
                        label={t('from')}
                        type="number"
                        inputSize="small"
                        value={minValueInput}
                        onChange={onChangeMinInputHandler}
                        onBlurCapture={onBlurMinHandler}
                        onKeyPress={onKeyPressHandler}
                    />
                </RangeSliderLeftValueStyled>
                <RangeSliderRightValueStyled data-testid={TEST_IDENTIFIER + '-right-value'}>
                    <TextInput
                        label={t('to')}
                        type="number"
                        inputSize="small"
                        value={maxValueInput}
                        onChange={onChangeMaxInputHandler}
                        onBlurCapture={onBlurMaxHandler}
                        onKeyPress={onKeyPressHandler}
                    />
                </RangeSliderRightValueStyled>
            </RangeSliderStyled>
        </RangeSliderContainerStyled>
    );
};
