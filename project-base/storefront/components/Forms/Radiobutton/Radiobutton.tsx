import { LabelWrapper } from 'components/Forms/Lib/LabelWrapper';
import {
    forwardRef,
    InputHTMLAttributes,
    KeyboardEvent,
    KeyboardEventHandler,
    MouseEvent,
    MouseEventHandler,
    ReactNode,
} from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'id',
    'disabled' | 'name' | 'onBlur' | 'checked' | 'onChange' | 'onKeyDown' | 'aria-label'
>;

export type RadiobuttonProps<T = string> = NativeProps & {
    value: T;
    checked: InputHTMLAttributes<HTMLInputElement>['checked'];
    label: ReactNode;
    onClick?: (newValue: string | null, event: MouseEvent<HTMLInputElement> | KeyboardEvent<HTMLInputElement>) => void;
    labelWrapperClassName?: string;
    shouldUseFocusOnlyArrowKeys?: boolean;
};

export const Radiobutton = forwardRef<HTMLInputElement, RadiobuttonProps>(
    (
        {
            label,
            onChange,
            id,
            name,
            checked,
            value,
            disabled,
            onBlur,
            onClick,
            onKeyDown,
            labelWrapperClassName,
            shouldUseFocusOnlyArrowKeys,
            'aria-label': ariaLabel,
        },
        radiobuttonForwardedRef,
    ) => {
        const onClickHandler: MouseEventHandler<HTMLInputElement> = (event) => {
            if (!onClick) {
                return;
            }

            if (checked) {
                onClick(null, event);
            } else {
                onClick(event.currentTarget.value, event);
            }
        };

        const onKeyDownHandler: KeyboardEventHandler<HTMLInputElement> = (event) => {
            if (shouldUseFocusOnlyArrowKeys && handleFocusOnlyArrowKeys(event)) {
                return;
            }

            if (event.key === 'Enter' && onClick) {
                event.preventDefault();
                onClick(checked ? null : event.currentTarget.value, event);

                return;
            }

            onKeyDown?.(event);
        };

        return (
            <LabelWrapper
                checked={checked}
                className={labelWrapperClassName}
                disabled={disabled}
                htmlFor={id}
                inputType="radio"
                label={label}
            >
                <input
                    aria-label={ariaLabel}
                    checked={checked}
                    className="peer sr-only"
                    disabled={disabled}
                    id={id}
                    name={name}
                    readOnly={!onChange}
                    ref={radiobuttonForwardedRef}
                    tabIndex={0}
                    type="radio"
                    value={value}
                    onBlur={onBlur}
                    onChange={onChange}
                    onClick={onClickHandler}
                    onKeyDown={onKeyDownHandler}
                />
            </LabelWrapper>
        );
    },
);

Radiobutton.displayName = 'Radiobutton';

const handleFocusOnlyArrowKeys = (event: KeyboardEvent<HTMLInputElement>): boolean => {
    const isNextKey = event.key === 'ArrowDown' || event.key === 'ArrowRight';
    const isPreviousKey = event.key === 'ArrowUp' || event.key === 'ArrowLeft';

    if (!isNextKey && !isPreviousKey) {
        return false;
    }

    event.preventDefault();

    const radioButtons = Array.from(document.getElementsByName(event.currentTarget.name)).filter(
        (element): element is HTMLInputElement =>
            element instanceof HTMLInputElement && element.type === 'radio' && !element.disabled,
    );
    const currentIndex = radioButtons.indexOf(event.currentTarget);

    if (currentIndex === -1) {
        return true;
    }

    const nextIndex = isNextKey
        ? (currentIndex + 1) % radioButtons.length
        : (currentIndex - 1 + radioButtons.length) % radioButtons.length;

    radioButtons[nextIndex]?.focus();

    return true;
};
