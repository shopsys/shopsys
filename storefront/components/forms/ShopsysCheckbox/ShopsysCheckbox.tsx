import PropTypes, { InferProps } from 'prop-types';
import {
    StyledShopsysCheckbox,
    StyledShopsysChoiceFormLine,
    StyledShopsysErrorIcon,
    StyledShopsysErrorMessage,
    StyledShopsysFormFieldError,
    StyledShopsysRequiredSymbol,
} from './ShopsysCheckbox.style';
import { ErrorMessage } from '@hookform/error-message';
import { ReactElement } from 'react';
import { useFormContext } from 'react-hook-form';

/**
 * An HTML Checkbox element of type checkbox
 */
function ShopsysCheckbox(props: InferProps<typeof ShopsysCheckbox.propTypes>): ReactElement {
    const { register, formState } = useFormContext();

    return (
        <StyledShopsysChoiceFormLine>
            <StyledShopsysCheckbox>
                <input
                    /**
                     * @see https://react-hook-form.com/api/useform/register
                     */
                    {...register(props.name)}
                    {...props}
                    type="checkbox"
                />
                <label htmlFor={props.id}>
                    {props.label}
                    {props.required && <StyledShopsysRequiredSymbol>*</StyledShopsysRequiredSymbol>}
                </label>
            </StyledShopsysCheckbox>
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
        </StyledShopsysChoiceFormLine>
    );
}

ShopsysCheckbox.defaultProps = {
    disabled: false,
    required: false,
};

ShopsysCheckbox.propTypes = {
    /**
     * The ID of the HTML checkbox element which is used for identification
     */
    id: PropTypes.string.isRequired,
    /**
     * The name of the HTML checkbox element which is used by React Hook Form to manage the field
     */
    name: PropTypes.string.isRequired,
    /**
     * Display Label of the HTML checkbox element
     */
    label: PropTypes.oneOfType([PropTypes.string.isRequired, PropTypes.arrayOf(PropTypes.node), PropTypes.node]),
    /**
     * A prop to define if the HTML checkbox element is disabled
     */
    disabled: PropTypes.bool.isRequired,
    /**
     * A prop to define if the HTML checkbox element is required
     */
    required: PropTypes.bool.isRequired,
};

/* @component */
export default ShopsysCheckbox;
