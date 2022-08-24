import { ErrorIconStyled, ErrorMessageStyled, FormFieldErrorStyled } from './FormLineError.style';
import { FC } from 'react';
import { FieldError } from 'react-hook-form';

type FormLineErrorProps = {
    inputType: 'textarea' | 'text-input' | 'checkbox' | 'text-input-password' | 'select';
    textInputSize?: 'small';
    error?: FieldError;
    'data-testid'?: string;
};

export const FormLineError: FC<FormLineErrorProps> = (props) => {
    const testIdentifier = props['data-testid'] ?? 'forms-error';

    if (props.error) {
        return (
            <FormFieldErrorStyled data-testid={testIdentifier}>
                <ErrorIconStyled
                    inputType={props.inputType}
                    textInputSize={props.textInputSize}
                    iconType="icon"
                    icon="Cross"
                />
                {props.error.message !== undefined && <ErrorMessageStyled>{props.error.message}</ErrorMessageStyled>}
            </FormFieldErrorStyled>
        );
    }

    return null;
};
