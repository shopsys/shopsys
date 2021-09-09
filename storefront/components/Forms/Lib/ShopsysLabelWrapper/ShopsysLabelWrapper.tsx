import { LabelHTMLAttributes, ReactElement } from 'react';
import PropTypes, { InferProps } from 'prop-types';
import { StyledShopsysLabelWrapper, StyledShopsysRequiredSymbol } from './ShopsysLabelWrapper.style';

type NativeProps = Pick<LabelHTMLAttributes<HTMLLabelElement>, 'children' | 'htmlFor'>;

function ShopsysLabelWrapper(props: InferProps<typeof ShopsysLabelWrapper.propTypes> & NativeProps): ReactElement {
    return (
        <StyledShopsysLabelWrapper placeholderType={props.placeholderType} inputType={props.inputType}>
            {props.children}
            {props.placeholderType === 'adaptive' && (
                <label htmlFor={props.htmlFor}>
                    {props.label}
                    {props.required && <StyledShopsysRequiredSymbol>*</StyledShopsysRequiredSymbol>}
                </label>
            )}
        </StyledShopsysLabelWrapper>
    );
}

ShopsysLabelWrapper.defaultProps = {
    placeholderType: 'adaptive',
};

ShopsysLabelWrapper.propTypes = {
    /**
     * A prop based on which the "required symbol" (star) is displayed next to the label
     */
    required: PropTypes.bool,
    /**
     * Display Label of the given HTML element
     */
    label: PropTypes.oneOfType([PropTypes.string.isRequired, PropTypes.arrayOf(PropTypes.node), PropTypes.node]),
    /**
     * A prop based on which the CSS stzling is applied, as there is a slightly different
     * styling for each of the elements below.
     */
    inputType: PropTypes.oneOf<'textarea' | 'text-input' | 'checkbox' | 'radio'>([
        'textarea',
        'text-input',
        'checkbox',
        'radio',
    ]).isRequired,
    /**
     * Type of placeholder for check if the placeholder is static or adaptive.
     */
    placeholderType: PropTypes.oneOf<'static' | 'adaptive'>(['static', 'adaptive']).isRequired,
};

export default ShopsysLabelWrapper;
