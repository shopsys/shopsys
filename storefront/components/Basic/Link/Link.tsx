import { AnchorHTMLAttributes, FC, ImgHTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import NextLink from 'next/link';
import { StyledShopsysLink } from './Link.style';

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
        return <StyledShopsysLink {...props}>{props.children}</StyledShopsysLink>;
    }

    return (
        <NextLink {...props}>
            <StyledShopsysLink>{props.children}</StyledShopsysLink>
        </NextLink>
    );
};

/* @component */
export default Link;
