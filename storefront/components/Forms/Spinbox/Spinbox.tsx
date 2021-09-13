/* eslint-disable no-use-before-define */
import { FC, FormEventHandler, useEffect, useRef, useState } from 'react';
import { SpinboxButtonStyled, SpinboxInputStyled, SpinboxSmallStyled, SpinboxStyled } from './Spinbox.style';

type SpinboxProps = {
    min: number;
    max: number;
    step: number;
    defaultValue: number;
    size?: 'default' | 'small';
    onChangeCallback?: (currentValue: number) => void;
};

/**
 * Global component for spinbox input.
 */
const Spinbox: FC<SpinboxProps> = (props) => {
    const [isHoldingDecrease, setIsHoldingDecrease] = useState(false);
    const [isHoldingIncrease, setIsHoldingIncrease] = useState(false);
    const intervalRef = useRef<NodeJS.Timer | null>(null);
    const spinboxRef = useRef<HTMLInputElement>(null);
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

    let Component = SpinboxStyled;

    if (props.size === 'small') {
        Component = SpinboxSmallStyled;
    }

    const onInputHandler: FormEventHandler<HTMLInputElement> = (event) => {
        if (spinboxRef.current !== null) {
            if (Number.isNaN(spinboxRef.current.valueAsNumber) || event.currentTarget.valueAsNumber < props.min) {
                spinboxRef.current.valueAsNumber = props.min;
            } else if (event.currentTarget.valueAsNumber > props.max) {
                spinboxRef.current.valueAsNumber = props.max;
            } else {
                spinboxRef.current.valueAsNumber = event.currentTarget.valueAsNumber;
            }

            if (props.onChangeCallback !== undefined) {
                props.onChangeCallback(spinboxRef.current.valueAsNumber);
            }
        }
    };

    const onChangeValueHandler = (amountChange: number) => {
        if (spinboxRef.current !== null) {
            if (
                Number.isNaN(spinboxRef.current.valueAsNumber) ||
                spinboxRef.current.valueAsNumber + amountChange < props.min
            ) {
                spinboxRef.current.valueAsNumber = props.min;
            } else if (spinboxRef.current.valueAsNumber + amountChange > props.max) {
                spinboxRef.current.valueAsNumber = props.max;
            } else {
                spinboxRef.current.valueAsNumber += amountChange;
            }

            if (props.onChangeCallback !== undefined) {
                props.onChangeCallback(spinboxRef.current.valueAsNumber);
            }
        }
    };

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
                onInput={onInputHandler}
                ref={spinboxRef}
                type="number"
                min={props.min}
                max={props.max}
                defaultValue={props.defaultValue}
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
};

/* @component */
export default Spinbox;
