import { css } from 'styled-components';
import Icon from 'components/Basic/Icon';
import { styled } from 'components/Theme/main';

const localVariables = {
    SortingBarBackgroundHoverColor: '#99a2ff',
    SortOrderOptionsLeftMargin: '30px',
};

type SortingBarItemLinkStyledProps = {
    isActive: boolean;
};

export const SortingBarStyled = styled.div`
    ${({ theme }) => css`
        position: relative;
        width: 100%;
        height: 48px;

        @media ${theme.mediaQueries.querySm} {
            width: 170px;
        }

        @media ${theme.mediaQueries.queryVl} {
            display: inline-block;
            width: 100%;
            height: 34px;
        }
    `}
`;

export const SortingBarOptionsWrapStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        flex-direction: column;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        z-index: ${theme.zIndex.above};
        overflow: hidden;

        background-color: ${theme.color.border};
        border-radius: ${theme.radius.big};

        @media ${theme.mediaQueries.queryVl} {
            flex-direction: row;
            justify-content: space-between;
            top: 5px;
            align-items: center;

            background-color: transparent;
            border-radius: 0;
        }
    `}
`;

export const SortingBarOptionsStyled = styled.div`
    display: flex;
    flex-direction: row;
    position: static;
    margin-left: -${localVariables.SortOrderOptionsLeftMargin};
    overflow: visible;

    border: 0;
`;

export const SortingBarItemStyled = styled.div`
    ${({ theme }) => css`
        position: relative;

        @media ${theme.mediaQueries.queryVl} {
            margin-left: ${localVariables.SortOrderOptionsLeftMargin};
        }
    `}
`;

export const SortingBarItemLinkWrapStyled = styled.span`
    ${({ theme }) => css`
        line-height: ${theme.lineHeight.default};
    `}
`;

export const SortingBarItemLinkStyled = styled.a<SortingBarItemLinkStyledProps>`
    ${({ theme, isActive }) => css`
        display: block;
        padding: 16px 9px 17px;
        text-align: center;

        color: ${theme.color.base};
        font-size: ${theme.fontSize.extraSmall};
        text-decoration: none;
        text-transform: uppercase;
        transition: ${theme.transition};

        &:hover {
            color: ${theme.color.base};
            background-color: ${localVariables.SortingBarBackgroundHoverColor};
            text-decoration: none;
        }

        @media ${theme.mediaQueries.queryVl} {
            padding: 7px 0;

            border-radius: 0;

            &:hover {
                background-color: transparent;
            }

            ${isActive &&
            css`
                ::after {
                    position: absolute;
                    left: 0;
                    bottom: 0;
                    width: 100%;
                    height: 2px;
                    content: '';

                    background-color: ${theme.color.primary};
                    cursor: auto;
                }
            `};
        }
    `}
`;

export const SortingBarSelectedSortStyled = styled.div`
    display: flex;
    justify-content: center;
    padding: 5px 0;
    align-items: center;
`;

export const SortingBarSeletedSortWrapStyled = styled.div`
    ${({ theme }) => css`
        padding-left: 8px;
        text-align: justify;

        color: ${theme.color.base};
        font-weight: 700;
    `}
`;

export const SortingBarTitleStyled = styled.div`
    ${({ theme }) => css`
        line-height: ${theme.lineHeight.default};

        font-size: ${theme.fontSize.default};
        text-transform: uppercase;
    `}
`;

export const SortingBarSelectedValue = styled.div`
    ${({ theme }) => css`
        line-height: ${theme.lineHeight.default};

        color: ${theme.color.primary};
        text-transform: none;
        font-size: ${theme.fontSize.small};
    `}
`;

export const SortingBarSortIconStyled = styled(Icon)`
    vertical-align: middle;
    width: 21px;
    height: 14px;
`;
