import {
    FIELD_VALUE_PADDING_CLASS_NAME,
    FieldSize,
    getFieldHeightClassName,
    getFieldStateClassName,
} from 'components/Forms/Lib/fieldUtils';
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
    | 'aria-describedby'
    | 'aria-invalid'
    | 'aria-label'
    | 'aria-labelledby'
    | 'aria-hidden'
>;

export type TextInputProps = NativeProps & {
    value: any;
    label?: ReactNode;
    hasError?: boolean;
    hasWarning?: boolean;
    inputSize?: FieldSize;
};

export const TextInput = forwardRef<HTMLInputElement, TextInputProps>(
    (
        {
            label,
            hasError,
            hasWarning,
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
            'aria-describedby': ariaDescribedby,
            'aria-invalid': ariaInvalid,
            'aria-label': ariaLabel,
            'aria-labelledby': ariaLabelledby,
            'aria-hidden': ariaHidden,
        },
        textInputForwarderRef,
    ) => (
        <LabelWrapper
            className={className}
            htmlFor={id}
            inputSize={inputSize}
            inputType="text-input"
            label={label}
            required={required}
        >
            <input
                aria-describedby={ariaDescribedby}
                aria-hidden={ariaHidden}
                aria-invalid={ariaInvalid}
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
                spellCheck={false}
                type={type}
                value={value}
                className={twMergeCustom(
                    // class "peer" is used for styling in LabelWrapper
                    'peer w-full rounded-input px-3 font-medium [-moz-appearance:textfield] [-webkit-appearance:none] placeholder:text-transparent focus:outline-hidden disabled:pointer-events-none disabled:cursor-no-drop',
                    getFieldStateClassName({ hasError, hasWarning }),
                    '[&:-internal-autofill-selected]:bg-input-bg-default! [&:-internal-autofill-selected]:shadow-inner! [&:-webkit-autofill]:bg-input-bg-default! [&:-webkit-autofill]:shadow-inner!',
                    '[&:-webkit-autofill]:hover:bg-input-fill! [&:-webkit-autofill]:hover:shadow-inner!',
                    '[&:-webkit-autofill]:focus:bg-input-fill! [&:-webkit-autofill]:focus:shadow-inner!',
                    getFieldHeightClassName(inputSize),
                    inputSize === 'small' ? 'pt-3.5 text-sm' : FIELD_VALUE_PADDING_CLASS_NAME,
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
