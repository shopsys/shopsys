import { FieldValues, FormState, useFormContext } from 'react-hook-form';
import PropTypes, { InferProps } from 'prop-types';
import { ReactElement, useEffect, useState } from 'react';
import {
    StyledShopsysErrorIcon,
    StyledShopsysErrorMessage,
    StyledShopsysFormFieldError,
    StyledShopsysInputFormLine,
    StyledShopsysRequiredSymbol,
    StyledShopsysTextarea,
} from './ShopsysTextarea.style';
import { ErrorMessage } from '@hookform/error-message';

/**
 * An HTML Textarea element
 */
function ShopsysTextarea(props: InferProps<typeof ShopsysTextarea.propTypes>): ReactElement {
    const { register, formState } = useFormContext();
    const [inputState, setInputState] = useState<string | undefined>(undefined);

    useEffect(() => {
        setInputState(getStateAfterValidation(formState, props));
    }, [formState.touchedFields[props.name], formState.errors[props.name], props.markSuccessfulWhenValid]);

    return (
        <StyledShopsysInputFormLine>
            <StyledShopsysTextarea inputState={inputState}>
                <textarea
                    /**
                     * @see https://react-hook-form.com/api/useform/register
                     */
                    {...register(props.name)}
                    name={props.name}
                    id={props.id}
                    disabled={props.disabled}
                    required={props.required}
                    placeholder={props.label}
                    rows={props.rows}
                />
                <label htmlFor={props.id}>
                    {props.label}
                    {props.required && <StyledShopsysRequiredSymbol>*</StyledShopsysRequiredSymbol>}
                </label>
            </StyledShopsysTextarea>
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
    props: InferProps<typeof ShopsysTextarea.propTypes>,
) => {
    if (formState.errors[props.name]) {
        return 'error';
    }

    if (props.markSuccessfulWhenValid && formState.touchedFields[props.name]) {
        return 'success';
    }
};

ShopsysTextarea.defaultProps = {
    disabled: false,
    required: false,
    rows: 4,
    markSuccessfulWhenValid: false,
};

ShopsysTextarea.propTypes = {
    /**
     * The ID of the HTML textarea element which is used for identification
     */
    id: PropTypes.string.isRequired,
    /**
     * The name of the HTML textarea element which is used by React Hook Form to manage the field
     */
    name: PropTypes.string.isRequired,
    /**
     * Display Label of the HTML textarea element
     */
    label: PropTypes.string.isRequired,
    /**
     * A prop to define if the HTML textarea element is disabled
     */
    disabled: PropTypes.bool.isRequired,
    /**
     * A prop to define if the HTML textarea element is required
     */
    required: PropTypes.bool.isRequired,
    /**
     * A prop to define number of rows for the html textarea element
     */
    rows: PropTypes.number.isRequired,
    /**
     * A prop to define if the HTML textarea element should receive the .success CSS class when the input is correct
     */
    markSuccessfulWhenValid: PropTypes.bool.isRequired,
};

/* @component */
export default ShopsysTextarea;
