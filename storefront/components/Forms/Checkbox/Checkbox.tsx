import { CheckboxStyled } from './Checkbox.style';
import { LabelWrapper } from 'components/Forms/Lib/LabelWrapper/LabelWrapper';
import { FC, forwardRef, InputHTMLAttributes, ReactNode } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'id' | 'onChange',
    'name' | 'disabled' | 'required' | 'onBlur'
>;

export type CheckboxProps = NativeProps & {
    value: any;
    label: ReactNode;
    count?: number;
    testIdentifier?: string;
};

export const Checkbox: FC<CheckboxProps> = forwardRef<HTMLInputElement, CheckboxProps>(
    ({ id, name, label, count, required, disabled, onChange, value, testIdentifier }, checkboxForwardedRef) => {
        return (
            <LabelWrapper
                label={label}
                count={count}
                required={required}
                htmlFor={id}
                inputType="checkbox"
                checked={value as any}
            >
                <CheckboxStyled
                    id={id}
                    disabled={disabled}
                    required={required}
                    name={name}
                    onChange={onChange}
                    type="checkbox"
                    value={value}
                    ref={checkboxForwardedRef}
                    data-testid={testIdentifier}
                />
            </LabelWrapper>
        );
    },
);

Checkbox.displayName = 'Checkbox';
