import { AnchorHTMLAttributes, FC, ImgHTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { LinkStyled } from './Link.style';
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
    };

/**
 * Link element, which either uses wrapping Next.js Link element
 * or bare anchor tag depending on the "linkType" prop
 */
const Link: FC<LinkProps> = (props) => {
    if (props.linkType === 'external') {
        return <LinkStyled {...props}>{props.children}</LinkStyled>;
    }

    return (
        <NextLink {...props}>
            <LinkStyled>{props.children}</LinkStyled>
        </NextLink>
    );
};

/* @component */
export default Link;
