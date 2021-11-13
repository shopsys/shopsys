import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

const localVariables = {
    searchResultsPanelWidth: '304px',
};

type PanelProps = {
    isOpen?: boolean;
};

export const SearchResultsStyled = styled.div`
    ${({ theme }) =>
        css`
            position: relative;
            display: flex;
            flex-direction: column;
            margin-bottom: 30px;

            @media ${theme.mediaQueries.queryVl} {
                flex-direction: row;
                flex-wrap: wrap;
                margin-bottom: 40px;
            }
        `}
`;

export const SeatchResultsPanelStyled = styled.div<PanelProps>`
    ${({ theme, isOpen }) => css`
        display: none;
        position: absolute;
        width: 100%;

        ${isOpen &&
        css`
            display: block;
            z-index: ${theme.zIndex.menu};
        `};

        @media ${theme.mediaQueries.queryVl} {
            position: static;
            display: block;
            width: ${localVariables.searchResultsPanelWidth};
        }
    `}
`;

export const SearchResultsContentStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        flex: 1;
        flex-direction: column;

        @media ${theme.mediaQueries.queryVl} {
            padding-left: 50px;
        }
    `}
`;
