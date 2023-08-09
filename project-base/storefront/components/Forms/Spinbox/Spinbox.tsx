import { useForwardedRef } from 'hooks/typescript/useForwardedRef';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FormEventHandler, forwardRef, useCallback, useEffect, useRef, useState } from 'react';

type SpinboxProps = {
    min: number;
    max: number;
    step: number;
    defaultValue: number;
    id: string;
    onChangeValueCallback?: (currentValue: number) => void;
    size?: 'default' | 'small';
};

const TEST_IDENTIFIER = 'forms-spinbox-';

export const Spinbox = forwardRef<HTMLInputElement, SpinboxProps>(
    ({ min, max, onChangeValueCallback, step, defaultValue, size, id }, spinboxForwardedRef) => {
        const t = useTypedTranslationFunction();
        const [isHoldingDecrease, setIsHoldingDecrease] = useState(false);
        const [isHoldingIncrease, setIsHoldingIncrease] = useState(false);
        const intervalRef = useRef<NodeJS.Timer | null>(null);
        const spinboxRef = useForwardedRef(spinboxForwardedRef);

        const setNewSpinboxValue = useCallback(
            (newValue: number) => {
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
            },
            [min, max, onChangeValueCallback, spinboxRef],
        );

        const onChangeValueHandler = useCallback(
            (amountChange: number) => {
                if (spinboxRef.current !== null) {
                    setNewSpinboxValue(spinboxRef.current.valueAsNumber + amountChange);
                }
            },
            [setNewSpinboxValue, spinboxRef],
        );

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

        const clearSpinboxInterval = (interval: NodeJS.Timer | null) => {
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
                    onClick={() => onChangeValueHandler(-step)}
                    onMouseDown={() => setIsHoldingDecrease(true)}
                    onMouseUp={() => setIsHoldingDecrease(false)}
                    onMouseLeave={() => setIsHoldingDecrease(false)}
                    dataTestId={TEST_IDENTIFIER + 'decrease'}
                    title={t('Decrease')}
                >
                    -
                </SpinboxButton>

                <input
                    aria-label={`${t('Quantity')} ${id}`}
                    className="h-full min-w-0 flex-1 border-0 p-0 text-center text-lg font-bold text-dark outline-none"
                    ref={spinboxRef}
                    defaultValue={defaultValue}
                    onInput={onInputHandler}
                    type="number"
                    min={min}
                    max={max}
                    data-testid={TEST_IDENTIFIER + 'input'}
                />

                <SpinboxButton
                    onClick={() => onChangeValueHandler(step)}
                    onMouseDown={() => setIsHoldingIncrease(true)}
                    onMouseUp={() => setIsHoldingIncrease(false)}
                    onMouseLeave={() => setIsHoldingIncrease(false)}
                    dataTestId={TEST_IDENTIFIER + 'increase'}
                    title={t('Increase')}
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
            <div className="inline-flex h-12 w-32 overflow-hidden rounded-xl border-2 border-border bg-white">
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
};

const SpinboxButton: FC<SpinboxButtonProps> = ({ children, dataTestId, ...props }) => (
    <button
        className="flex min-h-0 w-6 cursor-pointer items-center justify-center border-none bg-none p-0 text-2xl text-dark outline-none"
        data-testid={dataTestId}
        {...props}
    >
        {children}
    </button>
);
