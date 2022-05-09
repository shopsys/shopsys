import { Tab, TabList, TabPanel, Tabs } from 'react-tabs';
import { css } from 'styled-components';
import Icon from 'components/Basic/Icon';
import { styled } from 'components/Theme/main';

type TabsContentStyledProps = {
    isActiveOnMobile?: boolean;
};

type TabsIconStyledProps = {
    isActive?: boolean;
};

const localVariables = {
    tabsHeadingPadding: '20px',
} as const;

export const TabsStyled = styled(Tabs)`
    ${({ theme }) => css`
        padding: 0;
        margin-bottom: 20px;

        @media ${theme.mediaQueries.queryVl} {
            padding: 0 ${theme.layout.padding};
        }

        @media ${theme.mediaQueries.queryXl} {
            width: ${theme.layout.width};
            margin: 0 auto 20px auto;
        }
    `}
`;

export const TabsListStyled = styled(TabList)`
    ${({ theme }) => css`
        display: none;
        flex-direction: row;
        padding: 0 10px;
        z-index: ${theme.zIndex.above};

        border-bottom: 1px solid ${theme.color.border};

        @media ${theme.mediaQueries.queryLg} {
            display: flex;
        }
    `}
`;

export const TabsListItemStyled = styled(Tab)`
    ${({ theme }) => css`
        position: relative;
        margin: 0 16px;
        padding: 3px 8px;

        text-decoration: none;
        color: ${theme.color.black};
        cursor: pointer;

        &:hover {
            text-decoration: none;
        }

        &.active {
            color: ${theme.color.primary};

            &:before {
                content: '';
                position: absolute;
                left: 0;
                right: 0;
                bottom: -1px;
                height: 2px;

                background-color: ${theme.color.primary};
            }
        }
    `}
`;

export const TabsContentStyled = styled(TabPanel)`
    ${({ theme }) => css`
        display: none;

        @media ${theme.mediaQueries.queryTablet} {
            display: flex !important;
            flex-direction: column;
        }

        &.active {
            display: flex;
            flex-direction: column;
            flex-wrap: wrap;

            @media ${theme.mediaQueries.queryLg} {
                padding-top: 48px;
            }
        }
    `}
`;

export const TabsContentInStyled = styled.div<TabsContentStyledProps>`
    ${({ theme, isActiveOnMobile }) => css`
        display: block;

        @media ${theme.mediaQueries.queryTablet} {
            display: none;
            padding: 0 ${localVariables.tabsHeadingPadding};

            ${isActiveOnMobile
                ? css`
                      display: block !important;
                  `
                : css`
                      display: none !important;
                  `};
        }
    `}
`;

export const TabsContentMobileHeadingStyled = styled.h3`
    ${({ theme }) => css`
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        padding: 14px ${localVariables.tabsHeadingPadding};
        cursor: pointer;

        font-size: ${theme.fontSize.default};
        font-weight: 700;
        background-color: ${theme.color.blueLight};
        border-radius: ${theme.radius.medium};

        @media ${theme.mediaQueries.queryLg} {
            display: none;
        }
    `}
`;

export const TabsIconStyled = styled(Icon)<TabsIconStyledProps>`
    ${({ theme, isActive }) => css`
        height: 18px;
        width: 18px;

        transform: rotate(0deg);
        transition: ${theme.transition};

        ${isActive &&
        css`
            transform: rotate(-180deg);
            transition: ${theme.transition};
        `}
    `}
`;
