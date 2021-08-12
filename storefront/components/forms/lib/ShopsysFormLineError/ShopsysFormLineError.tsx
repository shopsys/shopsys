import { DeepMap, FieldError, FieldValues } from 'react-hook-form';
import PropTypes, { InferProps } from 'prop-types';
import {
    StyledShopsysErrorIcon,
    StyledShopsysErrorMessage,
    StyledShopsysFormFieldError,
} from './ShopsysFormLineError.style';
import { ErrorMessage } from '@hookform/error-message';
import { ReactElement } from 'react';
import ShopsysIcon from '../../../basic/ShopsysIcon';

function ShopsysFormLineError(
    props: InferProps<typeof ShopsysFormLineError.propTypes> & { errors: DeepMap<FieldValues, FieldError> },
): ReactElement | null {
    if (props.errors[props.for]) {
        return (
            <StyledShopsysFormFieldError>
                <StyledShopsysErrorIcon inputType={props.inputType} textInputSize="default">
                    <ShopsysIcon icon="cross" iconHeight={16} />
                </StyledShopsysErrorIcon>
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

ShopsysFormLineError.defaultProps = {
    textInputSize: 'default',
};

ShopsysFormLineError.propTypes = {
    /**
     * A prop which originally is the name of the component for which the error should be shown.
     * Here as a "for" prop it defines which errors should be displayed inside this error message.
     */
    for: PropTypes.string.isRequired,
    /**
     * A prop based on which the CSS styling is applied, as there is a slightly different
     * styling for each of the elements below.
     */
    inputType: PropTypes.oneOf<'textarea' | 'text-input' | 'checkbox'>(['textarea', 'text-input', 'checkbox'])
        .isRequired,
    /**
     * A prop which is automatically set based on the text input size.
     * This prop then sets the top indentation for the error icon.
     */
    textInputSize: PropTypes.oneOf<'default' | 'small'>(['default', 'small']).isRequired,
};

export default ShopsysFormLineError;
