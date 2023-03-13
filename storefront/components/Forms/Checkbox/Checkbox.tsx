import { LabelWrapper } from 'components/Forms/Lib/LabelWrapper';
import { forwardRef, InputHTMLAttributes, ReactNode } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'id' | 'onChange',
    'name' | 'disabled' | 'required' | 'onBlur'
>;

export type CheckboxProps = NativeProps & {
    value: boolean;
    label: ReactNode;
    count?: number;
    dataTestId?: string;
};

export const Checkbox: FC<CheckboxProps> = forwardRef<HTMLInputElement, CheckboxProps>(
    ({ id, name, label, count, required, disabled, onChange, value, dataTestId }, checkboxForwardedRef) => (
        <LabelWrapper label={label} count={count} required={required} htmlFor={id} checked={value} inputType="checkbox">
            <input
                // class "peer" is used for styling in LabelWrapper
                className="peer sr-only"
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
