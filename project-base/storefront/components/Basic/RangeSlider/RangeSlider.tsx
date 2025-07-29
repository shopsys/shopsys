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
    useMemo,
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
    title: string;
};

const DELIMITER_REGEXP = /[,.]/;

const getDecimalsLength = (value: number) => (value.toString().split(DELIMITER_REGEXP)[1] ?? '').length;

const getStep = (min: number, max: number): number => {
    const decimals = Math.max(getDecimalsLength(min), getDecimalsLength(max));

    return decimals === 0 ? 1 : Math.pow(10, -decimals);
};

export const RangeSlider: FC<RangeSliderProps> = ({
    min,
    max,
    minValue,
    maxValue,
    setMinValueCallback,
    setMaxValueCallback,
    isDisabled,
    title,
}) => {
    const { t } = useTranslation();
    const step = useMemo(() => getStep(min, max), [min, max]);

    const [minValueInput, setMinValueInput] = useState(min);
    const [minValueThumb, setMinValueThumb] = useState(min);

    const [maxValueInput, setMaxValueInput] = useState(max);
    const [maxValueThumb, setMaxValueThumb] = useState(max);

    const range = useRef<HTMLDivElement>(null);

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

    useEffect(() => {
        const minPercent = getPercent(minValueThumb);
        const maxPercent = getPercent(maxValueThumb);

        if (range.current) {
            range.current.style.left = `${minPercent}%`;
            range.current.style.width = `${maxPercent - minPercent}%`;
        }
    }, [getPercent, maxValueThumb, minValueThumb]);

    const onBlurMinHandler: FocusEventHandler<HTMLInputElement> = (event) => {
        const value = parseFloat(event.currentTarget.value);
        if (value < min || isNaN(value)) {
            setMinValueThumb(min);
            setMinValueInput(min);
            setMinValueCallback(min);
        } else if (value > maxValueThumb) {
            setMinValueThumb(maxValueThumb);
            setMinValueInput(maxValueThumb);
            setMinValueCallback(maxValueThumb);
        } else {
            setMinValueCallback(value);
        }

        // clear any text selection to ensure proper navigation
        window.getSelection()?.removeAllRanges();
    };

    const onBlurMaxHandler: FocusEventHandler<HTMLInputElement> = (event) => {
        const value = parseFloat(event.currentTarget.value);

        if (value > max || isNaN(value)) {
            setMaxValueThumb(max);
            setMaxValueInput(max);
            setMaxValueCallback(max);
        } else if (value < minValueThumb) {
            setMaxValueThumb(minValueThumb);
            setMaxValueInput(minValueThumb);
            setMaxValueCallback(minValueThumb);
        } else {
            setMaxValueCallback(value);
        }

        // clear any text selection to ensure proper navigation
        window.getSelection()?.removeAllRanges();
    };

    const onChangeMaxInputHandler: ChangeEventHandler<HTMLInputElement> = (event) =>
        setMaxValueInput(parseFloat(event.currentTarget.value));

    const onChangeMinInputHandler: ChangeEventHandler<HTMLInputElement> = (event) =>
        setMinValueInput(parseFloat(event.currentTarget.value));

    const onEnterKeyDownHandler: KeyboardEventHandler<HTMLInputElement> = (event) =>
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

    const handleMinValueThumbCallback = () => setMinValueCallback(minValueThumb);
    const handleMaxValueThumbCallback = () => setMaxValueCallback(maxValueThumb);

    return (
        <>
            <div className="relative flex h-4 w-full items-center justify-center">
                <RangeSliderThumb
                    aria-label={t('Minimum value')}
                    className="pr-4"
                    disabled={isDisabled}
                    max={max}
                    min={min}
                    step={step}
                    type="range"
                    value={minValueThumb}
                    onChange={onChangeMinHandler}
                    onMouseUp={handleMinValueThumbCallback}
                    onTouchEnd={handleMinValueThumbCallback}
                />
                <RangeSliderThumb
                    aria-label={t('Maximum value')}
                    className="pl-4"
                    disabled={isDisabled}
                    max={max}
                    min={min}
                    step={step}
                    type="range"
                    value={maxValueThumb}
                    onChange={onChangeMaxHandler}
                    onMouseUp={handleMaxValueThumbCallback}
                    onTouchEnd={handleMaxValueThumbCallback}
                />
                <div className="relative w-full">
                    <div className="bg-border-less absolute z-[1] h-[2px] w-full rounded-sm" />
                    <div className="relative mx-auto flex w-[calc(100%-32px)]">
                        <div className="bg-input-fill absolute z-[2] h-[2px]" ref={range} />
                    </div>
                </div>
            </div>
            <div className="flex gap-x-2">
                <div className="w-1/2">
                    <TextInput
                        aria-label={t('Filter by minimum value')}
                        disabled={isDisabled}
                        id={`${title} - from`}
                        label={t('from')}
                        type="number"
                        value={minValueInput}
                        onBlur={onBlurMinHandler}
                        onChange={onChangeMinInputHandler}
                        onKeyDown={onEnterKeyDownHandler}
                    />
                </div>
                <div className="w-1/2">
                    <TextInput
                        aria-label={t('Filter by maximum value')}
                        disabled={isDisabled}
                        id={`${title} - to`}
                        label={t('to')}
                        type="number"
                        value={maxValueInput}
                        onBlur={onBlurMaxHandler}
                        onChange={onChangeMaxInputHandler}
                        onKeyDown={onEnterKeyDownHandler}
                    />
                </div>
            </div>
        </>
    );
};

