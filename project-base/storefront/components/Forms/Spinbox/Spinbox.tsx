import { MinusIcon } from 'components/Basic/Icon/MinusIcon';
import { PlusIcon } from 'components/Basic/Icon/PlusIcon';
import { Tooltip } from 'components/Basic/Tooltip/Tooltip';
import { VALIDATION_CONSTANTS } from 'components/Forms/validationConstants';
import { TIDs } from 'cypress/tids';
import {
    FormEventHandler,
    forwardRef,
    KeyboardEventHandler,
    ReactNode,
    useEffect,
    useEffectEvent,
    useRef,
    useState,
} from 'react';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { showInfoMessage } from 'utils/toasts/showInfoMessage';
import { twMergeCustom } from 'utils/twMerge';
import { useForwardedRef } from 'utils/typescript/useForwardedRef';
import { useDebounce } from 'utils/useDebounce';

const { maxCartItemQuantity: MAX_CART_ITEM_QUANTITY } = VALIDATION_CONSTANTS;

type SpinboxProps = {
    min: number;
    step: number;
    defaultValue: number;
    id: string;
    max?: number | null;
    onChangeValueCallback?: (currentValue: number) => void;
    size?: 'small' | 'medium' | 'large' | 'xlarge';
    ariaDescription?: string;
    ariaLabel?: string;
    decreaseAriaLabel?: string;
    increaseAriaLabel?: string;
    minValueDecreaseIcon?: ReactNode;
    minValueDecreaseTitle?: string;
    minValueDecreaseAriaLabel?: string;
    onMinValueDecrease?: () => void;
    inputAriaLabel?: string;
    liveAnnouncement?: string;
    className?: string;
    hasPendingLook?: boolean;
};

const isValidNumber = (value: number): boolean => !Number.isNaN(value);
const isWithinMaxLimit = (value: number): boolean => value <= MAX_CART_ITEM_QUANTITY;

