import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

export const SearchStyled = styled.div`
    ${({ theme }) => css`
        height: 48px;

        @media ${theme.mediaQueries.queryLg} {
            position: relative;
        }
    `}
`;

export const SearchInStyled = styled.div`
    ${({ theme }) => css`
        @media ${theme.mediaQueries.queryLg} {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            z-index: ${theme.zIndex.above};
        }
    `}
`;

export const SearchFormStyled = styled.div`
    ${({ theme }) => css`
        position: relative;
        display: flex;
        width: 100%;

        transition: all ${theme.transition};
    `}
`;
