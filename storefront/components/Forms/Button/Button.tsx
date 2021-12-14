import { ButtonAsLinkStyled, ButtonStyled } from './Button.style';
import { ButtonHTMLAttributes, FC } from 'react';
import { ButtonDefaultPropType } from './propTypes';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { useFormContext } from 'react-hook-form';

type NativeProps = ExtractNativePropsFromDefault<
    ButtonHTMLAttributes<HTMLButtonElement>,
    'type',
    'onClick' | 'style' | 'name'
>;

type ButtonProps = NativeProps &
    ButtonDefaultPropType & {
        isDisabled?: boolean;
        hasDisabledLook?: boolean;
        isLink?: boolean;
    };

/**
 * Global component for Buttons.
 * We can combine variants, sizes and border radius.
 * The component is connected to the Link component.
 */
const Button: FC<ButtonProps> = (props) => {
    const formProviderMethods = useFormContext();
    let Component = ButtonStyled;

    if (props.isLink === true) {
        Component = ButtonAsLinkStyled;
    }

    return (
        <Component
            {...props}
            hasDisabledLook={props.hasDisabledLook}
            isDisabled={props.isDisabled || (props.type === 'submit' && formProviderMethods?.formState.isSubmitting)}
        >
            {props.children}
        </Component>
    );
};

/* @component */
export default Button;
