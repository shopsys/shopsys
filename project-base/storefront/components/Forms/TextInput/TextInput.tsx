import { LabelWrapper } from 'components/Forms/Lib/LabelWrapper';
import { forwardRef, InputHTMLAttributes, ReactNode } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'id',
    | 'name'
    | 'disabled'
    | 'required'
    | 'onBlur'
    | 'onKeyDown'
    | 'className'
    | 'type'
    | 'children'
    | 'autoComplete'
    | 'onChange'
    | 'inputMode'
    | 'aria-label'
    | 'aria-labelledby'
>;

export type TextInputProps = NativeProps & {
    value: any;
    label?: ReactNode;
    hasError?: boolean;
    inputSize?: 'small' | 'default';
};

export const TextInput = forwardRef<HTMLInputElement, TextInputProps>(
    (
        {
            label,
            hasError,
            inputSize = 'default',
            name,
            id,
            disabled,
            required,
            onBlur,
            onChange,
            onKeyDown,
            className,
            value,
            type,
            children,
            autoComplete,
            inputMode,
            'aria-label': ariaLabel,
            'aria-labelledby': ariaLabelledby,
        },
        textInputForwarderRef,
    ) => (
        <LabelWrapper className={className} htmlFor={id} inputType="text-input" label={label} required={required}>
            <input
                aria-label={ariaLabel}
                aria-labelledby={ariaLabelledby}
                autoComplete={autoComplete}
                disabled={disabled}
                id={id}
                inputMode={inputMode}
                name={name}
                placeholder={typeof label === 'string' ? label : ' '}
                ref={textInputForwarderRef}
                required={required}
                type={type}
                value={value}
                className={twMergeCustom(
                    // class "peer" is used for styling in LabelWrapper
                    'peer rounded-input w-full border-2 px-3 pt-5 font-semibold transition [-moz-appearance:textfield] [-webkit-appearance:none] placeholder:[color:transparent] focus:outline-hidden disabled:pointer-events-none disabled:cursor-no-drop',
                    'border-input-border-default bg-input-bg-default text-input-text-default',
                    'disabled:border-input-border-disabled disabled:bg-input-bg-disabled disabled:text-input-text-disabled',
                    !hasError && 'hover:border-input-border-hovered hover:text-input-text-hovered',
                    !hasError && 'focus:border-input-border-active focus:text-input-text-active',
                    '[&:-internal-autofill-selected]:!bg-input-bg-default [&:-webkit-autofill]:!bg-input-bg-default [&:-internal-autofill-selected]:!shadow-inner [&:-webkit-autofill]:!shadow-inner',
                    '[&:-webkit-autofill]:hover:!bg-input-fill [&:-webkit-autofill]:hover:!shadow-inner',
                    '[&:-webkit-autofill]:focus:!bg-input-fill [&:-webkit-autofill]:focus:!shadow-inner',
                    inputSize === 'small' ? 'text-small h-12' : 'h-14',
                    hasError && 'border-input-border-error bg-input-bg-default shadow-none',
                    type === 'password' && 'text-input-text-default',
                    className,
                )}
                onBlur={onBlur}
                onChange={onChange}
                onKeyDown={onKeyDown}
            />
            {children}
        </LabelWrapper>
    ),
);

TextInput.displayName = 'TextInput';
