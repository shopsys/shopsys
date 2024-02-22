import { LabelWrapper } from 'components/Forms/Lib/LabelWrapper';
import { twMergeCustom } from 'helpers/twMerge';
import { forwardRef, InputHTMLAttributes, ReactNode } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'id' | 'onChange',
    'name' | 'disabled' | 'required' | 'onBlur' | 'className'
>;

export type CheckboxProps = NativeProps & {
    value: boolean;
    label: ReactNode;
    count?: number;
};

export const Checkbox: FC<CheckboxProps> = forwardRef<HTMLInputElement, CheckboxProps>(
    ({ id, name, label, count, required, disabled, onChange, value, className }, checkboxForwardedRef) => (
        <LabelWrapper
            checked={value}
            count={count}
            disabled={disabled}
            htmlFor={id}
            inputType="checkbox"
            label={label}
            required={required}
        >
            <input
                // class "peer" is used for styling in LabelWrapper
                checked={value}
                className={twMergeCustom('peer sr-only', className)}
                disabled={disabled}
                id={id}
                name={name}
                ref={checkboxForwardedRef}
                required={required}
                type="checkbox"
                value={value as any}
                onChange={onChange}
            />
        </LabelWrapper>
    ),
);

Checkbox.displayName = 'Checkbox';
