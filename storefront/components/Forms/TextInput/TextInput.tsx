import { PasswordVisibilityToggleStyled, SearchButtonStyled, TextInputStyled } from './TextInput.style';
import { Icon } from 'components/Basic/Icon/Icon';
import { getStateAfterValidation } from 'components/Forms/Helpers/getStateAfterValidation';
import { LabelWrapper } from 'components/Forms/Lib/LabelWrapper/LabelWrapper';
import { FC, InputHTMLAttributes, useEffect, useState } from 'react';
import { ControllerRenderProps } from 'react-hook-form';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    never,
    'name' | 'id' | 'disabled' | 'style' | 'required' | 'onBlurCapture' | 'onChange' | 'onKeyPress'
>;

type TextInputProps = NativeProps & {
    label: string | JSX.Element;
    type: 'text' | 'password' | 'email' | 'tel' | 'search' | 'number';
    hasError?: boolean;
    isTouched?: boolean;
    inputSize?: 'small';
    placeholderType?: 'static';
    variant?: 'searchInHeader';
    markSuccessfulWhenValid?: boolean;
    fieldRef?: ControllerRenderProps<any, any>;
    isSearchButtonDisabled?: boolean;
    value?: number | string;
};

export const TextInput: FC<TextInputProps> = ({
    label,
    type,
    disabled,
    fieldRef,
    hasError,
    id,
    inputSize,
    isSearchButtonDisabled,
    isTouched,
    markSuccessfulWhenValid,
    name,
    onBlurCapture,
    onChange,
    onKeyPress,
    placeholderType,
    required,
    style,
    value,
    variant,
}) => {
    const [inputState, setInputState] = useState<'success' | 'error' | undefined>(undefined);
    const [inputType, setInputType] = useState<'text' | 'password' | 'email' | 'tel' | 'search' | 'number'>(type);

    const togglePasswordVisibilityHandler = () => {
        setInputType((currentInputType) => {
            if (currentInputType === 'password') {
                return 'text';
            }
            if (currentInputType === 'text') {
                return 'password';
            }
            return currentInputType;
        });
    };

    useEffect(() => {
        setInputState(getStateAfterValidation(hasError, isTouched, markSuccessfulWhenValid));
    }, [hasError, isTouched, markSuccessfulWhenValid]);

    return (
        <LabelWrapper
            label={label}
            placeholderType={placeholderType}
            required={required}
            htmlFor={id}
            inputType="text-input"
        >
            <TextInputStyled
                disabled={disabled}
                id={id}
                inputSize={inputSize}
                name={name}
                onBlurCapture={onBlurCapture}
                onChange={onChange}
                onKeyPress={onKeyPress}
                placeholderType={placeholderType}
                required={required}
                style={style}
                value={value}
                variant={variant}
                inputState={inputState}
                type={inputType}
                placeholder={typeof label === 'string' ? label : ' '}
                {...fieldRef}
            />
            {type === 'password' && (
                <PasswordVisibilityToggleStyled
                    src="/svg/eye.svg"
                    isVisible={inputType === 'text'}
                    onClick={togglePasswordVisibilityHandler}
                />
            )}
            {variant === 'searchInHeader' && (
                <SearchButtonStyled type="submit" disabled={isSearchButtonDisabled}>
                    <Icon iconType="icon" icon="Search" />
                </SearchButtonStyled>
            )}
        </LabelWrapper>
    );
};
