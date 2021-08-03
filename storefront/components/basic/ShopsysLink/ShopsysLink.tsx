import { AnchorHTMLAttributes, ReactElement } from 'react';
import PropTypes, { InferProps } from 'prop-types';
import { StyledShopsysLink, StyledShopsysLinkIcon } from './ShopsysLink.style';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import Link from 'next/link';

type NativeProps = ExtractNativePropsFromDefault<
    AnchorHTMLAttributes<HTMLAnchorElement>,
    'children' | 'href',
    'rel' | 'target'
>;

/**
 * Link element, which either uses wrapping Next.js Link element
 * or bare anchor tag depending on the "linkType" prop
 */
function ShopsysLink(props: InferProps<typeof ShopsysLink.propTypes> & NativeProps): ReactElement {
    if (props.linkType === 'external') {
        return (
            <StyledShopsysLink {...props}>
                {props.icon && <StyledShopsysLinkIcon src={props.icon} alt="" />}
                {props.children}
            </StyledShopsysLink>
        );
    }

    return (
        <Link {...props}>
            <StyledShopsysLink>
                {props.icon && <StyledShopsysLinkIcon src={props.icon} alt="" />}
                {props.children}
            </StyledShopsysLink>
        </Link>
    );
}

ShopsysLink.defaultProps = {
    linkType: 'internal',
};

ShopsysLink.propTypes = {
    /**
     * A prop which defines if the link is internal or external
     */
    linkType: PropTypes.oneOf<'internal' | 'external'>(['internal', 'external']),
    /**
     * A prop which, if present, provides a URL for an icon
     * which then gets rendered next to the text of the link
     */
    icon: PropTypes.string,
};

/* @component */
export default ShopsysLink;
