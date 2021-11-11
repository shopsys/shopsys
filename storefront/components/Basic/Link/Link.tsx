import { AnchorHTMLAttributes, FC, ImgHTMLAttributes } from 'react';
import { ButtonStyled, LinkStyled } from './Link.style';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import NextLink from 'next/link';

type NativePropsAnchor = ExtractNativePropsFromDefault<
    AnchorHTMLAttributes<HTMLAnchorElement>,
    'children' | 'href',
    'rel' | 'target'
>;

type NativePropsImage = ExtractNativePropsFromDefault<ImgHTMLAttributes<HTMLImageElement>, never, 'alt'>;

type LinkProps = NativePropsAnchor &
    NativePropsImage & {
        /**
         * A prop which defines if the link is internal or external-
         */
        linkType?: 'external';
        variant?: 'default' | 'primary' | 'secondary';
        size?: 'small';
        borderRadius?: 'big';
    };

/**
 * Link element, which either uses wrapping Next.js Link element
 * or bare anchor tag depending on the "linkType" prop
 */
const Link: FC<LinkProps> = (props) => {
    if (props.linkType === 'external') {
        return <LinkStyled {...props}>{props.children}</LinkStyled>;
    }

    if (props.variant !== undefined) {
        return (
            <NextLink {...props}>
                <ButtonStyled {...props}>{props.children}</ButtonStyled>
            </NextLink>
        );
    }

    return (
        <NextLink {...props}>
            <LinkStyled>{props.children}</LinkStyled>
        </NextLink>
    );
};

/* @component */
export default Link;
