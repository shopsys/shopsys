import { css } from 'styled-components';
import Icon from 'components/Basic/Icon';
import SimpleNavigation from 'components/Blocks/SimpleNavigation';
import { styled } from 'components/Theme/main';

const localVariables = {
    categoryDetailPanelWidth: '304px',
} as const;

type PanelProps = {
    isOpen?: boolean;
};

export const CategoryDetailStyled = styled.div`
    ${({ theme }) => css`
        position: relative;
        display: flex;
        flex-direction: column;
        margin-bottom: 30px;

        @media ${theme.mediaQueries.queryVl} {
            flex-direction: row;
            flex-wrap: wrap;
            margin-bottom: 40px;
        }
    `}
`;

export const CategoryDetailPanelStyled = styled.div<PanelProps>`
    ${({ theme, isOpen }) => css`
        display: none;
        position: absolute;
        width: 100%;

        ${isOpen &&
        css`
            display: block;
            z-index: ${theme.zIndex.menu};
        `};

        @media ${theme.mediaQueries.queryVl} {
            position: static;
            display: block;
            width: ${localVariables.categoryDetailPanelWidth};
        }
    `}
`;

export const CategoryDetailContentStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        flex: 1;
        flex-direction: column;

        @media ${theme.mediaQueries.queryVl} {
            padding-left: 50px;
        }
    `}
`;

export const CategoryDetailPanelOpenerStyled = styled.div`
    ${({ theme }) => css`
        position: relative;
        display: flex;
        flex-direction: row;
        justify-content: center;
        width: 100%;
        min-height: 48px;
        line-height: 27px;
        padding: 11px 32px 10px;
        margin-bottom: 10px;
        z-index: ${theme.zIndex.aboveOverlay};

        cursor: pointer;
        color: ${theme.color.white};
        font-size: ${theme.fontSize.default};
        font-weight: 700;
        background-color: ${theme.color.primary};
        border-radius: ${theme.radius.big};
        text-transform: uppercase;

        @media ${theme.mediaQueries.querySm} {
            width: 170px;
        }

        @media ${theme.mediaQueries.queryVl} {
            display: none;
        }
    `}
`;

export const CategoryDetailPanelIconStyled = styled(Icon)`
    ${({ theme }) => css`
        height: 24px;
        width: 24px;
        margin: 2px 10px 0 0;

        color: ${theme.color.white};
        font-weight: 700;
    `}
`;

export const SubcategoriesSimpleNavigationStyled = styled(SimpleNavigation)`
    margin-bottom: 24px;
`;
