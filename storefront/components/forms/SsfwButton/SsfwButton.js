import PropTypes from 'prop-types';
import React from 'react';
import { StyledSsfwButton, StyledSsfwButtonPrimary, StyledSsfwButtonSecondary } from './SsfwButton.style.js';

/**
 * Global component for Buttons.
 * We have for every modification of buttons special nametag element from styled-component. This method is used because we want to use the benefits of critical css and for this function is necessary to have it special styled-component element for each modification.
 */

const SsfwButton = (props) => {
    const { variant, children, type, additionalClassName, name } = props;
    let Component = StyledSsfwButton;

    if (variant == 'primary') {
        Component = StyledSsfwButtonPrimary;
    } else if (variant == 'secondary') {
        Component = StyledSsfwButtonSecondary;
    }

    return (
        <Component type={type} className={'btn ' + additionalClassName} name={name}>
            {children}
        </Component>
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
    /**
     * Type for change variant of button. If you don't fill this prop then the button will be in default modification.
     */
    variant: PropTypes.string,
};

/* @component */
export default SsfwButton;
