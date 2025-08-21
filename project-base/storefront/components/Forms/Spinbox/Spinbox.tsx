import { MinusIcon } from 'components/Basic/Icon/MinusIcon';
import { PlusIcon } from 'components/Basic/Icon/PlusIcon';
import { VALIDATION_CONSTANTS } from 'components/Forms/validationConstants';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { FormEventHandler, KeyboardEventHandler, forwardRef, useEffect, useRef, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { twMergeCustom } from 'utils/twMerge';
import { useForwardedRef } from 'utils/typescript/useForwardedRef';
import { useDebounce } from 'utils/useDebounce';

const { maxCartItemQuantity: MAX_CART_ITEM_QUANTITY } = VALIDATION_CONSTANTS;

type SpinboxProps = {
    min: number;
    step: number;
    defaultValue: number;
    id: string;
    onChangeValueCallback?: (currentValue: number) => void;
    size?: 'small' | 'medium' | 'large' | 'xlarge';
};

const isValidNumber = (value: number): boolean => !isNaN(value);
const isWithinMaxLimit = (value: number): boolean => value <= MAX_CART_ITEM_QUANTITY;

export const Spinbox = forwardRef<HTMLInputElement, SpinboxProps>(
    ({ min, onChangeValueCallback, step, defaultValue, size = 'large' }, spinboxForwardedRef) => {
        const { t } = useTranslation();

        const [value, setValue] = useState<number>();
        const [lastValidValue, setLastValidValue] = useState<number>(defaultValue);
        const [isHoldingDecrease, setIsHoldingDecrease] = useState(false);
        const [isHoldingIncrease, setIsHoldingIncrease] = useState(false);
        const lastKeyPressedRef = useRef<string | null>(null);
        const backspaceSequenceRef = useRef<boolean>(false);

        const spinboxRef = useForwardedRef<HTMLInputElement>(spinboxForwardedRef);
        const intervalRef = useRef<NodeJS.Timeout | null>(null);
        const debouncedValue = useDebounce(value, 500);

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

            if (integerValue > MAX_CART_ITEM_QUANTITY) {
                const clampedValue = MAX_CART_ITEM_QUANTITY;
                spinboxRef.current.valueAsNumber = clampedValue;
                setValue(clampedValue);
                setLastValidValue(clampedValue);
                return;
            }

            if (integerValue < min) {
                spinboxRef.current.valueAsNumber = min;
                setValue(min);
                setLastValidValue(min);
            } else {
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

        useEffect(() => {
            const currentValue = spinboxRef.current?.valueAsNumber;
            if (currentValue !== undefined && isValidNumber(currentValue)) {
                setValue(currentValue);
                setLastValidValue(currentValue);
            }
        }, [spinboxRef]);

        useEffect(() => {
            if (debouncedValue !== undefined && !isNaN(debouncedValue)) {
                onChangeValueCallback?.(debouncedValue);
            }
        }, [debouncedValue]);

        useEffect(() => {
            if (isHoldingIncrease) {
                intervalRef.current = setInterval(() => {
                    handleValueChange(step);
                }, 200);
            } else if (isHoldingDecrease) {
                intervalRef.current = setInterval(() => {
                    handleValueChange(-step);
                }, 200);
            } else {
                clearSpinboxInterval(intervalRef.current);
            }
            return () => {
                clearSpinboxInterval(intervalRef.current);
            };
        }, [isHoldingIncrease, isHoldingDecrease, step]);

        return (
            <div
                className={twJoin(
                    'bg-input-bg-default outline-input-border-default rounded-counter inline-flex h-fit w-auto shrink-0 items-center justify-center self-start overflow-hidden outline-2 outline-offset-[-2px]',
                    (size === 'small' || size === 'medium') && 'py-1',
                    size === 'large' && 'py-1 sm:py-1.5',
                    size === 'xlarge' && 'py-1.5 sm:py-3.5',
                )}
            >
                <SpinboxButton
                    ariaLabel={t('Decrease quantity')}
                    disabled={value === min}
                    size={size}
                    tid={TIDs.forms_spinbox_decrease}
                    title={t('Decrease')}
                    onClick={() => handleValueChange(-step)}
                    onMouseDown={() => setIsHoldingDecrease(true)}
                    onMouseLeave={() => setIsHoldingDecrease(false)}
                    onMouseUp={() => setIsHoldingDecrease(false)}
                >
                    <MinusIcon className="size-4" />
                </SpinboxButton>

                <input
                    aria-describedby="quantity-input-description"
                    aria-label={t('Quantity')}
                    data-tid={TIDs.spinbox_input}
                    defaultValue={defaultValue}
                    max={MAX_CART_ITEM_QUANTITY}
                    min={min}
                    ref={spinboxRef}
                    step={step}
                    tid={TIDs.spinbox_input}
                    type="number"
                    className={twJoin(
                        'font-secondary text-input-text-default text-center text-lg font-bold outline-hidden',
                        size === 'xlarge' ? 'w-10' : 'w-8',
                    )}
                    onBlur={handleBlur}
                    onInput={handleInput}
                    onKeyDown={handleKeyDown}
                />

                <span className="sr-only" id="quantity-input-description">
                    {t('Type in a number or use arrow up or arrow down to change the quantity')}
                </span>

                <span aria-live="polite" className="sr-only">
                    {value}
                </span>

                <SpinboxButton
                    ariaLabel={t('Increase quantity')}
                    disabled={value === MAX_CART_ITEM_QUANTITY}
                    size={size}
                    tid={TIDs.forms_spinbox_increase}
                    title={t('Increase')}
                    onClick={() => handleValueChange(step)}
                    onMouseDown={() => setIsHoldingIncrease(true)}
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
    title: string;
    disabled: boolean;
    size?: 'small' | 'medium' | 'large' | 'xlarge';
    ariaLabel: string;
};

const SpinboxButton: FC<SpinboxButtonProps> = ({ children, disabled, size, tid, title, ariaLabel, ...props }) => (
    <button
        aria-label={ariaLabel}
        data-tid={tid}
        tabIndex={disabled ? -1 : 0}
        title={title}
        className={twMergeCustom([
            'text-icon-less hover:text-icon-default flex cursor-pointer justify-center rounded-sm border-none outline-none',
            size === 'xlarge' ? 'w-10' : 'w-7',

            disabled && 'text-input-border-disabled pointer-events-none',
        ])}
        {...props}
    >
        {children}
    </button>
);
