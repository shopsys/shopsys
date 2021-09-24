import { FC, InputHTMLAttributes, useEffect, useState } from 'react';
import { PasswordVisibilityToggleStyled, SearchButtonStyled, TextInputStyled } from './TextInput.style.';
import { ControllerRenderProps } from 'react-hook-form';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { getStateAfterValidation } from 'components/Forms/Helpers/getStateAfterValidation';
import Icon from 'components/Basic/Icon';
import LabelWrapper from 'components/Forms/Lib/LabelWrapper';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    never,
    'name' | 'id' | 'disabled' | 'style' | 'required'
>;

type TextInputProps = NativeProps & {
    /**
     * Display Label of the HTML input element
     */
    label: string;
    /**
     * A enumerator-like list of all available types of the custom TextInput element
     * @see https://www.w3schools.com/html/html_form_input_types.asp
     */
    type: 'text' | 'password' | 'email' | 'tel' | 'search';
    /**
     * A prop to decide if the input has errors
     */
    hasError?: boolean;
    /**
     * A prop to decide if the input is touched
     */
    isTouched?: boolean;
    /**
     * A enumerator-like list of all available sizes of the custom TextInput element
     */
    inputSize?: 'small';
    /**
     * Type of placeholder for check if the placeholder is static or adaptive.
     */
    placeholderType?: 'static';
    /**
     * Type for change variant of input.
     */
    variant?: 'searchInHeader';
    /**
     * A prop to define if the HTML textarea element should receive the .success CSS class when the input is correct
     */
    markSuccessfulWhenValid?: boolean;
    /**
     * a ref of the controlled field element used for hooking onto the field events/changes
     */
    fieldRef?: ControllerRenderProps;
};

/**
 * An HTML Input element used for text inputs of types: text, password, email, tel,
 */
const TextInput: FC<TextInputProps> = (props) => {
    const [inputState, setInputState] = useState<'success' | 'error' | undefined>(undefined);
    const [inputType, setInputType] = useState<'text' | 'password' | 'email' | 'tel' | 'search'>(props.type);

    const togglePasswordVisibilityHandler = () => {
        setInputType(inputType === 'text' ? 'text' : 'password');
    };

    useEffect(() => {
        setInputState(getStateAfterValidation(props.hasError, props.isTouched, props.markSuccessfulWhenValid));
    }, [props.hasError, props.isTouched, props.markSuccessfulWhenValid]);

    return (
        <LabelWrapper {...props} htmlFor={props.id} inputType="text-input">
            <TextInputStyled
                {...props.fieldRef}
                {...props}
                inputState={inputState}
                type={inputType}
                placeholder={props.label}
            />
            {props.type === 'password' && (
                <PasswordVisibilityToggleStyled
                    src="/svg/eye.svg"
                    isVisible={inputType === 'text'}
                    onClick={togglePasswordVisibilityHandler}
                />
            )}
            {props.variant === 'searchInHeader' && (
                <SearchButtonStyled>
                    <Icon icon="Search" />
                </SearchButtonStyled>
            )}
        </LabelWrapper>
    );
};

/* @component */
export default TextInput;
