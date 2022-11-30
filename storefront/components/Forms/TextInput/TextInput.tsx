import { TextInputStyled } from './TextInput.style';
import { LabelWrapper } from 'components/Forms/Lib/LabelWrapper/LabelWrapper';
import { forwardRef, InputHTMLAttributes, ReactNode } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'id' | 'onChange',
    'name' | 'disabled' | 'required' | 'onBlur' | 'onKeyPress' | 'className' | 'type'
>;

export type TextInputProps = NativeProps & {
    value: any;
    label: ReactNode;
    hasError?: boolean;
    testIdentifier?: string;
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
            onKeyPress,
            className,
            testIdentifier,
            value,
            type,
        },
        textInputForwarderRef,
    ) => {
        return (
            <LabelWrapper className={className} label={label} required={required} htmlFor={id} inputType="text-input">
                <TextInputStyled
                    className={className}
                    disabled={disabled}
                    id={id}
                    inputSize={inputSize}
                    name={name}
                    onBlur={onBlur}
                    onChange={onChange}
                    onKeyPress={onKeyPress}
                    required={required}
                    value={value}
                    hasError={hasError}
                    type={type}
                    placeholder={typeof label === 'string' ? label : ' '}
                    data-testid={testIdentifier}
                    ref={textInputForwarderRef}
                />
            </LabelWrapper>
        );
    },
);

TextInput.displayName = 'TextInput';
