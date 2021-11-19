import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

export const SimpleNavigationStyled = styled.ul`
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    margin: 0 0 -12px -24px;
    padding: 0;
`;

export const ListItemStyled = styled.li`
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
