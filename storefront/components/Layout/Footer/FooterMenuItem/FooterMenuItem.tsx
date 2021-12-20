import { FC, useRef, useState } from 'react';
import {
    FooterMenuHeadingIconStyled,
    FooterMenuHeadingStyled,
    FooterMenuItemStyled,
    FooterMenuListItemLinkStyled,
    FooterMenuListItemStyled,
    FooterMenuListStyled,
} from './FooterMenuItem.style';
import { CSSTransition } from 'react-transition-group';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { useGetWindowSize } from 'hooks/ui/UseGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/UseResizeWidthEffect';

type FooterMenuItemProps = {
    key: number;
    title: string;
    items: { title: string }[];
    isContentVisible?: boolean;
    contentElementHeight?: number;
};

const FooterMenuItem: FC<FooterMenuItemProps> = (props) => {
    const testIdentifier = 'layout-footer-footermenuitem';

    const [isContentVisible, setIsContentVisible] = useState(false);
    const [contentElementHeight, setContentElementHeight] = useState(0);
    const contentElement = useRef<HTMLUListElement>(null);
    const wrapperElement = useRef<HTMLDivElement>(null);
    const { width } = useGetWindowSize();

    const calcHeight = () => {
        if (contentElement.current) {
            setContentElementHeight(contentElement.current.clientHeight);
        }
    };

    useResizeWidthEffect(
        width,
        desktopFirstSizes.tablet,
        () => setIsContentVisible(true),
        () => setIsContentVisible(false),
        () => setIsContentVisible(false),
    );

    return (
        <FooterMenuItemStyled contentElementHeight={contentElementHeight} data-testid={testIdentifier}>
            <FooterMenuHeadingStyled
                type="h4"
                onClick={() => setIsContentVisible(!isContentVisible)}
                isContentVisible={isContentVisible}
            >
                {props.title}
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
                        {props.items.map((item, index) => (
                            <FooterMenuListItemStyled key={index}>
                                <FooterMenuListItemLinkStyled href="#">{item.title}</FooterMenuListItemLinkStyled>
                            </FooterMenuListItemStyled>
                        ))}
                    </FooterMenuListStyled>
                </div>
            </CSSTransition>
        </FooterMenuItemStyled>
    );
};

export default FooterMenuItem;
