import { AnchorHTMLAttributes, ImgHTMLAttributes, ReactElement } from 'react';
import PropTypes, { InferProps } from 'prop-types';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import Link from 'next/link';
import { StyledShopsysLink } from './ShopsysLink.style';

type NativePropsAnchor = ExtractNativePropsFromDefault<
    AnchorHTMLAttributes<HTMLAnchorElement>,
    'children' | 'href',
    'rel' | 'target'
>;

type NativePropsImage = ExtractNativePropsFromDefault<ImgHTMLAttributes<HTMLImageElement>, never, 'alt'>;

/**
 * Link element, which either uses wrapping Next.js Link element
 * or bare anchor tag depending on the "linkType" prop
 */
function ShopsysLink(
    props: InferProps<typeof ShopsysLink.propTypes> & NativePropsAnchor & NativePropsImage,
): ReactElement {
    if (props.linkType === 'external') {
        return <StyledShopsysLink {...props}>{props.children}</StyledShopsysLink>;
    }

    return (
        <Link {...props}>
            <StyledShopsysLink>{props.children}</StyledShopsysLink>
        </Link>
    );
}

ShopsysLink.defaultProps = {
    linkType: 'internal',
    iconWidth: 16,
};

ShopsysLink.propTypes = {
    /**
     * A prop which defines if the link is internal or external
     */
    linkType: PropTypes.oneOf<'internal' | 'external'>(['internal', 'external']),
    /**
     * A prop for define width of icon, by default is setup to 16px.
     */
    iconWidth: PropTypes.number.isRequired,
};

/* @component */
export default ShopsysLink;
