import React, { useEffect, useState } from 'react';
import {
    ShopsysErrorIcon,
    ShopsysErrorMessage,
    ShopsysFormFieldErrorStyled,
    ShopsysInputFormLine,
    ShopsysPasswordVisibilityToggle,
    ShopsysTextInputStyled,
} from './ShopsysTextInput.style.js';
import { ErrorMessage } from '@hookform/error-message';
import PropTypes from 'prop-types';
import { useFormContext } from 'react-hook-form';

/**
 * An HTML Input element used for text inputs of types: text, password, email, tel,
 */
const ShopsysTextInput = (props) => {
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
        if (formState.touchedFields[props.name]) {
            if (formState.errors[props.name]) {
                setInputState('error');
            } else if (props.shouldUseSuccess) {
                setInputState('success');
            }
        }
    }, [formState.touchedFields[props.name], formState.errors[props.name]]);

    return (
        <ShopsysInputFormLine>
            <ShopsysTextInputStyled inputState={inputState}>
                <input
                    id={props.id}
                    type={inputType}
                    name={props.name}
                    required={props.required}
                    placeholder={props.label}
                    disabled={props.disabled}
                    /**
                     * Registering the HTML input element with the React Hook Form Form Provider
                     */
                    {...register(props.name)}
                />
                {/**
                 * The eye icon for HTML input elements of type password
                 */}
                {props.type === 'password' && (
                    <ShopsysPasswordVisibilityToggle
                        src="/svg/eye.svg"
                        className={inputType === 'password' && 'not-visible'}
                        onClick={togglePasswordVisibilityHandler}
                    />
                )}
                <label htmlFor={props.id}>
                    {props.label}
                    {/**
                     * The star for required HTML input elements
                     */}
                    {props.required && <span className={'required'}>*</span>}
                </label>
            </ShopsysTextInputStyled>
            {/**
             * Error section which is displayed or hidden based on the error fields list
             * and touched fields list
             */}
            {formState.errors[props.name] && formState.touchedFields[props.name] && (
                <ShopsysFormFieldErrorStyled>
                    <ShopsysErrorIcon src="/svg/cross.svg" />
                    <ErrorMessage
                        errors={formState.errors}
                        name={props.name}
                        /**
                         * We get object {message, messages} as an argument of the render method in ErrorMessage component
                         * These messages are the result of validation resolver defined on the form itself
                         * To display single message, we can destructure the object
                         */
                        render={({ message }) => <ShopsysErrorMessage>{message}</ShopsysErrorMessage>}
                    />
                </ShopsysFormFieldErrorStyled>
            )}
        </ShopsysInputFormLine>
    );
};

ShopsysTextInput.defaultProps = {
    disabled: false,
    required: false,
    type: 'text',
    shouldUseSuccess: false,
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
    disabled: PropTypes.bool,
    /**
     * A prop to define if the HTML input element is required
     */
    required: PropTypes.bool,
    /**
     * A enumerator-like list of all available types of the custom TextInput element
     */
    type: PropTypes.oneOf(['text', 'password', 'email', 'tel']),
    /**
     * A prop to define if the HTML input element should receive the .success CSS class when the input is correct
     */
    shouldUseSuccess: PropTypes.bool,
};

/* @component */
export default ShopsysTextInput;
