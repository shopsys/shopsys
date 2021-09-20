import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

const localVariables = {
    listCategoriesItemGapMobile: '10px',
    listCategoriesItemGap: '32px',
    listCategoriesItemWidthMobile: '96px',
};

export const PromotedCategoryListStyled = styled.ul`
    ${({ theme }) => css`
        display: flex;
        padding: 0 ${theme.layout.padding};
        margin: 0 -${theme.layout.padding} 0 calc(-${theme.layout.padding} - ${localVariables.listCategoriesItemGapMobile});
        overflow: hidden;

        list-style: none;

        @media ${theme.mediaQueries.queryLg} {
            flex-direction: row;
            flex-wrap: wrap;
            padding: 0;
            margin: 0 0 calc(-${localVariables.listCategoriesItemGap} / 2) -${localVariables.listCategoriesItemGap};
        }
    `}
`;

export const PromotedCategoryListItemStyled = styled.li`
    ${({ theme }) => css`
        width: ${localVariables.listCategoriesItemWidthMobile};
        min-width: ${localVariables.listCategoriesItemWidthMobile};
        margin-left: ${localVariables.listCategoriesItemGapMobile};
        text-align: center;

        @media ${theme.mediaQueries.queryLg} {
            width: 50%;
            min-width: auto;
            text-align: left;
            padding-left: ${localVariables.listCategoriesItemGap};
            margin-bottom: calc(${localVariables.listCategoriesItemGap} / 2);
            margin-left: 0;
        }

        @media ${theme.mediaQueries.queryVl} {
            width: calc(100% / 3);
        }

        @media ${theme.mediaQueries.queryXl} {
            width: 25%;
        }
    `}
`;
