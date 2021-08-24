import { styled } from 'theme/main';

export const SearchStyled = styled.div`
    ${({ theme }) => `
        height: 48px;

        @media ${theme.mediaQueries.queryLg} {
            position: relative;
        }
    `}
`;

export const SearchInStyled = styled.div`
    ${({ theme }) => `
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
    ${({ theme }) => `
        position: relative;
        display: flex;
        width: 100%;

        transition: all ${theme.transition};
    `}
`;
