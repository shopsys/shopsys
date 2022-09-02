import { ButtonAsLinkStyled, ButtonStyled } from './Button.style';
import { ButtonDefaultPropType } from './propTypes';
import { ButtonHTMLAttributes, FC } from 'react';
import { useFormContext } from 'react-hook-form';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<
    ButtonHTMLAttributes<HTMLButtonElement>,
    'type',
    'onClick' | 'style' | 'name' | 'className'
>;

type ButtonProps = NativeProps &
    ButtonDefaultPropType & {
        isDisabled?: boolean;
        hasDisabledLook?: boolean;
        isLink?: boolean;
        testIdentifier?: string;
    };

export const Button: FC<ButtonProps> = ({
    type,
    onClick,
    style,
    name,
    isDisabled,
    hasDisabledLook,
    isLink,
    size,
    variant,
    borderRadius,
    children,
    className,
    testIdentifier,
}) => {
    const formProviderMethods = useFormContext();
    let Component = ButtonStyled;

    if (isLink === true) {
        Component = ButtonAsLinkStyled;
    }

    return (
        <>
            <Component
                className={className}
                type={type}
                onClick={onClick}
                style={style}
                name={name}
                hasDisabledLook={hasDisabledLook}
                isLink={isLink}
                size={size}
                variant={variant}
                borderRadius={borderRadius}
                data-testid={testIdentifier}
                isDisabled={
                    // formProviderMethods may be null probably when it is not used in FormProvider context - see https://github.com/react-hook-form/react-hook-form/discussions/3894
                    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
                    isDisabled || (type === 'submit' && formProviderMethods?.formState.isSubmitting)
                }
            >
                {children}
            </Component>
        </>
    );
};
