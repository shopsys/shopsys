import { LabelWrapper } from '../Lib/LabelWrapper';
import { forwardRef, InputHTMLAttributes, MouseEventHandler, ReactNode, useCallback } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'id',
    'disabled' | 'name' | 'onBlur' | 'checked' | 'onChange'
>;

export type RadiobuttonProps = NativeProps & {
    value: any;
    checked: InputHTMLAttributes<HTMLInputElement>['checked'];
    dataTestId?: string;
    label: ReactNode;
    onChangeCallback?: (newValue: string | null) => void;
};

export const Radiobutton = forwardRef<HTMLInputElement, RadiobuttonProps>(
    (
        { label, onChangeCallback, onChange, id, name, checked, value, disabled, dataTestId, onBlur },
        radiobuttonForwardedRef,
    ) => {
        const onClickHandler: MouseEventHandler<HTMLInputElement> = useCallback(
            (event) => {
                if (!onChangeCallback) {
                    return;
                }

                if (checked) {
                    onChangeCallback(null);
                } else {
                    onChangeCallback(event.currentTarget.value);
                }
            },
            [checked, onChangeCallback],
        );

        return (
            <LabelWrapper htmlFor={id} label={label} inputType="radio" checked={checked} disabled={disabled}>
                <input
                    className="peer sr-only"
                    value={value}
                    name={name}
                    disabled={disabled}
                    checked={checked}
                    id={id}
                    type="radio"
                    onClick={onClickHandler}
                    onBlur={onBlur}
                    onChange={onChange}
                    ref={radiobuttonForwardedRef}
                    readOnly={!onChange}
                    data-testid={dataTestId}
                />
            </LabelWrapper>
        );
    },
);

Radiobutton.displayName = 'Radiobutton';
