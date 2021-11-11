import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

const localVariables = {
    TitleFontSize: '45px',
    TitleLineheight: '42px',
    Radius: '8px',
};

export const ArticleWrapper = styled.div`
    ${({ theme }) => css`
        padding: 0 20px;

        @media${theme.mediaQueries.queryVl} {
            display: flex;
        }
    `}
`;

export const ArticleTitle = styled.h1`
    ${({ theme }) => css`
        font-weight: 700;
        display: block;
        line-height: ${localVariables.TitleLineheight};
        padding: 0 20px;
        margin-bottom: 25px;

        color: ${theme.color.primary};
        font-weight: 700;
        font-size: ${localVariables.TitleFontSize};
    `}
`;

export const ArticleTextContent = styled.div`
    display: flex;
    flex-direction: column;
    width: 100%;
    margin-bottom: 70px;
    order: 2;
`;
