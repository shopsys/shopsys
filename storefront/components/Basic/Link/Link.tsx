import { ButtonStyled, LinkStyled } from './Link.style';
import { ButtonDefaultPropType } from 'components/Forms/Button/propTypes';
import NextLink from 'next/link';
import { AnchorHTMLAttributes, FC } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativePropsAnchor = ExtractNativePropsFromDefault<
    AnchorHTMLAttributes<HTMLAnchorElement>,
    'href',
    'rel' | 'target'
>;

type LinkProps = NativePropsAnchor &
    ButtonDefaultPropType & {
        linkType?: 'external';
        isButton?: boolean;
    };

/**
 * Link element, which either uses wrapping Next.js Link element
 * or bare anchor tag depending on the "linkType" prop
 * or a button link when the "isButton" prop is true
 */
const Link: FC<LinkProps> = ({ linkType, isButton, children, size, variant, borderRadius, ...restProps }) => {
    const testIdentifier =
        'basic-link' + (linkType !== undefined ? '-' + linkType : '') + (isButton === true ? '-button' : '');

    if (linkType === 'external') {
        if (isButton === true) {
            <ButtonStyled
                {...restProps}
                size={size}
                variant={variant}
                borderRadius={borderRadius}
                data-testid={testIdentifier}
            >
                {children}
            </ButtonStyled>;
        }

        return (
            <LinkStyled {...restProps} data-testid={testIdentifier}>
                {children}
            </LinkStyled>
        );
    }

    if (isButton === true) {
        return (
            <NextLink {...restProps} passHref>
                <ButtonStyled size={size} variant={variant} borderRadius={borderRadius} data-testid={testIdentifier}>
                    {children}
                </ButtonStyled>
            </NextLink>
        );
    }

    return (
        <NextLink {...restProps} passHref>
            <LinkStyled data-testid={testIdentifier}>{children}</LinkStyled>
        </NextLink>
    );
};

/* @component */
export default Link;
