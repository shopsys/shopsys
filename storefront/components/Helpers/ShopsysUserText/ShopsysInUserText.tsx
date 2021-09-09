import PropTypes, { InferProps } from 'prop-types';
import { ReactElement } from 'react';
import { StyledShopsysInUserText } from './ShopsysInUserText.style';

/**
 * Global component that serves as a wrapper
 * for rendering the text/HTML users can insert into the WYSIWYG editor
 */
function ShopsysInUserText(props: InferProps<typeof ShopsysInUserText.propTypes>): ReactElement {
    return <StyledShopsysInUserText dangerouslySetInnerHTML={{ __html: props.htmlContent }}></StyledShopsysInUserText>;
}

ShopsysInUserText.propTypes = {
    /**
     * The actual content of the wrapper element,
     * can be both plain text and HTML content
     */
    htmlContent: PropTypes.string.isRequired,
};

/* @component */
export default ShopsysInUserText;
