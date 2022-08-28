import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const CustomerListStyled = styled.ul`
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
`;

export const CustomerListItemStyled = styled.li(
    ({ theme }) => css`
        display: flex;
        flex-direction: row;
        align-items: center;
        margin: 0 0 10px 0;
        width: 100%;

        background-color: ${theme.color.greyVeryLight};
        border-radius: ${theme.radius.big};
        color: ${theme.color.base};
        font-size: ${theme.fontSize.bigger};
        cursor: pointer;
        transition: ${theme.transition};

        &:hover {
            background-color: ${theme.color.greyLighter};

            a {
                text-decoration: none;
                color: ${theme.color.base};
            }
        }

        a {
            width: 100%;
            height: 100%;
            padding: 20px;

            text-decoration: none;
        }

        @media ${theme.mediaQueries.queryMd} {
            width: calc(100% / 3 - 10px);
            margin: 0 10px 10px 0;
        }
    `,
);
