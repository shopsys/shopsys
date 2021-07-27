import {
    StyledShopsysButton,
    StyledShopsysButtonPrimary,
    StyledShopsysButtonSecondary,
} from './ShopsysButton.style.js';
import PropTypes from 'prop-types';
import React from 'react';

/**
 * Global component for Buttons.
 * We have a special nametag for every modification of button which correlates to the styled components.
 * This method is used because we want to take advantage of the benefits of critical CSS and for that, it is necessary to have a styled-component element for each modification.
 * We can also combine variants and sizes.
 */
const ShopsysButton = (props) => {
    let Component = StyledShopsysButton;

    if (props.variant === 'primary') {
        Component = StyledShopsysButtonPrimary;
    } else if (props.variant === 'secondary') {
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
};

ShopsysButton.propTypes = {
    /**
     * Attr name value for input HTML element.
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
