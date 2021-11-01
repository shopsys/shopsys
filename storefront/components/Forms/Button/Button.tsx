import { ButtonAsLinkStyled, ButtonPrimaryStyled, ButtonSecondaryStyled, ButtonStyled } from './Button.style';
import { ButtonHTMLAttributes, FC } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { useFormContext } from 'react-hook-form';

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
    variant?: 'primary' | 'secondary' | 'asLink';
    /**
     * A prop to define the size of the button.
     */
    size?: 'small';
    /**
     * A prop to check if button is disabled.
     */
    isDisabled?: boolean;
};

/**
 * Global component for Buttons.
 * We have a special nametag for every modification of button which correlates to the styled components.
 * This method is used because we want to take advantage of the benefits of critical CSS and for that, it is necessary to have a styled-component element for each modification.
 * We can also combine variants and sizes.
 */
const Button: FC<ButtonProps> = (props) => {
    const formProviderMethods = useFormContext();
    let Component = ButtonStyled;

    if (props.variant === 'primary') {
        Component = ButtonPrimaryStyled;
    } else if (props.variant === 'secondary') {
        Component = ButtonSecondaryStyled;
    } else if (props.variant === 'asLink') {
        Component = ButtonAsLinkStyled;
    }

    return (
        <Component
            {...props}
            isDisabled={props.isDisabled || (props.type === 'submit' && formProviderMethods?.formState.isSubmitting)}
        >
            {props.children}
        </Component>
    );
};

/* @component */
export default Button;
