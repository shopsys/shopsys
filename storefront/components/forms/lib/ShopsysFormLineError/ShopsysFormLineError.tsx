import { DeepMap, FieldError, FieldValues } from 'react-hook-form';
import PropTypes, { InferProps } from 'prop-types';
import {
    StyledShopsysErrorIcon,
    StyledShopsysErrorMessage,
    StyledShopsysFormFieldError,
} from './ShopsysFormLineError.style';
import { ErrorMessage } from '@hookform/error-message';
import { ReactElement } from 'react';

function ShopsysFormLineError(
    props: InferProps<typeof ShopsysFormLineError.propTypes> & { errors: DeepMap<FieldValues, FieldError> },
): ReactElement | null {
    if (props.errors[props.for]) {
        return (
            <StyledShopsysFormFieldError>
                <StyledShopsysErrorIcon inputType={props.inputType} src="/svg/cross.svg" />
                <ErrorMessage
                    errors={props.errors}
                    name={props.for}
                    render={({ message }) => <StyledShopsysErrorMessage>{message}</StyledShopsysErrorMessage>}
                />
            </StyledShopsysFormFieldError>
        );
    }

    return null;
}

ShopsysFormLineError.propTypes = {
    for: PropTypes.string.isRequired,
    /**
     * A prop based on which the CSS stzling is applied, as there is a slightly different
     * styling for each of the elements below.
     */
    inputType: PropTypes.oneOf<'textarea' | 'text-input' | 'checkbox'>(['textarea', 'text-input', 'checkbox'])
        .isRequired,
};

export default ShopsysFormLineError;
