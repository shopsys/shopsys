import { ButtonHTMLAttributes, FC } from 'react';
import { ButtonPrimaryStyled, ButtonSecondaryStyled, ButtonStyled } from './Button.style';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<
    ButtonHTMLAttributes<HTMLButtonElement>,
    'type',
    'onClick' | 'style' | 'name'
>;

type ButtonProps = NativeProps & {
    /**
     * A prop to define the border radius of the button.
     */
    borderRadius?: 'big';
    /**
     * A prop to define the variant of the button. If you don't fill this prop, the button will be in default modification.
     */
    variant?: 'primary' | 'secondary';
    /**
     * A prop to define the size of the button.
     */
    size?: 'small';
};

/**
 * Global component for Buttons.
 * We have a special nametag for every modification of button which correlates to the styled components.
 * This method is used because we want to take advantage of the benefits of critical CSS and for that, it is necessary to have a styled-component element for each modification.
 * We can also combine variants and sizes.
 */
const Button: FC<ButtonProps> = (props) => {
    let Component = ButtonStyled;

    if (props.variant === 'primary') {
        Component = ButtonPrimaryStyled;
    } else if (props.variant === 'secondary') {
        Component = ButtonSecondaryStyled;
    }

    return <Component {...props}>{props.children}</Component>;
};

/* @component */
export default Button;
