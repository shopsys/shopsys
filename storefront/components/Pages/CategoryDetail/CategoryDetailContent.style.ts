import { Icon } from 'components/Basic/Icon/Icon';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

const localVariables = {
    categoryDetailPanelWidth: '304px',
} as const;

type PanelStyledProps = {
    isOpen?: boolean;
};

export const CategoryDetailStyled = styled.div(
    ({ theme }) => css`
        display: flex;
        flex-direction: column;
        margin-bottom: 30px;

        @media ${theme.mediaQueries.queryVl} {
            flex-direction: row;
            flex-wrap: wrap;
            margin-bottom: 40px;
        }
    `,
);

export const CategoryDetailAdvertsStyled = styled(Adverts)`
    margin-bottom: 15px;
`;

export const CategoryDetailPanelStyled = styled.div<PanelStyledProps>(
    ({ theme, isOpen }) => css`
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        right: 40px;
        max-width: 400px;
        transform: translateX(-100%);

        ${isOpen &&
        css`
            z-index: ${theme.zIndex.aboveOverlay};
            transition: all ${theme.transition};
            transform: translateX(0);
        `};

        @media ${theme.mediaQueries.queryVl} {
            position: static;
            width: ${localVariables.categoryDetailPanelWidth};
            transform: translateX(0);
            transition: none;
        }
    `,
);

export const CategoryDetailContentStyled = styled.div(
    ({ theme }) => css`
        display: flex;
        flex: 1;
        flex-direction: column;

        @media ${theme.mediaQueries.queryVl} {
            padding-left: 50px;
        }
    `,
);

export const CategoryDetailDescriptionStyled = styled.div`
    font-size: 16px;
    margin-bottom: 16px;
`;

export const CategoryDetailPanelOpenerStyled = styled.div(
    ({ theme }) => css`
        position: relative;
        display: flex;
        flex-direction: row;
        justify-content: center;
        width: 100%;
        min-height: 48px;
        line-height: 27px;
        padding: 11px 32px 10px;
        margin-bottom: 10px;

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
    `,
);

export const CategoryDetailPanelIconStyled = styled(Icon)(
    ({ theme }) => css`
        height: 24px;
        width: 24px;
        margin: 2px 10px 0 0;

        color: ${theme.color.white};
        font-weight: 700;
    `,
);

export const SubcategoriesSimpleNavigationStyled = styled(SimpleNavigation)`
    margin-bottom: 24px;
`;

export const CategoryDetailContentMessageStyled = styled.div(
    ({ theme }) => css`
        padding: 50px;
        text-align: center;

        font-size: ${theme.fontSize.default};

        div:first-of-type {
            margin-bottom: 20px;
        }
    `,
);
