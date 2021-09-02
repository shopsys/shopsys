import { LabelHTMLAttributes, ReactElement } from 'react';
import PropTypes, { InferProps } from 'prop-types';
import { StyledShopsysColorLabelWrapper } from './ShopsysColorLabelWrapper.style';

type NativeProps = Pick<LabelHTMLAttributes<HTMLLabelElement>, 'children' | 'htmlFor'>;

function ShopsysColorLabelWrapper(
    props: InferProps<typeof ShopsysColorLabelWrapper.propTypes> & NativeProps,
): ReactElement {
    const circleBackgroundColorStyle = {
        'background-color': props.color,
    } as React.CSSProperties;

    return (
        <StyledShopsysColorLabelWrapper>
            {props.children}
            <label htmlFor={props.htmlFor} style={circleBackgroundColorStyle} title={props.label}></label>
        </StyledShopsysColorLabelWrapper>
    );
}

ShopsysColorLabelWrapper.defaultProps = {
    placeholderType: 'adaptive',
};

ShopsysColorLabelWrapper.propTypes = {
    /**
     * Display Label of the given HTML element
     */
    label: PropTypes.oneOfType([PropTypes.string.isRequired, PropTypes.arrayOf(PropTypes.node), PropTypes.node]),
};

export default ShopsysColorLabelWrapper;
