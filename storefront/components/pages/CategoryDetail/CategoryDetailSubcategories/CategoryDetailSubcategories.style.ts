import { css } from 'styled-components';
import { styled } from 'theme/main';

export const CategoryDetailSubcategoriesWrapperStyled = styled.ul`
    margin-bottom: 24px;
`;

export const CategoryDetailSubcategoriesListStyled = styled.ul`
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    margin: 0 0 -12px -24px;
    padding: 0;
`;

export const CategoryDetailSubcategoriesItemStyled = styled.li`
    ${({ theme }) => css`
        margin-bottom: 12px;
        margin-left: 0;
        min-width: auto;
        padding-left: 24px;
        text-align: left;

        @media ${theme.mediaQueries.queryTablet} {
            padding-left: 0;
            text-align: center;
        }

        @media ${theme.mediaQueries.queryLg} {
            width: 50%;
        }

        @media ${theme.mediaQueries.queryVl} {
            width: 33%;
        }

        @media ${theme.mediaQueries.queryXl} {
            width: 25%;
        }
    `}
`;
