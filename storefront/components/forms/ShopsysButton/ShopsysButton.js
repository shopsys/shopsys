import PropTypes from 'prop-types';
import React from 'react';
import {
    StyledShopsysButton,
    StyledShopsysButtonPrimary,
    StyledShopsysButtonSecondary,
} from './ShopsysButton.style.js';

/**
 * Global component for Buttons.
 * We have for every modification of buttons special nametag element from styled-component.
 * This method is used because we want to use the benefits of critical css and for this function is necessary to have it special styled-component element for each modification.
 * We can also combinate variants and sizes.
 */

const ShopsysButton = (props) => {
    let Component = StyledShopsysButton;

    if (props.variant == 'primary') {
        Component = StyledShopsysButtonPrimary;
    } else if (props.variant == 'secondary') {
        Component = StyledShopsysButtonSecondary;
    }

    return (
        <Component type={props.type} name={props.name} {...props}>
            {props.children}
        </Component>
    );
};

ShopsysButton.defaultProps = {
    type: 'button',
    size: '',
};

ShopsysButton.propTypes = {
    /**
     * Attr name value for input Html element.
     */
    name: PropTypes.string.isRequired,
    /**
     * Type for change input type button/submit etc.
     */
    type: PropTypes.string,
    /**
     * Type for change variant of button. If you don't fill this prop then the button will be in default modification.
     */
    variant: PropTypes.oneOf(['primary', 'secondary']),
    /**
     * Type for change size of button. If you don't fill this prop then the button will be in default size.
     */
    size: PropTypes.oneOf(['small']),
};

/* @component */
export default ShopsysButton;
