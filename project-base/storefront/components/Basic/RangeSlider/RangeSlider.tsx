import { TextInput } from 'components/Forms/TextInput/TextInput';
import useTranslation from 'next-translate/useTranslation';
import {
    ChangeEvent,
    ChangeEventHandler,
    DetailedHTMLProps,
    FocusEventHandler,
    InputHTMLAttributes,
    KeyboardEventHandler,
    useCallback,
    useEffect,
    useRef,
    useState,
} from 'react';
import { twJoin } from 'tailwind-merge';

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
    isDisabled?: boolean;
};

const DELIMITER_REGEXP = /[,.]/;

const getDecimalsLength = (value: number) => (value.toString().split(DELIMITER_REGEXP)[1] ?? '').length;

const getStep = (min: number, max: number): number => {
    const decimals = Math.max(getDecimalsLength(min), getDecimalsLength(max));

    return decimals === 0 ? 1 : Math.pow(10, -decimals);
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
    isDisabled,
}) => {
    const { t } = useTranslation();
    const step = getStep(min, max);

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
    }, [minValueThumb, delay]);

    useEffect(() => {
        const timer = setTimeout(() => {
            if (maxValueThumb !== maxValue) {
                setMaxValueCallback(maxValueThumb);
            }
        }, delay);

        return () => clearTimeout(timer);
    }, [maxValueThumb, delay]);

    useEffect(() => {
        if (minValue < min) {
            setMinValueThumb(min);
            setMinValueInput(min);
        } else if (minValue > maxValue) {
            setMinValueThumb(maxValue);
            setMinValueInput(maxValue);
        } else {
            setMinValueThumb(minValue);
            setMinValueInput(minValue);
        }
    }, [maxValue, minValue, min]);

    useEffect(() => {
        if (maxValue > max) {
            setMaxValueThumb(max);
            setMaxValueInput(max);
        } else if (maxValue < minValue) {
            setMaxValueThumb(minValue);
            setMaxValueInput(minValue);
        } else {
            setMaxValueThumb(maxValue);
            setMaxValueInput(maxValue);
        }
    }, [maxValue, minValue, max]);

    const getPercent = useCallback((value: number) => Math.round(((value - min) / (max - min)) * 100), [min, max]);

    const onBlurMinHandler: FocusEventHandler<HTMLInputElement> = (event) => {
        const value = parseFloat(event.currentTarget.value);
        if (value < min || isNaN(value)) {
            setMinValueThumb(min);
            setMinValueInput(min);
        } else {
            setMinValueCallback(value);
        }
    };

    const onBlurMaxHandler: FocusEventHandler<HTMLInputElement> = (event) => {
        const value = parseFloat(event.currentTarget.value);
        if (value > max || isNaN(value)) {
            setMaxValueThumb(max);
            setMaxValueInput(max);
        } else {
            setMaxValueCallback(value);
        }
    };

    const onChangeMaxInputHandler: ChangeEventHandler<HTMLInputElement> = (event) =>
        setMaxValueInput(parseFloat(event.currentTarget.value));

    const onChangeMinInputHandler: ChangeEventHandler<HTMLInputElement> = (event) =>
        setMinValueInput(parseFloat(event.currentTarget.value));

    const onKeyPressHandler: KeyboardEventHandler<HTMLInputElement> = (event) =>
        event.key === 'Enter' && event.currentTarget.blur();

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
        <div
            className="relative mb-10 -mt-5 flex h-12 w-full items-center justify-center p-2"
            data-testid={TEST_IDENTIFIER}
        >
            <RangeSliderThumb
                isActive={minValueThumb !== min}
                type="range"
                min={min}
                max={max}
                step={step}
                value={minValueThumb}
                onChange={onChangeMinHandler}
                data-testid={TEST_IDENTIFIER + '-left-thumb'}
                disabled={isDisabled}
                aria-label={t('from')}
            />
            <RangeSliderThumb
                isActive={maxValueThumb !== max}
                type="range"
                min={min}
                max={max}
                step={step}
                value={maxValueThumb}
                onChange={onChangeMaxHandler}
                data-testid={TEST_IDENTIFIER + '-right-thumb'}
                disabled={isDisabled}
                aria-label={t('to')}
            />
            <div className="relative w-full">
                <div className="absolute z-above h-1 w-full rounded bg-greyLighter" />
                <div
                    className={twJoin('absolute z-[2] h-1 rounded', isDisabled ? 'bg-greyLight' : 'bg-primary')}
                    ref={range}
                />
                <div
                    className="absolute -left-2 mt-5 w-20 text-xs text-black"
                    data-testid={TEST_IDENTIFIER + '-left-value'}
                >
                    <TextInput
                        id={TEST_IDENTIFIER + '-left-value'}
                        label={t('from')}
                        type="number"
                        inputSize="small"
                        value={minValueInput}
                        onChange={onChangeMinInputHandler}
                        onBlur={onBlurMinHandler}
                        onKeyPress={onKeyPressHandler}
                        disabled={isDisabled}
                    />
                </div>
                <div
                    className="absolute -right-2 mt-5 w-20 text-xs text-dark"
                    data-testid={TEST_IDENTIFIER + '-right-value'}
                >
                    <TextInput
                        id={TEST_IDENTIFIER + '-right-value'}
                        label={t('to')}
                        type="number"
                        inputSize="small"
                        value={maxValueInput}
                        onChange={onChangeMaxInputHandler}
                        onBlur={onBlurMaxHandler}
                        onKeyPress={onKeyPressHandler}
                        disabled={isDisabled}
                    />
                </div>
            </div>
        </div>
    );
};

