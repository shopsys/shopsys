import { LabelWrapper } from 'components/Forms/Lib/LabelWrapper';
import { twMergeCustom } from 'helpers/twMerge';
import { forwardRef, InputHTMLAttributes, ReactNode } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';

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
        },
        textInputForwarderRef,
    ) => (
        <LabelWrapper className={className} htmlFor={id} inputType="text-input" label={label} required={required}>
            <input
                autoComplete={autoComplete}
                disabled={disabled}
                id={id}
                name={name}
                placeholder={typeof label === 'string' ? label : ' '}
                ref={textInputForwarderRef}
                required={required}
                type={type}
                value={value}
                className={twMergeCustom(
                    // class "peer" is used for styling in LabelWrapper
                    'peer w-full rounded border-2 border-border bg-white px-3 pt-5 text-dark [-moz-appearance:textfield] [-webkit-appearance:none] placeholder:[color:transparent] focus:outline-none disabled:pointer-events-none disabled:cursor-no-drop disabled:opacity-50',
                    '[&:-internal-autofill-selected]:!bg-white [&:-internal-autofill-selected]:!shadow-inner [&:-webkit-autofill]:!bg-white [&:-webkit-autofill]:!shadow-inner [&:-webkit-autofill]:hover:!bg-white [&:-webkit-autofill]:hover:!shadow-inner [&:-webkit-autofill]:focus:!bg-white [&:-webkit-autofill]:focus:!shadow-inner',
                    inputSize === 'small' ? 'text-small h-12' : 'text-body h-14',
                    hasError && 'border-red bg-white shadow-none',
                    type === 'password' && 'text-2xl text-greyLighter focus-visible:text-dark',
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
