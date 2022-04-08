import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

export const CustomerListStyled = styled.ul`
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
`;

export const CustomerListItemStyled = styled.li`
    ${({ theme }) => css`
        display: flex;
        flex-direction: row;
        align-items: center;
        padding: 20px;
        margin: 0 0 10px 0;
        width: 100%;

        background-color: ${theme.color.greyVeryLight};
        border-radius: ${theme.radius.big};
        color: ${theme.color.base};
        font-size: ${theme.fontSize.bigger};
        cursor: pointer;

        @media ${theme.mediaQueries.queryMd} {
            width: calc(100% / 3 - 10px);
            margin: 0 10px 10px 0;
        }
    `};
`;
