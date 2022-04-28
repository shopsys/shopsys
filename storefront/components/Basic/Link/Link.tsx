import { AnchorHTMLAttributes, FC, ImgHTMLAttributes } from 'react';
import { ButtonStyled, LinkStyled } from './Link.style';
import { ButtonDefaultPropType } from 'components/Forms/Button/propTypes';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import NextLink from 'next/link';

type NativePropsAnchor = ExtractNativePropsFromDefault<
    AnchorHTMLAttributes<HTMLAnchorElement>,
    'href',
    'rel' | 'target'
>;

type NativePropsImage = ExtractNativePropsFromDefault<ImgHTMLAttributes<HTMLImageElement>, never, 'alt'>;

type LinkProps = NativePropsAnchor &
    NativePropsImage &
    ButtonDefaultPropType & {
        linkType?: 'external';
        isButton?: boolean;
    };

/**
 * Link element, which either uses wrapping Next.js Link element
 * or bare anchor tag depending on the "linkType" prop
 * or a button link when the "isButton" prop is true
 */
const Link: FC<LinkProps> = (props) => {
    const testIdentifier =
        'basic-link' +
        (props.linkType !== undefined ? '-' + props.linkType : '') +
        (props.isButton === true ? '-button' : '');

    if (props.linkType === 'external') {
        return (
            <LinkStyled {...props} data-testid={testIdentifier}>
                {props.children}
            </LinkStyled>
        );
    }

    if (props.isButton === true) {
        return (
            <NextLink {...props}>
                <ButtonStyled {...props} data-testid={testIdentifier}>
                    {props.children}
                </ButtonStyled>
            </NextLink>
        );
    }

    return (
        <NextLink {...props} passHref>
            <LinkStyled data-testid={testIdentifier}>{props.children}</LinkStyled>
        </NextLink>
    );
};

/* @component */
export default Link;
