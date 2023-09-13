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
    dataTestId?: string;
};

export const Checkbox: FC<CheckboxProps> = forwardRef<HTMLInputElement, CheckboxProps>(
    ({ id, name, label, count, required, disabled, onChange, value, dataTestId, className }, checkboxForwardedRef) => (
        <LabelWrapper
            label={label}
            count={count}
            required={required}
            htmlFor={id}
            checked={value}
            inputType="checkbox"
            disabled={disabled}
        >
            <input
                // class "peer" is used for styling in LabelWrapper
                className={twMergeCustom('peer sr-only', className)}
                id={id}
                disabled={disabled}
                required={required}
                name={name}
                onChange={onChange}
                type="checkbox"
                checked={value}
                value={value as any}
                ref={checkboxForwardedRef}
                data-testid={dataTestId}
            />
        </LabelWrapper>
    ),
);

Checkbox.displayName = 'Checkbox';
