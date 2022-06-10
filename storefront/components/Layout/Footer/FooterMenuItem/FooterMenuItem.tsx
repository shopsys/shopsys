import {
    FooterMenuHeadingIconStyled,
    FooterMenuHeadingStyled,
    FooterMenuItemStyled,
    FooterMenuListItemLinkStyled,
    FooterMenuListItemStyled,
    FooterMenuListStyled,
} from './FooterMenuItem.style';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { SimpleArticleFragmentApi } from 'graphql/generated';
import { useGetWindowSize } from 'hooks/ui/UseGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/UseResizeWidthEffect';
import NextLink from 'next/link';
import { FC, useCallback, useRef, useState } from 'react';
import { CSSTransition } from 'react-transition-group';

type FooterMenuItemProps = {
    title: string;
    items: SimpleArticleFragmentApi[];
};

const TEST_IDENTIFIER = 'layout-footer-footermenuitem';

const FooterMenuItem: FC<FooterMenuItemProps> = ({ items, title }) => {
    const [isContentVisible, setIsContentVisible] = useState(false);
    const [contentElementHeight, setContentElementHeight] = useState(0);
    const contentElement = useRef<HTMLUListElement>(null);
    const wrapperElement = useRef<HTMLDivElement>(null);
    const { width } = useGetWindowSize();

    const calcHeight = useCallback(() => {
        if (contentElement.current) {
            setContentElementHeight(contentElement.current.clientHeight);
        }
    }, []);

    useResizeWidthEffect(
        width,
        desktopFirstSizes.tablet,
        () => setIsContentVisible(true),
        () => setIsContentVisible(false),
        () => setIsContentVisible(false),
    );

    return (
        <FooterMenuItemStyled contentElementHeight={contentElementHeight} data-testid={TEST_IDENTIFIER}>
            <FooterMenuHeadingStyled
                type="h4"
                onClick={() => setIsContentVisible(!isContentVisible)}
                isContentVisible={isContentVisible}
            >
                {title}
                <FooterMenuHeadingIconStyled iconType="icon" icon="Arrow" />
            </FooterMenuHeadingStyled>
            <CSSTransition
                in={isContentVisible}
                timeout={300}
                classNames="footer-menu-item"
                onEnter={calcHeight}
                onExit={calcHeight}
                nodeRef={wrapperElement}
                unmountOnExit
            >
                <div ref={wrapperElement}>
                    <FooterMenuListStyled ref={contentElement}>
                        {items.map((item) => (
                            <FooterMenuListItemStyled key={item.uuid}>
                                <NextLink href={item.slug} passHref>
                                    <FooterMenuListItemLinkStyled
                                        target={item.external ? '_blank' : undefined}
                                        rel={item.external ? 'nofollow noreferrer noopener' : undefined}
                                    >
                                        {item.name}
                                    </FooterMenuListItemLinkStyled>
                                </NextLink>
                            </FooterMenuListItemStyled>
                        ))}
                    </FooterMenuListStyled>
                </div>
            </CSSTransition>
        </FooterMenuItemStyled>
    );
};

export default FooterMenuItem;
