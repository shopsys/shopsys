import { FieldValues, FormState, useFormContext } from 'react-hook-form';
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

/**
 * An HTML Input element used for text inputs of types: text, password, email, tel,
 */
function ShopsysTextInput(props: InferProps<typeof ShopsysTextInput.propTypes>): ReactElement {
    const { register, formState } = useFormContext();
    const [inputState, setInputState] = useState<string | undefined>(undefined);
    const [inputType, setInputType] = useState(props.type);

    const togglePasswordVisibilityHandler = () => {
        inputType === 'text' ? setInputType('password') : setInputType('text');
    };

    useEffect(() => {
        setInputState(getStateAfterValidation(formState, props));
    }, [formState.touchedFields[props.name], formState.errors[props.name], props.markSuccessfulWhenValid]);

    return (
        <StyledShopsysInputFormLine>
            <StyledShopsysTextInput inputState={inputState}>
                <input
                    /**
                     * @see https://react-hook-form.com/api/useform/register
                     */
                    {...register(props.name)}
                    name={props.name}
                    id={props.id}
                    disabled={props.disabled}
                    required={props.required}
                    type={inputType}
                    placeholder={props.label}
                />
                {props.type === 'password' && (
                    <StyledShopsysPasswordVisibilityToggle
                        src="/svg/eye.svg"
                        className={inputType === 'password' ? 'not-visible' : undefined}
                        onClick={togglePasswordVisibilityHandler}
                    />
                )}
                <label htmlFor={props.id}>
                    {props.label}
                    {props.required && <StyledShopsysRequiredSymbol>*</StyledShopsysRequiredSymbol>}
                </label>
            </StyledShopsysTextInput>
            {formState.errors[props.name] && (
                <StyledShopsysFormFieldError>
                    <StyledShopsysErrorIcon src="/svg/cross.svg" />
                    {/**
                     * @see https://react-hook-form.com/api/useformstate/errormessage
                     */}
                    <ErrorMessage
                        errors={formState.errors}
                        name={props.name}
                        render={({ message }) => <StyledShopsysErrorMessage>{message}</StyledShopsysErrorMessage>}
                    />
                </StyledShopsysFormFieldError>
            )}
        </StyledShopsysInputFormLine>
    );
}

const getStateAfterValidation = (
    formState: FormState<FieldValues>,
    props: InferProps<typeof ShopsysTextInput.propTypes>,
) => {
    if (formState.errors[props.name]) {
        return 'error';
    }

    if (props.markSuccessfulWhenValid && formState.touchedFields[props.name]) {
        return 'success';
    }
};

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
