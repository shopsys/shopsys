import PropTypes, { InferProps } from 'prop-types';
import { ReactElement, useEffect, useState } from 'react';
import {
    StyledShopsysErrorIcon,
    StyledShopsysErrorMessage,
    StyledShopsysFormFieldError,
    StyledShopsysInputFormLine,
    StyledShopsysPasswordVisibilityToggle,
    StyledShopsysRequiredSymbol,
    StyledShopsysTextInput,
} from './ShopsysTextInput.style';
import { ErrorMessage } from '@hookform/error-message';
import { useFormContext } from 'react-hook-form';

/**
 * An HTML Input element used for text inputs of types: text, password, email, tel,
 */
function ShopsysTextInput(props: InferProps<typeof ShopsysTextInput.propTypes>): ReactElement {
    const { register, formState } = useFormContext();
    const [inputState, setInputState] = useState('');
    const [inputType, setInputType] = useState(props.type);

    /**
     * onClick handler which toggles the type of the HTML Input element
     * between text and password to allow user to display his password
     */
    const togglePasswordVisibilityHandler = () => {
        inputType === 'text' ? setInputType('password') : setInputType('text');
    };

    /**
     * Every time this field gets inserted or removed from the touched fields list
     * or error field list, we determine if the CSS class of the HTML element
     * should be updated
     */
    useEffect(() => {
        if (formState.errors[props.name]) {
            setInputState('error');
        } else if (props.markSuccessfulWhenValid && formState.touchedFields[props.name]) {
            setInputState('success');
        }
    }, [formState.touchedFields[props.name], formState.errors[props.name], props.markSuccessfulWhenValid]);

    return (
        <StyledShopsysInputFormLine>
            <StyledShopsysTextInput inputState={inputState}>
                <input
                    /**
                     * Registering the HTML input element with the React Hook Form Form Provider
                     */
                    {...register(props.name)}
                    name={props.name}
                    id={props.id}
                    disabled={props.disabled}
                    required={props.required}
                    type={inputType}
                    placeholder={props.label}
                />
                {/**
                 * The eye icon for HTML input elements of type password
                 */}
                {props.type === 'password' && (
                    <StyledShopsysPasswordVisibilityToggle
                        src="/svg/eye.svg"
                        className={inputType === 'password' ? 'not-visible' : undefined}
                        onClick={togglePasswordVisibilityHandler}
                    />
                )}
                <label htmlFor={props.id}>
                    {props.label}
                    {/**
                     * The star for required HTML input elements
                     */}
                    {props.required && <StyledShopsysRequiredSymbol>*</StyledShopsysRequiredSymbol>}
                </label>
            </StyledShopsysTextInput>
            {/**
             * Error section which is displayed or hidden based on the error fields list
             * and touched fields list
             */}
            {formState.errors[props.name] && (
                <StyledShopsysFormFieldError>
                    <StyledShopsysErrorIcon src="/svg/cross.svg" />
                    <ErrorMessage
                        errors={formState.errors}
                        name={props.name}
                        /**
                         * We get object {message, messages} as an argument of the render method in ErrorMessage component
                         * These messages are the result of validation resolver defined on the form itself
                         * To display single message, we can destructure the object
                         */
                        render={({ message }) => <StyledShopsysErrorMessage>{message}</StyledShopsysErrorMessage>}
                    />
                </StyledShopsysFormFieldError>
            )}
        </StyledShopsysInputFormLine>
    );
}

ShopsysTextInput.defaultProps = {
    disabled: false,
    required: false,
    type: 'text',
    markSuccessfulWhenValid: false,
};

ShopsysTextInput.propTypes = {
    /**
     * The ID of the HTML input element which is used for identification
     */
    id: PropTypes.string.isRequired,
    /**
     * The name of the HTML input element which is used by React Hook Form to manage the field
     */
    name: PropTypes.string.isRequired,
    /**
     * Display Label of the HTML input element
     */
    label: PropTypes.string.isRequired,
    /**
     * A prop to define if the HTML input element is disabled
     */
    disabled: PropTypes.bool.isRequired,
    /**
     * A prop to define if the HTML input element is required
     */
    required: PropTypes.bool.isRequired,
    /**
     * A enumerator-like list of all available types of the custom TextInput element
     */
    type: PropTypes.oneOf(['text', 'password', 'email', 'tel']).isRequired,
    /**
     * A prop to define if the HTML input element should receive the .success CSS class when the input is correct
     */
    markSuccessfulWhenValid: PropTypes.bool.isRequired,
};

/* @component */
export default ShopsysTextInput;
