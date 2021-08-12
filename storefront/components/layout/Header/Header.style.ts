import styled, { css } from 'styled-components';
import { Theme } from 'theme/main';

const localVariables = {
    headerItemGapSmall: '20px',
    headerItemGap: '32px',
};

export const StyledHeader = styled.div`
    ${({ theme }: { theme: Theme }) => css`
        display: flex;
        flex-wrap: wrap;
        padding: 8px 0 11px;

        @media ${theme.mediaQueries.queryLg} {
            padding: 15px 0 0;
        }

        @media ${theme.mediaQueries.queryVl} {
            padding: 23px 0 16px;
        }
    `}
`;

export const StyledHeaderLogo = styled.div`
    ${({ theme }: { theme: Theme }) => css`
        order: 1;
        flex: 1;
        display: flex;
        margin-right: auto;

        @media ${theme.mediaQueries.queryLg} {
            align-self: flex-end;
        }

        @media ${theme.mediaQueries.queryVl} {
            flex: none;
            margin-right: ${localVariables.headerItemGapSmall};
        }

        @media ${theme.mediaQueries.queryXl} {
            margin-right: ${localVariables.headerItemGap};
        }
    `}
`;

export const StyledHeaderMiddle = styled.div`
    ${({ theme }: { theme: Theme }) => css`
        width: 100%;
        order: 6;
        margin-top: 11px;

        @media ${theme.mediaQueries.queryLg} {
            order: 4;
            margin-top: 20px;
        }

        @media ${theme.mediaQueries.queryVl} {
            order: 2;
            max-width: 400px;
            flex: 1;
            margin-top: 0;
            margin-left: auto;
            margin-right: ${localVariables.headerItemGapSmall};
        }

        @media ${theme.mediaQueries.queryXl} {
            flex: none;
            margin-right: ${localVariables.headerItemGap};
        }
    `}
`;

export const StyledHeaderLinks = styled.div`
    ${({ theme }: { theme: Theme }) => css`
        order: 2;
        display: flex;

        @media ${theme.mediaQueries.queryLg} {
            margin-right: ${localVariables.headerItemGapSmall};
            margin-left: auto;
        }

        @media ${theme.mediaQueries.queryVl} {
            order: 3;
            margin-left: 0;
        }

        @media ${theme.mediaQueries.queryXl} {
            margin-right: ${localVariables.headerItemGap};
        }
    `}
`;

export const StyledHeaderCart = styled.div`
    ${({ theme }: { theme: Theme }) => css`
        order: 3;
        position: relative;
        display: flex;

        @media ${theme.mediaQueries.queryVl} {
            order: 4;
        }
    `}
`;
