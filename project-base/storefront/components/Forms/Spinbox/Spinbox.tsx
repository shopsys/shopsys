import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { FormEventHandler, forwardRef, useEffect, useRef, useState } from 'react';
import { twMergeCustom } from 'utils/twMerge';
import { useForwardedRef } from 'utils/typescript/useForwardedRef';

type SpinboxProps = {
    min: number;
    max: number;
    step: number;
    defaultValue: number;
    id: string;
    onChangeValueCallback?: (currentValue: number) => void;
    size?: 'default' | 'small';
};

export const Spinbox = forwardRef<HTMLInputElement, SpinboxProps>(
    ({ min, max, onChangeValueCallback, step, defaultValue, size, id }, spinboxForwardedRef) => {
        const { t } = useTranslation();
        const [isHoldingDecrease, setIsHoldingDecrease] = useState(false);
        const [isHoldingIncrease, setIsHoldingIncrease] = useState(false);
        const intervalRef = useRef<NodeJS.Timeout | null>(null);
        const spinboxRef = useForwardedRef<HTMLInputElement>(spinboxForwardedRef);
        const [value, setValue] = useState<number>();

        const setNewSpinboxValue = (newValue: number) => {
            if (!spinboxRef.current) {
                return;
            }

            if (isNaN(newValue) || newValue < min) {
                spinboxRef.current.valueAsNumber = min;
            } else if (newValue > max) {
                spinboxRef.current.valueAsNumber = max;
            } else {
                spinboxRef.current.valueAsNumber = newValue;
            }

            if (onChangeValueCallback !== undefined) {
                onChangeValueCallback(spinboxRef.current.valueAsNumber);
            }
            setValue(spinboxRef.current.valueAsNumber);
        };

        useEffect(() => {
            setValue(spinboxRef.current?.valueAsNumber);
        }, [spinboxRef]);

        const onChangeValueHandler = (amountChange: number) => {
            if (spinboxRef.current !== null) {
                setNewSpinboxValue(spinboxRef.current.valueAsNumber + amountChange);
            }
        };

        useEffect(() => {
            if (isHoldingDecrease) {
                intervalRef.current = setInterval(() => {
                    onChangeValueHandler(-step);
                }, 200);
            } else {
                clearSpinboxInterval(intervalRef.current);
            }
            return () => {
                clearSpinboxInterval(intervalRef.current);
            };
        }, [isHoldingDecrease, onChangeValueHandler, step]);

        useEffect(() => {
            if (isHoldingIncrease) {
                intervalRef.current = setInterval(() => {
                    onChangeValueHandler(step);
                }, 200);
            } else {
                clearSpinboxInterval(intervalRef.current);
            }
            return () => {
                clearSpinboxInterval(intervalRef.current);
            };
        }, [isHoldingIncrease, onChangeValueHandler, step]);

        const clearSpinboxInterval = (interval: NodeJS.Timeout | null) => {
            if (interval !== null) {
                clearInterval(interval);
            }
        };

        const onInputHandler: FormEventHandler<HTMLInputElement> = (event) => {
            if (spinboxRef.current !== null) {
                setNewSpinboxValue(event.currentTarget.valueAsNumber);
            }
        };

        const content = (
            <>
                <SpinboxButton
                    disabled={value === min}
                    tid={TIDs.forms_spinbox_decrease}
                    title={t('Decrease')}
                    onClick={() => onChangeValueHandler(-step)}
                    onMouseDown={() => setIsHoldingDecrease(true)}
                    onMouseLeave={() => setIsHoldingDecrease(false)}
                    onMouseUp={() => setIsHoldingDecrease(false)}
                >
                    -
                </SpinboxButton>

                <input
                    aria-label={`${t('Quantity')} ${id}`}
                    className="h-full min-w-0 flex-1 border-0 p-0 text-center text-lg font-bold text-dark outline-none"
                    defaultValue={defaultValue}
                    max={max}
                    min={min}
                    ref={spinboxRef}
                    tid={TIDs.spinbox_input}
                    type="number"
                    onInput={onInputHandler}
                />

                <SpinboxButton
                    disabled={value === max}
                    tid={TIDs.forms_spinbox_increase}
                    title={t('Increase')}
                    onClick={() => onChangeValueHandler(step)}
                    onMouseDown={() => setIsHoldingIncrease(true)}
                    onMouseLeave={() => setIsHoldingIncrease(false)}
                    onMouseUp={() => setIsHoldingIncrease(false)}
                >
                    +
                </SpinboxButton>
            </>
        );

        if (size === 'small') {
            return (
                <div className="inline-flex w-20 overflow-hidden rounded border-2 border-border bg-white [&>button]:translate-y-0 [&>button]:text-xs">
                    {content}
                </div>
            );
        }

        return (
            <div className="inline-flex h-12 w-32 overflow-hidden rounded border-2 border-border bg-white">
                {content}
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
};

const SpinboxButton: FC<SpinboxButtonProps> = ({ children, disabled, ...props }) => (
    <button
        className={twMergeCustom([
            'flex min-h-0 w-6 cursor-pointer items-center justify-center border-none bg-none p-0 text-2xl text-dark outline-none',
            disabled && 'pointer-events-none text-greyLight',
        ])}
        {...props}
    >
        {children}
    </button>
);
