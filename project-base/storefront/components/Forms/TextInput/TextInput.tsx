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
                    'peer w-full rounded-md border-2 px-3 pt-5 [-moz-appearance:textfield] [-webkit-appearance:none] placeholder:[color:transparent] focus:outline-none disabled:pointer-events-none disabled:cursor-no-drop transition font-bold',
                    'border-inputBorder bg-inputBackground text-inputText',
                    'disabled:border-inputBorderDisabled disabled:bg-inputBackgroundDisabled disabled:text-inputTextDisabled',
                    'hover:border-inputBorderHovered hover:bg-inputBackgroundHovered hover:text-inputTextHovered',
                    'focus:border-inputBorderActive focus:bg-inputBackgroundActive focus:text-inputTextActive',
                    '[&:-internal-autofill-selected]:!bg-inputBackground [&:-internal-autofill-selected]:!shadow-inner [&:-webkit-autofill]:!bg-inputBackground [&:-webkit-autofill]:!shadow-inner',
                    '[&:-webkit-autofill]:hover:!bg-inputBackgroundActive [&:-webkit-autofill]:hover:!shadow-inner',
                    '[&:-webkit-autofill]:focus:!bg-inputBackgroundActive [&:-webkit-autofill]:focus:!shadow-inner',
                    inputSize === 'small' ? 'text-small h-12' : 'h-14',
                    hasError && 'border-inputError bg-inputBackground shadow-none',
                    type === 'password' && 'text-2xl text-inputTextDisabled focus-visible:text-inputText',
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
