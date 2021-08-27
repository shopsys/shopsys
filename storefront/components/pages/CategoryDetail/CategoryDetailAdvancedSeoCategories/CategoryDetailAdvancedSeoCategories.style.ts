import { css } from 'styled-components';
import { styled } from 'theme/main';

export const CategoryDetailAdvancedSeoCategoriesWrapperStyled = styled.div`
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    margin-right: -24px;
`;

export const CategoryDetailAdvancedSeoCategoriesItemStyled = styled.a`
    ${({ theme }) => css`
        padding: 11px 12px;
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;

        background-color: ${theme.color.greyVeryLight};
        border-radius: ${theme.radius.big};
        color: ${theme.color.base};
        font-size: ${theme.fontSize.small};
        line-height: 18px;
        text-decoration: none;

        @media ${theme.mediaQueries.queryLg} {
            margin: 0 24px 12px 0;
        }

        &:hover,
        &:active {
            background-color: ${theme.color.whitesmoke};
            color: ${theme.color.base};
            text-decoration: none;
        }
    `}
`;
