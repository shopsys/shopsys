import { ButtonAsLinkStyled, ButtonStyled } from './Button.style';
import { ButtonDefaultPropType } from './propTypes';
import { ButtonHTMLAttributes, FC } from 'react';
import { useFormContext } from 'react-hook-form';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

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

const Button: FC<ButtonProps> = (props) => {
    const formProviderMethods = useFormContext();
    let Component = ButtonStyled;

    if (props.isLink === true) {
        Component = ButtonAsLinkStyled;
    }

    return (
        <>
            <Component
                {...props}
                hasDisabledLook={props.hasDisabledLook}
                isDisabled={
                    // formProviderMethods may be null probably when it is not used in FormProvider context - see https://github.com/react-hook-form/react-hook-form/discussions/3894
                    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
                    props.isDisabled || (props.type === 'submit' && formProviderMethods?.formState.isSubmitting)
                }
            >
                {props.children}
            </Component>
        </>
    );
};

export default Button;
