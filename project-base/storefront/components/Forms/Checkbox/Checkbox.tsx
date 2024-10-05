import { LabelWrapper } from 'components/Forms/Lib/LabelWrapper';
import { forwardRef, InputHTMLAttributes, ReactNode } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'id' | 'onChange',
    'name' | 'disabled' | 'required' | 'onBlur' | 'className'
>;

export type CheckboxProps = NativeProps & {
    value: boolean;
    label: ReactNode;
    count?: number;
    labelWrapperClassName?: string;
};

export const Checkbox: FC<CheckboxProps> = forwardRef<HTMLInputElement, CheckboxProps>(
    (
        { id, name, label, count, required, disabled, onChange, value, className, labelWrapperClassName },
        checkboxForwardedRef,
    ) => (
        <LabelWrapper
            checked={value}
            className={labelWrapperClassName}
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
