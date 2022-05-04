/* eslint-disable no-use-before-define */
import { FormEventHandler, forwardRef, useCallback, useEffect, useRef, useState } from 'react';
import { SpinboxButtonStyled, SpinboxInputStyled, SpinboxSmallStyled, SpinboxStyled } from './Spinbox.style';
import { useForwardedRef } from 'hooks/typescript/UseForwardedRef';

type SpinboxProps = {
    min: number;
    max: number;
    step: number;
    defaultValue: number;
    onChangeValueCallback?: (currentValue: number) => void;
    size?: 'default' | 'small';
};

/**
 * Global component for spinbox input.
 */
const Spinbox = forwardRef<HTMLInputElement, SpinboxProps>(
    ({ min, max, onChangeValueCallback, step, ...restProps }, spinboxForwardedRef) => {
        const testIdentifier = 'forms-spinbox-';

        const [isHoldingDecrease, setIsHoldingDecrease] = useState(false);
        const [isHoldingIncrease, setIsHoldingIncrease] = useState(false);
        const intervalRef = useRef<NodeJS.Timer | null>(null);
        const spinboxRef = useForwardedRef(spinboxForwardedRef);

        const setNewSpinboxValue = useCallback(
            (newValue: number) => {
                if (Number.isNaN(newValue) || newValue < min) {
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

        let Component = SpinboxStyled;

        if (restProps.size === 'small') {
            Component = SpinboxSmallStyled;
        }

        return (
            <Component>
                <SpinboxButtonStyled
                    onClick={() => onChangeValueHandler(-step)}
                    onMouseDown={() => setIsHoldingDecrease(true)}
                    onMouseUp={() => setIsHoldingDecrease(false)}
                    onMouseLeave={() => setIsHoldingDecrease(false)}
                    data-testid={testIdentifier + 'decrease'}
                >
                    -
                </SpinboxButtonStyled>
                <SpinboxInputStyled
                    ref={spinboxRef}
                    defaultValue={restProps.defaultValue}
                    onInput={onInputHandler}
                    type="number"
                    min={min}
                    max={max}
                    data-testid={testIdentifier + 'input'}
                />
                <SpinboxButtonStyled
                    onClick={() => onChangeValueHandler(step)}
                    onMouseDown={() => setIsHoldingIncrease(true)}
                    onMouseUp={() => setIsHoldingIncrease(false)}
                    onMouseLeave={() => setIsHoldingIncrease(false)}
                    data-testid={testIdentifier + 'increase'}
                >
                    +
                </SpinboxButtonStyled>
            </Component>
        );
    },
);

Spinbox.displayName = 'Spinbox';

/* @component */
export default Spinbox;
