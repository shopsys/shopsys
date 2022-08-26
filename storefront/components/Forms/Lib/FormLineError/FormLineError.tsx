import { ErrorIconStyled, ErrorMessageStyled, FormFieldErrorStyled } from './FormLineError.style';
import { FC } from 'react';
import { FieldError } from 'react-hook-form';

type FormLineErrorProps = {
    inputType: 'textarea' | 'text-input' | 'checkbox' | 'text-input-password' | 'select';
    textInputSize?: 'small';
    error?: FieldError;
    testIdentifier?: string;
};

const getTestIdentifier = (testIdentifier?: string) => testIdentifier ?? 'forms-error';

export const FormLineError: FC<FormLineErrorProps> = ({ inputType, error, testIdentifier, textInputSize }) => {
    if (error === undefined) {
        return null;
    }

    return (
        <FormFieldErrorStyled data-testid={getTestIdentifier(testIdentifier)}>
            <ErrorIconStyled inputType={inputType} textInputSize={textInputSize} iconType="icon" icon="Cross" />
            {error.message !== undefined && <ErrorMessageStyled>{error.message}</ErrorMessageStyled>}
        </FormFieldErrorStyled>
    );
};