type RangeSliderThumbProps = DetailedHTMLProps<InputHTMLAttributes<HTMLInputElement>, HTMLInputElement>;

const RangeSliderThumb: FC<RangeSliderThumbProps> = ({ disabled, className, ...props }) => {
    const webkitTwClass =
        '[&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:relative [&::-webkit-slider-thumb]:z-[3] [&::-webkit-slider-thumb]:-my-2 [&::-webkit-slider-thumb]:size-4 [&::-webkit-slider-thumb]:cursor-pointer [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:[-webkit-tap-highlight-color:transparent] [&::-webkit-slider-runnable-track]:pointer-events-none';
    const mozTwClass =
        '[&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:relative [&::-moz-range-thumb]:z-[3] [&::-moz-range-thumb]:-my-2 [&::-moz-range-thumb]:size-3 [&::-moz-range-thumb]:cursor-pointer [&::-moz-range-thumb]:rounded-full [&::-moz-range-track]:pointer-events-none';
    const msTwClass =
        '[&::-ms-track]:pointer-events-none [&::-ms-fill-lower] [&::-ms-thumb]:z-[3] [&::-ms-thumb]:-my-2 [&::-ms-thumb]:size-4 [&::-ms-thumb]:cursor-pointer [&::-ms-thumb]:rounded-full [&::-ms-fill-upper]:pointer-events-none';

    const webkitBgClass =
        '[&::-webkit-slider-thumb]:bg-input-bg-default hover:[&::-webkit-slider-thumb]:bg-input-fill [&::-webkit-slider-thumb]:border-2 [&::-webkit-slider-thumb]:border-input-fill [&::-webkit-slider-thumb]:border-solid';
    const mozBgClass =
        '[&::-moz-range-thumb]:bg-input-bg-default hover:[&::-moz-range-thumb]:bg-input-fill [&::-moz-range-thumb]:border-2 [&::-moz-range-thumb]:border-input-fill [&::-moz-range-thumb]:border-solid';
    const msBgClass =
        '[&::-ms-thumb]:bg-input-bg-default hover:[&::-ms-thumb]:bg-input-fill [&::-ms-thumb]:border-2 [&::-ms-thumb]:border-input-fill [&::-ms-thumb]:border-solid';

    let bgClass = twJoin(mozBgClass, msBgClass, webkitBgClass);

    if (disabled) {
        bgClass =
            '[&::-moz-range-thumb]:bg-input-border-disabled [&::-ms-thumb]:bg-input-border-disabled [&::-webkit-slider-thumb]:bg-input-border-disabled';
    }

    return (
        <input
            disabled={disabled}
            tabIndex={-1}
            type="range"
            className={twJoin(
                'pointer-events-none absolute top-[9px] z-[3] h-0 w-full appearance-none outline-hidden',
                webkitTwClass,
                mozTwClass,
                msTwClass,
                bgClass,
                disabled &&
                    '[&::-moz-range-thumb]:cursor-not-allowed [&::-ms-thumb]:cursor-not-allowed [&::-webkit-slider-thumb]:cursor-not-allowed',
                className,
            )}
            {...props}
        />
    );
};
