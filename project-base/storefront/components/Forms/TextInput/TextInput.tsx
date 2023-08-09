import { LabelWrapper } from 'components/Forms/Lib/LabelWrapper';
import { forwardRef, InputHTMLAttributes, ReactNode } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'helpers/twMerge';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'id' | 'onChange',
    'name' | 'disabled' | 'required' | 'onBlur' | 'onKeyPress' | 'className' | 'type' | 'children' | 'autoComplete'
>;

export type TextInputProps = NativeProps & {
    value: any;
    label: ReactNode;
    hasError?: boolean;
    dataTestId?: string;
    inputSize?: 'small' | 'default';
    isWithoutLabel?: boolean;
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
            onKeyPress,
            className,
            dataTestId,
            value,
            type,
            isWithoutLabel,
            children,
            autoComplete,
        },
        textInputForwarderRef,
    ) => (
        <LabelWrapper
            className={className}
            label={label}
            required={required}
            htmlFor={id}
            inputType="text-input"
            isWithoutLabel={isWithoutLabel}
        >
            <input
                className={twMergeCustom(
                    // class "peer" is used for styling in LabelWrapper
                    'peer w-full rounded-xl border-2 border-border bg-white px-3 pt-5 text-dark [-moz-appearance:textfield] [-webkit-appearance:none] placeholder:[color:transparent] focus:outline-none disabled:pointer-events-none disabled:cursor-no-drop disabled:opacity-50',
                    '[&:-webkit-autofill]:!bg-white [&:-webkit-autofill]:!shadow-inner [&:-webkit-autofill]:hover:!bg-white [&:-webkit-autofill]:hover:!shadow-inner [&:-webkit-autofill]:focus:!bg-white [&:-webkit-autofill]:focus:!shadow-inner [&:-internal-autofill-selected]:!bg-white [&:-internal-autofill-selected]:!shadow-inner',
                    inputSize === 'small' ? 'text-small h-12' : 'text-body h-14',
                    hasError && 'border-red bg-white shadow-none',
                    type === 'password' && 'text-2xl text-greyLighter focus-visible:text-dark',
                    className,
                )}
                disabled={disabled}
                id={id}
                name={name}
                onBlur={onBlur}
                onChange={onChange}
                onKeyPress={onKeyPress}
                required={required}
                value={value}
                type={type}
                autoComplete={autoComplete}
                placeholder={typeof label === 'string' ? label : ' '}
                data-testid={dataTestId}
                ref={textInputForwarderRef}
            />
            {children}
        </LabelWrapper>
    ),
);

TextInput.displayName = 'TextInput';
