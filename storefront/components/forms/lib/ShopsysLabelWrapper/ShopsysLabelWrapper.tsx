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
    htmlFor: PropTypes.string,
    required: PropTypes.bool,
    label: PropTypes.string.isRequired,
};

export default ShopsysLabelWrapper;
