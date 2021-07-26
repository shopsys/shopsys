import PropTypes from 'prop-types';
import React from 'react';
import { ShopsysInUserTextStyled } from './ShopsysInUserText.style.js';

/**
 * Global component that serves as a wrapper
 * for rendering the text/HTML users can insert into the WYSIWYG editor
 */
const ShopsysInUserText = (props) => {
    return (
        <ShopsysInUserTextStyled
            className={props.additionalClassName}
            dangerouslySetInnerHTML={{ __html: props.htmlContent }}
        ></ShopsysInUserTextStyled>
    );
};

ShopsysInUserText.propTypes = {
    /**
     * This prop is added to the html class list =>
     * class="[additionalClassName]"
     * it also accepts multiple classes in a form of a string with spaces between separate classes
     * e.g. additionalClassName="foo bar"
     */
    additionalClassName: PropTypes.string,
    /**
     * The actual content of the wrapper element,
     * can be both plain text and HTML content
     */
    htmlContent: PropTypes.string,
};

/* @component */
export default ShopsysInUserText;
