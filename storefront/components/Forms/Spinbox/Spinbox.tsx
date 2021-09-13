/* eslint-disable no-use-before-define */
import { FormEventHandler, forwardRef, useEffect, useRef, useState } from 'react';
import { SpinboxButtonStyled, SpinboxInputStyled, SpinboxSmallStyled, SpinboxStyled } from './Spinbox.style';
import { useForwardedRef } from 'hooks/UseForwardedRef';

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
const Spinbox = forwardRef<HTMLInputElement, SpinboxProps>((props, spinboxForwardedRef) => {
    const [isHoldingDecrease, setIsHoldingDecrease] = useState(false);
    const [isHoldingIncrease, setIsHoldingIncrease] = useState(false);
    const intervalRef = useRef<NodeJS.Timer | null>(null);
    const spinboxRef = useForwardedRef(spinboxForwardedRef);
    useEffect(() => {
        if (isHoldingDecrease) {
            intervalRef.current = setInterval(() => {
                onChangeValueHandler(-props.step);
            }, 200);
        } else {
            clearSpinboxInterval(intervalRef.current);
        }
        return () => {
            clearSpinboxInterval(intervalRef.current);
        };
    }, [isHoldingDecrease]);
    useEffect(() => {
        if (isHoldingIncrease) {
            intervalRef.current = setInterval(() => {
                onChangeValueHandler(props.step);
            }, 200);
        } else {
            clearSpinboxInterval(intervalRef.current);
        }
        return () => {
            clearSpinboxInterval(intervalRef.current);
        };
    }, [isHoldingIncrease]);

    const clearSpinboxInterval = (interval: NodeJS.Timer | null) => {
        if (interval !== null) {
            clearInterval(interval);
        }
    };

    const setNewSpinboxValue = (newValue: number) => {
        if (Number.isNaN(newValue) || newValue < props.min) {
            spinboxRef.current.valueAsNumber = props.min;
        } else if (newValue > props.max) {
            spinboxRef.current.valueAsNumber = props.max;
        } else {
            spinboxRef.current.valueAsNumber = newValue;
        }

        if (props.onChangeValueCallback !== undefined) {
            props.onChangeValueCallback(spinboxRef.current.valueAsNumber);
        }
    };

    const onChangeValueHandler = (amountChange: number) => {
        if (spinboxRef.current !== null) {
            setNewSpinboxValue(spinboxRef.current.valueAsNumber + amountChange);
        }
    };

    const onInputHandler: FormEventHandler<HTMLInputElement> = (event) => {
        if (spinboxRef.current !== null) {
            setNewSpinboxValue(event.currentTarget.valueAsNumber);
        }
    };

    let Component = SpinboxStyled;

    if (props.size === 'small') {
        Component = SpinboxSmallStyled;
    }

    return (
        <Component>
            <SpinboxButtonStyled
                onClick={() => onChangeValueHandler(-props.step)}
                onMouseDown={() => setIsHoldingDecrease(true)}
                onMouseUp={() => setIsHoldingDecrease(false)}
                onMouseLeave={() => setIsHoldingDecrease(false)}
            >
                -
            </SpinboxButtonStyled>
            <SpinboxInputStyled
                ref={spinboxRef}
                defaultValue={props.defaultValue}
                onInput={onInputHandler}
                type="number"
                min={props.min}
                max={props.max}
            />
            <SpinboxButtonStyled
                onClick={() => onChangeValueHandler(props.step)}
                onMouseDown={() => setIsHoldingIncrease(true)}
                onMouseUp={() => setIsHoldingIncrease(false)}
                onMouseLeave={() => setIsHoldingIncrease(false)}
            >
                +
            </SpinboxButtonStyled>
        </Component>
    );
});

Spinbox.displayName = 'Spinbox';

/* @component */
export default Spinbox;
