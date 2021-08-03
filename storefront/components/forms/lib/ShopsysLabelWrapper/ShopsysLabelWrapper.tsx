import { LabelHTMLAttributes, ReactElement } from 'react';
import PropTypes, { InferProps } from 'prop-types';
import { StyledShopsysLabelWrapper, StyledShopsysRequiredSymbol } from './ShopsysLabelWrapper.style';

type NativeProps = Pick<LabelHTMLAttributes<HTMLLabelElement>, 'children' | 'htmlFor'>;

function ShopsysLabelWrapper(props: InferProps<typeof ShopsysLabelWrapper.propTypes> & NativeProps): ReactElement {
    return (
        <StyledShopsysLabelWrapper>
            {props.children}
            <label htmlFor={props.htmlFor}>
                {props.label}
                {props.required && <StyledShopsysRequiredSymbol>*</StyledShopsysRequiredSymbol>}
            </label>
        </StyledShopsysLabelWrapper>
    );
}

ShopsysLabelWrapper.propTypes = {
    /**
     * A prop based on which the "required symbol" (star) is displayed next to the label
     */
    required: PropTypes.bool,
    /**
     * Display Label of the given HTML element
     */
    label: PropTypes.oneOfType([PropTypes.string.isRequired, PropTypes.arrayOf(PropTypes.node), PropTypes.node]),
};

export default ShopsysLabelWrapper;
