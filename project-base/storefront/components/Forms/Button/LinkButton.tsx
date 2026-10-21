import { ExtendedNextLink, ExtendedNextLinkProps } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { getButtonClassName } from 'components/Forms/Button/buttonUtils';
import { twMergeCustom } from 'utils/twMerge';
import { ButtonBaseProps } from './Button';

type LinkButtonProps = ButtonBaseProps & ExtendedNextLinkProps;

export const LinkButton: FC<LinkButtonProps> = ({
    href,
    tid,
    className,
    size = 'medium',
    variant = 'primary',
    hasDisabledLook,
    children,
    hasDisabledCursor,
    type,
    ...props
}) => {
    return (
        <ExtendedNextLink
            href={href}
            tid={tid}
            type={type}
            className={twMergeCustom(
                getButtonClassName(variant, size, hasDisabledLook, hasDisabledCursor),
                'no-underline hover:no-underline',
                className,
            )}
            {...props}
        >
            {children}
        </ExtendedNextLink>
    );
};
