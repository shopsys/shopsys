import PropTypes, { InferProps } from 'prop-types';
import { ReactElement, ReactNode } from 'react';
import { StyledShopsysButton, StyledShopsysButtonPrimary, StyledShopsysButtonSecondary } from './ShopsysButton.style';
import { CSSProperties } from 'styled-components';

/**
 * Global component for Buttons.
 * We have a special nametag for every modification of button which correlates to the styled components.
 * This method is used because we want to take advantage of the benefits of critical CSS and for that, it is necessary to have a styled-component element for each modification.
 * We can also combine variants and sizes.
 */
type nativeProps = {
    children: ReactNode;
    onClick?: React.MouseEventHandler<HTMLButtonElement>;
    style?: CSSProperties;
};

function ShopsysButton(props: InferProps<typeof ShopsysButton.propTypes> & nativeProps): ReactElement {
    let Component = StyledShopsysButton;

    if (props.variant === 'primary') {
        Component = StyledShopsysButtonPrimary;
    } else if (props.variant === 'secondary') {
        Component = StyledShopsysButtonSecondary;
    }

    return <Component {...props}>{props.children}</Component>;
}

ShopsysButton.defaultProps = {
    type: 'button',
    variant: 'default',
    size: 'default',
};

ShopsysButton.propTypes = {
    /**
     * Attr name value for input HTML element.
     */
    name: PropTypes.string.isRequired,
    /**
     * Type for change input type button/submit etc.
     */
    type: PropTypes.oneOf<'button' | 'submit'>(['button', 'submit']).isRequired,
    /**
     * Type for change variant of button. If you don't fill this prop then the button will be in default modification.
     */
    variant: PropTypes.oneOf<'default' | 'primary' | 'secondary'>(['default', 'primary', 'secondary']).isRequired,
    /**
     * Type for change size of button. If you don't fill this prop then the button will be in default size.
     */
    size: PropTypes.oneOf<'default' | 'small'>(['default', 'small']).isRequired,
};

/* @component */
export default ShopsysButton;
