import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

export const BlogCategoryStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        flex-direction: column;
        margin-bottom: 70px;

        @media ${theme.mediaQueries.queryVl} {
            flex-direction: row;
        }
    `}
`;

export const BlogCategoryListStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        flex-direction: column;
        width: 100%;
        order: 2;
        margin-bottom: 70px;

        @media ${theme.mediaQueries.queryVl} {
            flex: 1;
            order: 1;
        }
    `}
`;

export const BlogCategoryPanelStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        flex-direction: column;
        width: 100%;
        order: 1;
        margin-bottom: 30px;

        @media ${theme.mediaQueries.queryVl} {
            order: 2;
            width: 435px;
            padding-left: 50px;
        }
    `}
`;
