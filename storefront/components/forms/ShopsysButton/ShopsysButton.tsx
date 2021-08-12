import { ButtonHTMLAttributes, ReactElement } from 'react';
import PropTypes, { InferProps } from 'prop-types';
import { StyledShopsysButton, StyledShopsysButtonPrimary, StyledShopsysButtonSecondary } from './ShopsysButton.style';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<
    ButtonHTMLAttributes<HTMLButtonElement>,
    'children',
    'onClick' | 'style' | 'type' | 'name'
>;

/**
 * Global component for Buttons.
 * We have a special nametag for every modification of button which correlates to the styled components.
 * This method is used because we want to take advantage of the benefits of critical CSS and for that, it is necessary to have a styled-component element for each modification.
 * We can also combine variants and sizes.
 */
function ShopsysButton(props: InferProps<typeof ShopsysButton.propTypes> & NativeProps): ReactElement {
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
    borderRadius: 'medium',
    variant: 'default',
    size: 'default',
};

ShopsysButton.propTypes = {
    /**
     * A prop to define border radius of the button. If not provided, the default value will be set.
     */
    borderRadius: PropTypes.oneOf<'medium' | 'big'>(['medium', 'big']).isRequired,
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
