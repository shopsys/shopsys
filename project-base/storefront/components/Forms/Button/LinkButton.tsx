import { ButtonBaseProps, getButtonClassName } from './Button';
import { ExtendedNextLink, ExtendedNextLinkProps } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { twMergeCustom } from 'utils/twMerge';

export type LinkButtonProps = ButtonBaseProps & ExtendedNextLinkProps;

export const LinkButton: FC<LinkButtonProps> = ({
    href,
    tid,
    className,
    size = 'medium',
    variant = 'primary',
    isDisabled,
    children,
    isWithDisabledLook,
    type,
    ...props
}) => {
    return (
        <ExtendedNextLink
            href={href}
            tid={tid}
            type={type}
            className={twMergeCustom(
                getButtonClassName(variant, size, isDisabled, isWithDisabledLook),
                'no-underline hover:no-underline',
                className,
            )}
            {...props}
        >
            {children}
        </ExtendedNextLink>
    );
};