export const Spinbox = forwardRef<HTMLInputElement, SpinboxProps>(
    (
        {
            min,
            max,
            onChangeValueCallback,
            step,
            defaultValue,
            size = 'large',
            ariaDescription,
            ariaLabel,
            minValueDecreaseIcon,
            minValueDecreaseTitle,
            minValueDecreaseAriaLabel,
            onMinValueDecrease,
            inputAriaLabel,
            decreaseAriaLabel,
            increaseAriaLabel,
            liveAnnouncement,
            className,
            hasPendingLook = false,
            id,
        },
        spinboxForwardedRef,
    ) => {
        const { t } = useTranslation();

        const resolvedMax = Math.min(max ?? MAX_CART_ITEM_QUANTITY, MAX_CART_ITEM_QUANTITY);

        const [value, setValue] = useState<number | undefined>(defaultValue);
        const [lastValidValue, setLastValidValue] = useState<number>(defaultValue);
        const [isHoldingDecrease, setIsHoldingDecrease] = useState(false);
        const [isHoldingIncrease, setIsHoldingIncrease] = useState(false);
        const [userValueChangeSequence, setUserValueChangeSequence] = useState(0);
        const lastKeyPressedRef = useRef<string | null>(null);
        const backspaceSequenceRef = useRef<boolean>(false);

        const spinboxRef = useForwardedRef<HTMLInputElement>(spinboxForwardedRef);
        const intervalRef = useRef<NodeJS.Timeout | null>(null);
        const debouncedValue = useDebounce(value, 500);
        const debouncedUserValueChangeSequence = useDebounce(userValueChangeSequence, 500);
        const lastReportedValueRef = useRef<number | undefined>(defaultValue);
        const hasPendingUserValueChangeRef = useRef(false);

        useEffect(() => {
            setValue(defaultValue);
            setLastValidValue(defaultValue);
            lastReportedValueRef.current = defaultValue;
            hasPendingUserValueChangeRef.current = false;

            if (spinboxRef.current) {
                spinboxRef.current.valueAsNumber = defaultValue;
            }
        }, [defaultValue]);

        const restoreValueOnEmpty = (inputValue: number) => {
            if (!spinboxRef.current) {
                return;
            }

            if (!isValidNumber(inputValue)) {
                spinboxRef.current.valueAsNumber = lastValidValue;
                setValue(lastValidValue);

                if (onChangeValueCallback) {
                    onChangeValueCallback(lastValidValue);
                }
            }
        };

        const updateInputValue = (newValue: number, skipLastValidUpdate: boolean = false) => {
            if (!spinboxRef.current) {
                return;
            }

            if (!isValidNumber(newValue)) {
                setValue(undefined);
                return;
            }

            const integerValue = Math.round(newValue);

            if (integerValue < min) {
                hasPendingUserValueChangeRef.current = true;
                setUserValueChangeSequence((currentSequence) => currentSequence + 1);
                spinboxRef.current.valueAsNumber = min;
                setValue(min);
                setLastValidValue(min);
            } else if (integerValue > resolvedMax) {
                hasPendingUserValueChangeRef.current = true;
                setUserValueChangeSequence((currentSequence) => currentSequence + 1);
                spinboxRef.current.valueAsNumber = resolvedMax;
                setValue(resolvedMax);
                setLastValidValue(resolvedMax);

                showInfoMessage(
                    t('Maximum available quantity is {{ quantity }}. The quantity was adjusted.', {
                        quantity: resolvedMax,
                    }),
                );
            } else {
                hasPendingUserValueChangeRef.current = true;
                setUserValueChangeSequence((currentSequence) => currentSequence + 1);
                spinboxRef.current.valueAsNumber = integerValue;
                setValue(integerValue);

                if (!skipLastValidUpdate) {
                    setLastValidValue(integerValue);
                }
            }
        };

        const handleValueChange = (amountChange: number) => {
            if (!spinboxRef.current) {
                return;
            }

            const currentValue = spinboxRef.current.valueAsNumber;
            if (!isValidNumber(currentValue)) {
                return;
            }

            const newValue = currentValue + amountChange;
            if (isWithinMaxLimit(newValue)) {
                updateInputValue(newValue);
            }
        };

        const handleBlur: FormEventHandler<HTMLInputElement> = (event) => {
            if (!spinboxRef.current) {
                return;
            }

            backspaceSequenceRef.current = false;

            const inputValue = event.currentTarget.valueAsNumber;

            if (isValidNumber(inputValue)) {
                if (!isWithinMaxLimit(inputValue)) {
                    updateInputValue(MAX_CART_ITEM_QUANTITY);
                }
            } else {
                restoreValueOnEmpty(inputValue);
            }

            window.getSelection()?.removeAllRanges();
        };

        const handleInput: FormEventHandler<HTMLInputElement> = (event) => {
            if (!spinboxRef.current) {
                return;
            }

            const inputValue = event.currentTarget.valueAsNumber;
            const isDeletingContent =
                lastKeyPressedRef.current === 'Backspace' || lastKeyPressedRef.current === 'Delete';

            if (isDeletingContent) {
                if (!backspaceSequenceRef.current) {
                    backspaceSequenceRef.current = true;

                    // We use >= 10 as the threshold because single-digit values (1-9) are more likely to be
                    // intermediate states during deletion, while double-digit values represent meaningful
                    // user inputs that should be preserved for restoration on blur
                    const shouldUpdateOnFirstDeletion = isValidNumber(inputValue) && inputValue >= 10;
                    updateInputValue(inputValue, !shouldUpdateOnFirstDeletion);
                } else {
                    updateInputValue(inputValue, true);
                }
            } else {
                backspaceSequenceRef.current = false;
                updateInputValue(inputValue, false);
            }

            lastKeyPressedRef.current = null;
        };

        const handleKeyDown: KeyboardEventHandler<HTMLInputElement> = (event) => {
            lastKeyPressedRef.current = event.key;

            if (event.key === '.' || event.key === ',') {
                event.preventDefault();
            }
        };

        const clearSpinboxInterval = (interval: NodeJS.Timeout | null) => {
            if (interval !== null) {
                clearInterval(interval);
            }
        };

        const onReportValue = useEffectEvent((reportedValue: number) => {
            onChangeValueCallback?.(reportedValue);
        });

        useEffect(() => {
            if (
                debouncedValue !== undefined &&
                !Number.isNaN(debouncedValue) &&
                (hasPendingUserValueChangeRef.current || debouncedValue !== lastReportedValueRef.current)
            ) {
                lastReportedValueRef.current = debouncedValue;
                hasPendingUserValueChangeRef.current = false;
                onReportValue(debouncedValue);
            }
        }, [debouncedValue, debouncedUserValueChangeSequence]);

        const onValueChange = useEffectEvent((amountChange: number) => {
            handleValueChange(amountChange);
        });

        const isDecreaseOnMinValue = value === min;
        const isIncreaseOnMaxValue = value === resolvedMax;
        const isDecreaseDisabled = isDecreaseOnMinValue && onMinValueDecrease === undefined;
        const isIncreaseDisabled = isIncreaseOnMaxValue;
        const descriptionId = `${id}-quantity-input-description`;
        const onDecreaseClick = () => {
            if (isDecreaseOnMinValue) {
                lastReportedValueRef.current = value;
                hasPendingUserValueChangeRef.current = false;
                onMinValueDecrease?.();

                return;
            }

            handleValueChange(-step);
        };

        useEffect(() => {
            if (isHoldingIncrease) {
                intervalRef.current = setInterval(() => {
                    onValueChange(step);
                }, 200);
            } else if (isHoldingDecrease) {
                intervalRef.current = setInterval(() => {
                    onValueChange(-step);
                }, 200);
            } else {
                clearSpinboxInterval(intervalRef.current);
            }
            return () => {
                clearSpinboxInterval(intervalRef.current);
            };
            // `clearSpinboxInterval` is intentionally excluded — it creates a new reference each render, which would make the interval unstable across rerenders.
        }, [isHoldingIncrease, isHoldingDecrease, step]);

        return (
            <div
                aria-busy={hasPendingLook}
                className={twMergeCustom(
                    'inline-flex h-fit w-full shrink-0 items-center justify-between overflow-hidden rounded-counter bg-background-default outline outline-gray-400 -outline-offset-1',
                    className,
                )}
            >
                <SpinboxButton
                    ariaLabel={
                        isDecreaseOnMinValue && minValueDecreaseAriaLabel
                            ? minValueDecreaseAriaLabel
                            : (decreaseAriaLabel ?? t('Decrease quantity', { ns: 'accessibility' }))
                    }
                    className="bg-gray-300 hover:bg-gray-500"
                    disabled={isDecreaseDisabled}
                    size={size}
                    tid={TIDs.forms_spinbox_decrease}
                    tooltipLabel={minValueDecreaseIcon ? minValueDecreaseTitle : undefined}
                    isTooltipDisabled={!isDecreaseOnMinValue}
                    onClick={onDecreaseClick}
                    onMouseDown={() => {
                        if (!isDecreaseOnMinValue) {
                            setIsHoldingDecrease(true);
                        }
                    }}
                    onMouseLeave={() => setIsHoldingDecrease(false)}
                    onMouseUp={() => setIsHoldingDecrease(false)}
                >
                    {isDecreaseOnMinValue && minValueDecreaseIcon ? (
                        minValueDecreaseIcon
                    ) : (
                        <MinusIcon className="size-4" />
                    )}
                </SpinboxButton>

                <input
                    aria-describedby={descriptionId}
                    aria-label={inputAriaLabel ?? ariaLabel ?? t('Quantity', { ns: 'accessibility' })}
                    data-tid={TIDs.spinbox_input}
                    defaultValue={defaultValue}
                    id={id}
                    max={resolvedMax}
                    min={min}
                    ref={spinboxRef}
                    step={step}
                    tid={TIDs.spinbox_input}
                    type="number"
                    className={twJoin(
                        'w-13 text-center font-secondary font-semibold text-input-text-default text-lg outline-hidden',
                    )}
                    onBlur={handleBlur}
                    onInput={handleInput}
                    onKeyDown={handleKeyDown}
                />

                <span className="sr-only" id={descriptionId}>
                    {ariaDescription ?? t('Type in a number or use arrow up or arrow down to change the quantity')}
                </span>

                <span className="sr-only" role="status">
                    {liveAnnouncement}
                </span>

                <SpinboxButton
                    ariaLabel={increaseAriaLabel ?? t('Increase quantity', { ns: 'accessibility' })}
                    className={twJoin(
                        'bg-button-primary-bg-default text-button-primary-text-default hover:bg-button-primary-bg-hovered hover:text-button-primary-text-hovered',
                        isIncreaseDisabled && 'bg-button-primary-bg-disabled text-button-primary-text-disabled',
                    )}
                    disabled={isIncreaseDisabled}
                    size={size}
                    tid={TIDs.forms_spinbox_increase}
                    hasPendingLook={hasPendingLook}
                    onClick={() => {
                        if (!isIncreaseDisabled) {
                            handleValueChange(step);
                        }
                    }}
                    onMouseDown={() => {
                        if (!isIncreaseDisabled) {
                            setIsHoldingIncrease(true);
                        }
                    }}
                    onMouseLeave={() => setIsHoldingIncrease(false)}
                    onMouseUp={() => setIsHoldingIncrease(false)}
                >
                    <PlusIcon className="size-4" />
                </SpinboxButton>
            </div>
        );
    },
);

