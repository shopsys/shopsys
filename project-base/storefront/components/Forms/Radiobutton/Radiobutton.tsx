import { LabelWrapper } from 'components/Forms/Lib/LabelWrapper';
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
    label: ReactNode;
    onChangeCallback?: (newValue: string | null) => void;
};

export const Radiobutton = forwardRef<HTMLInputElement, RadiobuttonProps>(
    ({ label, onChangeCallback, onChange, id, name, checked, value, disabled, onBlur }, radiobuttonForwardedRef) => {
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
            <LabelWrapper checked={checked} disabled={disabled} htmlFor={id} inputType="radio" label={label}>
                <input
                    checked={checked}
                    className="peer sr-only"
                    disabled={disabled}
                    id={id}
                    name={name}
                    readOnly={!onChange}
                    ref={radiobuttonForwardedRef}
                    type="radio"
                    value={value}
                    onBlur={onBlur}
                    onChange={onChange}
                    onClick={onClickHandler}
                />
            </LabelWrapper>
        );
    },
);

Radiobutton.displayName = 'Radiobutton';
