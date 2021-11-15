import { AnchorHTMLAttributes, FC, ImgHTMLAttributes } from 'react';
import { ButtonStyled, LinkStyled } from './Link.style';
import { ButtonDefaultProps } from 'components/Forms/Button/types';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import NextLink from 'next/link';

type NativePropsAnchor = ExtractNativePropsFromDefault<
    AnchorHTMLAttributes<HTMLAnchorElement>,
    'children' | 'href',
    'rel' | 'target'
>;

type NativePropsImage = ExtractNativePropsFromDefault<ImgHTMLAttributes<HTMLImageElement>, never, 'alt'>;

type LinkProps = NativePropsAnchor &
    NativePropsImage &
    ButtonDefaultProps & {
        linkType?: 'external';
        isButton?: boolean;
    };

/**
 * Link element, which either uses wrapping Next.js Link element
 * or bare anchor tag depending on the "linkType" prop
 * or a button link when the "isButton" prop is true
 */
const Link: FC<LinkProps> = (props) => {
    if (props.linkType === 'external') {
        return <LinkStyled {...props}>{props.children}</LinkStyled>;
    }

    if (props.isButton === true) {
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