type RangeSliderThumbProps = DetailedHTMLProps<InputHTMLAttributes<HTMLInputElement>, HTMLInputElement> & {
    isActive: boolean;
};

const RangeSliderThumb: FC<RangeSliderThumbProps> = ({ dataTestId, isActive, disabled, ...props }) => {
    const webkitTwClass =
        '[&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:relative [&::-webkit-slider-thumb]:z-[3] [&::-webkit-slider-thumb]:-my-2 [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:cursor-pointer [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:border-none [&::-webkit-slider-thumb]:[-webkit-tap-highlight-color:transparent] [&::-webkit-slider-runnable-track]:pointer-events-none';
    const mozTwClass =
        '[&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:relative [&::-moz-range-thumb]:z-[3] [&::-moz-range-thumb]:-my-2 [&::-moz-range-thumb]:h-4 [&::-moz-range-thumb]:w-4 [&::-moz-range-thumb]:cursor-pointer [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:border-none [&::-moz-range-track]:pointer-events-none';
    const msTwClass =
        '[&::-ms-track]:pointer-events-none [&::-ms-fill-lower] [&::-ms-thumb]:z-[3] [&::-ms-thumb]:-my-2 [&::-ms-thumb]:h-4 [&::-ms-thumb]:w-4 [&::-ms-thumb]:cursor-pointer [&::-ms-thumb]:rounded-full [&::-ms-thumb]:border-none [&::-ms-fill-upper]:pointer-events-none';

    return (
        <input
            className={twJoin(
                'pointer-events-none absolute top-6 z-[3] h-0 w-full appearance-none outline-none',
                webkitTwClass,
                mozTwClass,
                msTwClass,
                isActive
                    ? '[&::-webkit-slider-thumb]:bg-orange [&::-moz-range-thumb]:bg-orange [&::-ms-thumb]:bg-orange'
                    : '[&::-webkit-slider-thumb]:bg-greyLight [&::-moz-range-thumb]:bg-greyLight [&::-ms-thumb]:bg-greyLight',
                disabled &&
                    '[&::-webkit-slider-thumb]:cursor-not-allowed [&::-moz-range-thumb]:cursor-not-allowed [&::-ms-thumb]:cursor-not-allowed',
            )}
            disabled={disabled}
            type="range"
            data-testid={dataTestId}
            {...props}
        />
    );
};
