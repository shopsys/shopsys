import PropTypes from 'prop-types';
import React from 'react';
import { SsfwButtonStyled } from './SsfwButton.style.js';

/**
 * Global component for Buttons
 */

const SsfwButton = (props) => {
    return (
        <SsfwButtonStyled type={props.type} className={'btn ' + props.additionalClassName} name={props.name}>
            {props.children}
        </SsfwButtonStyled>
    );
};

SsfwButton.defaultProps = {
    additionalClassName: 'btn--default',
    type: 'button',
};

SsfwButton.propTypes = {
    /**
     * Attr name value for input Html element.
     */
    name: PropTypes.string.isRequired,
    /**
     * This prop is added at the end of html class list =>
     * class="btn [additionalClassName]"
     */
    additionalClassName: PropTypes.oneOfType([PropTypes.array, PropTypes.string]),
    /**
     * Type for change input type button/submit etc.
     */
    type: PropTypes.string,
};

/* @component */
export default SsfwButton;