Spinbox.displayName = 'Spinbox';

type SpinboxButtonProps = {
    onClick: () => void;
    onMouseDown: () => void;
    onMouseUp: () => void;
    onMouseLeave: () => void;
    disabled: boolean;
    size?: 'small' | 'medium' | 'large' | 'xlarge';
    ariaLabel: string;
    className: string;
    hasPendingLook?: boolean;
    isTooltipDisabled?: boolean;
    tooltipLabel?: string;
};

const SpinboxButton: FC<SpinboxButtonProps> = ({
    children,
    disabled,
    size,
    tid,
    ariaLabel,
    className,
    hasPendingLook,
    isTooltipDisabled,
    tooltipLabel,
    ...props
}) => {
    const button = (
        <button
            aria-disabled={disabled}
            aria-label={ariaLabel}
            data-tid={tid}
            tabIndex={disabled ? -1 : 0}
            className={twMergeCustom([
                'relative flex cursor-pointer items-center justify-center rounded-input border-none',
                size === 'small' && 'size-9',
                size === 'medium' && 'size-9',
                size === 'large' && 'size-9 sm:size-10',
                size === 'xlarge' && 'size-10 sm:size-14',
                className,
                hasPendingLook && 'opacity-70',
                disabled && 'pointer-events-none',
            ])}
            {...props}
        >
            {children}
        </button>
    );
    const buttonWrapper = <span className="inline-flex">{button}</span>;

    return tooltipLabel ? (
        <Tooltip disabled={isTooltipDisabled} label={tooltipLabel}>
            {buttonWrapper}
        </Tooltip>
    ) : (
        buttonWrapper
    );
};
