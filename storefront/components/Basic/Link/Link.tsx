import { ButtonStyled, LinkStyled } from './Link.style';
import { ButtonDefaultPropType } from 'components/Forms/Button/propTypes';
import NextLink from 'next/link';
import { AnchorHTMLAttributes, FC } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativePropsAnchor = ExtractNativePropsFromDefault<
    AnchorHTMLAttributes<HTMLAnchorElement>,
    'href',
    'rel' | 'target' | 'className'
>;

type LinkProps = NativePropsAnchor &
    ButtonDefaultPropType & {
        linkType?: 'external';
        isButton?: boolean;
    };

const getTestIdentifier = (linkType?: 'external', isButton?: boolean) =>
    'basic-link' + (linkType !== undefined ? '-' + linkType : '') + (isButton === true ? '-button' : '');

export const Link: FC<LinkProps> = ({
    linkType,
    isButton,
    children,
    size,
    variant,
    borderRadius,
    href,
    rel,
    target,
    className,
}) => {
    if (linkType === 'external') {
        if (isButton === true) {
            <ButtonStyled
                className={className}
                href={href}
                rel={rel}
                target={target}
                size={size}
                variant={variant}
                borderRadius={borderRadius}
                data-testid={getTestIdentifier(linkType, isButton)}
            >
                {children}
            </ButtonStyled>;
        }

        return (
            <LinkStyled
                className={className}
                href={href}
                rel={rel}
                target={target}
                data-testid={getTestIdentifier(linkType, isButton)}
            >
                {children}
            </LinkStyled>
        );
    }

    if (isButton === true) {
        return (
            <NextLink href={href} passHref>
                <ButtonStyled
                    className={className}
                    rel={rel}
                    target={target}
                    size={size}
                    variant={variant}
                    borderRadius={borderRadius}
                    data-testid={getTestIdentifier(linkType, isButton)}
                >
                    {children}
                </ButtonStyled>
            </NextLink>
        );
    }

    return (
        <NextLink href={href} passHref>
            <LinkStyled
                className={className}
                rel={rel}
                target={target}
                data-testid={getTestIdentifier(linkType, isButton)}
            >
                {children}
            </LinkStyled>
        </NextLink>
    );
};
