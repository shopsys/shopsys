import { MinusIcon } from 'components/Basic/Icon/MinusIcon';
import { PlusIcon } from 'components/Basic/Icon/PlusIcon';
import { VALIDATION_CONSTANTS } from 'components/Forms/validationConstants';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { FormEventHandler, forwardRef, useEffect, useRef, useState } from 'react';
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

const validateQuantityLimit = (newValue: number) => {
    if (isNaN(newValue)) {
        return true;
    }

    if (newValue > MAX_CART_ITEM_QUANTITY) {
        return false;
    }
    return true;
};

export const Spinbox = forwardRef<HTMLInputElement, SpinboxProps>(
    ({ min, onChangeValueCallback, step, defaultValue, size = 'large', id }, spinboxForwardedRef) => {
        const { t } = useTranslation();
        const [isHoldingDecrease, setIsHoldingDecrease] = useState(false);
        const [isHoldingIncrease, setIsHoldingIncrease] = useState(false);
        const intervalRef = useRef<NodeJS.Timeout | null>(null);
        const spinboxRef = useForwardedRef<HTMLInputElement>(spinboxForwardedRef);
        const [value, setValue] = useState<number>();
        const debouncedValue = useDebounce(value, 500);

        const validateNaNSpinboxValue = (newValue: number) => {
            if (!spinboxRef.current) {
                return;
            }

            if (isNaN(newValue)) {
                spinboxRef.current.valueAsNumber = debouncedValue ?? min;
            }

            if (onChangeValueCallback !== undefined && isNaN(newValue)) {
                onChangeValueCallback(spinboxRef.current.valueAsNumber);
            }
        };

        const setNewSpinboxValue = (newValue: number) => {
            if (!spinboxRef.current) {
                return;
            }

            if (newValue > MAX_CART_ITEM_QUANTITY) {
                spinboxRef.current.valueAsNumber = MAX_CART_ITEM_QUANTITY;
                setValue(MAX_CART_ITEM_QUANTITY);
                return;
            }

            if (newValue < min) {
                spinboxRef.current.valueAsNumber = min;
            } else {
                spinboxRef.current.valueAsNumber = newValue;
            }

            setValue(isNaN(newValue) ? debouncedValue : spinboxRef.current.valueAsNumber);
        };

        const onChangeValueHandler = (amountChange: number) => {
            if (spinboxRef.current !== null) {
                const currentValue = spinboxRef.current.valueAsNumber;
                if (isNaN(currentValue)) {
                    return;
                }

                const newValue = currentValue + amountChange;
                if (newValue <= MAX_CART_ITEM_QUANTITY) {
                    setNewSpinboxValue(newValue);
                }
            }
        };

        const clearSpinboxInterval = (interval: NodeJS.Timeout | null) => {
            if (interval !== null) {
                clearInterval(interval);
            }
        };

        const onBlurHandler: FormEventHandler<HTMLInputElement> = (event) => {
            if (spinboxRef.current !== null) {
                const inputValue = event.currentTarget.valueAsNumber;

                if (!isNaN(inputValue)) {
                    if (!validateQuantityLimit(inputValue)) {
                        event.currentTarget.valueAsNumber = MAX_CART_ITEM_QUANTITY;
                        setValue(MAX_CART_ITEM_QUANTITY);
                    }
                } else {
                    validateNaNSpinboxValue(inputValue);
                }

                window.getSelection()?.removeAllRanges();
            }
        };

        const onInputHandler: FormEventHandler<HTMLInputElement> = (event) => {
            if (spinboxRef.current !== null) {
                const inputValue = event.currentTarget.valueAsNumber;

                if (isNaN(inputValue) || inputValue <= MAX_CART_ITEM_QUANTITY) {
                    setNewSpinboxValue(inputValue);
                } else {
                    event.currentTarget.valueAsNumber = MAX_CART_ITEM_QUANTITY;
                    setNewSpinboxValue(MAX_CART_ITEM_QUANTITY);
                }
            }
        };

        useEffect(() => {
            setValue(spinboxRef.current?.valueAsNumber);
        }, [spinboxRef]);

        useEffect(() => {
            if (onChangeValueCallback !== undefined && debouncedValue !== undefined && !isNaN(debouncedValue)) {
                onChangeValueCallback(debouncedValue);
            }
        }, [debouncedValue]);

        useEffect(() => {
            if (isHoldingIncrease) {
                intervalRef.current = setInterval(() => {
                    onChangeValueHandler(step);
                }, 200);
            } else if (isHoldingDecrease) {
                intervalRef.current = setInterval(() => {
                    onChangeValueHandler(-step);
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
                    disabled={value === min}
                    size={size}
                    tid={TIDs.forms_spinbox_decrease}
                    title={t('Decrease')}
                    onClick={() => onChangeValueHandler(-step)}
                    onMouseDown={() => setIsHoldingDecrease(true)}
                    onMouseLeave={() => setIsHoldingDecrease(false)}
                    onMouseUp={() => setIsHoldingDecrease(false)}
                >
                    <MinusIcon className="size-4" />
                </SpinboxButton>

                <input
                    aria-label={`${t('Quantity')} ${id}`}
                    defaultValue={defaultValue}
                    max={MAX_CART_ITEM_QUANTITY}
                    min={min}
                    ref={spinboxRef}
                    tid={TIDs.spinbox_input}
                    type="number"
                    className={twJoin(
                        'font-secondary text-input-text-default text-center text-lg font-bold outline-hidden',
                        size === 'xlarge' ? 'w-10' : 'w-8',
                    )}
                    onBlur={onBlurHandler}
                    onInput={onInputHandler}
                />

                <SpinboxButton
                    disabled={value === MAX_CART_ITEM_QUANTITY}
                    size={size}
                    tid={TIDs.forms_spinbox_increase}
                    title={t('Increase')}
                    onClick={() => onChangeValueHandler(step)}
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
};

const SpinboxButton: FC<SpinboxButtonProps> = ({ children, disabled, size, ...props }) => (
    <button
        tabIndex={disabled ? -1 : 0}
        className={twMergeCustom([
            'text-icon-less hover:text-icon-default flex cursor-pointer justify-center rounded-md border-none',
            'focus-visible:outline-input-border-hovered focus-visible:outline-2',
            size === 'xlarge' ? 'w-10' : 'w-7',

            disabled && 'text-input-border-disabled pointer-events-none',
        ])}
        {...props}
    >
        {children}
    </button>
);
