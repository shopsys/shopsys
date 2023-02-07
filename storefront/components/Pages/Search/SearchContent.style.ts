import { Icon } from 'components/Basic/Icon/Icon';
import { Webline } from 'components/Layout/Webline/Webline';
import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

const localVariables = {
    searchResultsPanelWidth: '304px',
} as const;

type SearchResultsBlockStyledProps = {
    areAllResultsVisible: boolean;
};

type SearchResultsPanelStyled = {
    isOpen: boolean;
};

export const SearchResultsStyled = styled.div(
    ({ theme }) =>
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
        `,
);

export const SearchResultsPanelStyled = styled.div<SearchResultsPanelStyled>(
    ({ theme, isOpen }) => css`
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        right: 40px;
        max-width: 400px;
        transform: translateX(-100%);

        ${isOpen &&
        css`
            z-index: ${theme.zIndex.aboveOverlay};
            transition: all ${theme.transition};
            transform: translateX(0);
        `};

        @media ${theme.mediaQueries.queryVl} {
            position: static;
            width: ${localVariables.searchResultsPanelWidth};
            transform: translateX(0);
            transition: none;
        }
    `,
);

export const SearchResultsContentStyled = styled.div`
    display: flex;
    flex: 1;
    flex-direction: column;
`;

export const SearchResultsBlockStyled = styled.div<SearchResultsBlockStyledProps>(
    ({ theme, areAllResultsVisible }) => css`
        @media ${theme.mediaQueries.queryLg} {
            max-height: ${areAllResultsVisible ? 'none' : '150px'};
            overflow: hidden;
        }
    `,
);

export const SearchResultsWeblineStyled = styled(Webline)`
    margin-top: 24px;
`;

export const ShowResultsButtonWrapperStyled = styled.div(
    ({ theme }) => css`
        display: flex;
        justify-content: center;
        margin: 20px 0;

        @media ${theme.mediaQueries.queryTablet} {
            display: none;
        }
    `,
);

export const SearchResultsPanelOpenerStyled = styled.div(
    ({ theme }) => css`
        position: relative;
        display: flex;
        flex-direction: row;
        justify-content: center;
        width: 100%;
        min-height: 48px;
        line-height: 27px;
        padding: 11px 32px 10px;
        margin-bottom: 10px;

        cursor: pointer;
        color: ${theme.color.white};
        font-size: ${theme.fontSize.default};
        font-weight: 700;
        background-color: ${theme.color.primary};
        border-radius: ${theme.radius.big};
        text-transform: uppercase;

        @media ${theme.mediaQueries.querySm} {
            width: 170px;
        }

        @media ${theme.mediaQueries.queryVl} {
            display: none;
        }
    `,
);

export const SearchResultsPanelIconStyled = styled(Icon)(
    ({ theme }) => css`
        height: 24px;
        width: 24px;
        margin: 2px 10px 0 0;

        color: ${theme.color.white};
        font-weight: 700;
    `,
);
