import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

const localVariables = {
    titleFontSize: '44px',
    titleLineheight: '42px',
    radius: '8px',
} as const;

export const ArticleWrapper = styled.div(
    ({ theme }) => css`
        padding: 0 20px;

        @media ${theme.mediaQueries.queryVl} {
            display: flex;
        }
    `,
);

export const ArticleTitle = styled.h1(
    ({ theme }) => css`
        display: block;
        line-height: ${localVariables.titleLineheight};
        padding: 0 20px;
        margin-bottom: 24px;

        color: ${theme.color.primary};
        font-weight: 700;
        font-size: ${localVariables.titleFontSize};
    `,
);

export const ArticleDate = styled.p(
    ({ theme }) => css`
        font-size: ${theme.fontSize.extraSmall};
        font-weight: 600;
        text-align: left;
        color: ${theme.color.grey};
        padding: 0 20px;

        margin-bottom: 8px;
    `,
);

export const ArticleTextContent = styled.div`
    display: flex;
    flex-direction: column;
    width: 100%;
    margin-bottom: 70px;
    order: 2;
`;
