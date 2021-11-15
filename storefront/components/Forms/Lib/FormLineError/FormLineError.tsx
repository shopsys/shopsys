import { ErrorIconStyled, ErrorMessageStyled, FormFieldErrorStyled } from './FormLineError.style';
import { FC } from 'react';
import { FieldError } from 'react-hook-form';

type FormLineErrorProps = {
    /**
     * A prop based on which the CSS styling is applied, as there is a slightly different
     * styling for each of the elements below.
     */
    inputType: 'textarea' | 'text-input' | 'checkbox' | 'text-input-password';
    /**
     * A prop which is automatically set based on the text input size.
     * This prop then sets the top indentation for the error icon.
     */
    textInputSize?: 'small';
    /**
     * errors object with separate errors for the given field
     */
    error?: FieldError;
};

const FormLineError: FC<FormLineErrorProps> = (props) => {
    if (props.error) {
        return (
            <FormFieldErrorStyled>
                <ErrorIconStyled
                    inputType={props.inputType}
                    textInputSize={props.textInputSize}
                    iconType="icon"
                    icon="Cross"
                />
                {props.error !== undefined && <ErrorMessageStyled>{props.error.message}</ErrorMessageStyled>}
            </FormFieldErrorStyled>
        );
    }

    return null;
};

export default FormLineError;
