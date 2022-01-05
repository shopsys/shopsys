import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

type WithIsActiveStyledProps = {
    isActive: boolean;
};

const localVariables = {
    imageMaxWidth: '72px',
    imageMaxWidthMobile: '48px',
    imageMaxHeight: '48px',
};

export const AutocompleteStyled = styled.div<WithIsActiveStyledProps>`
    ${({ theme, isActive }) => css`
        @media ${theme.mediaQueries.queryLg} {
            width: 100%;
            opacity: 0;
            pointer-events: none;
            position: relative;
            transform: scaleY(0.9);
            transition: all ${theme.transition};
            transform-origin: center top;

            ${isActive &&
            css`
                transform: scaleY(1);
                opacity: 1;
                pointer-events: auto;
            `}
        }

        @media ${theme.mediaQueries.queryVl} {
            width: 576px;
        }
    `}
`;

export const AutocompleteBodyStyled = styled.div<WithIsActiveStyledProps>`
    ${({ theme, isActive }) => css`
        width: 100%;
        position: absolute;
        top: 0;
        left: 10px;
        right: 10px;
        z-index: ${theme.zIndex.aboveMenu};
        padding: 0 30px 25px;
        background: ${theme.color.creamWhite};
        box-shadow: 0 5px 10px 0 rgba(164, 167, 193, 0.34);

        ${isActive &&
        css`
            padding-top: 120px;
        `}

        @media ${theme.mediaQueries.queryLg} {
            top: 12px;
            left: 0;
            right: auto;
            padding: 32px;

            border-radius: 11px;
        }

        @media ${theme.mediaQueries.queryNotLargeDesktop} {
            opacity: 0;
            transform: scaleY(0.9);
            transition: all ${theme.transition};
            transform-origin: center top;
            pointer-events: none;

            ${isActive &&
            css`
                transform: scaleY(1);
                opacity: 1;
                pointer-events: auto;
            `}
        }
    `}
`;

export const SearchResultGroupStyled = styled.ul`
    list-style: none;
    margin: 0 0 10px;
    padding: 0;

    & > li::last-child {
        border-bottom: 0;
    }
`;

export const SearchResultGroupTitleStyled = styled.p`
    ${({ theme }) => css`
        margin: 0;

        color: ${theme.color.greyLight};
        font-size: 13px;
    `}
`;

export const SearchResultLinkStyled = styled.a`
    ${({ theme }) => css`
        width: 100%;
        display: flex;
        align-items: center;
        padding: 10px 0;

        font-size: 14px;
        font-weight: 700;
        color: ${theme.color.base};
        text-decoration: none;
    `}
`;

export const ProductsSearchResultStyled = styled.ul`
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    margin: 0 0 12px -18px;
    padding: 0;

    list-style: none;
`;

export const ProductSearchResultItemStyled = styled.li`
    ${({ theme }) => css`
        margin-bottom: 9px;
        padding-left: 18px;
        width: 100%;

        font-size: 14px;

        @media ${theme.mediaQueries.queryLg} {
            margin-bottom: 18px;
            width: 20%;
        }
    `}
`;

export const ProductSearchResultLinkStyled = styled.a`
    ${({ theme }) => css`
        align-items: center;
        display: flex;
        flex-direction: row;
        line-height: 18px;

        text-decoration: none !important;
        cursor: pointer;
        outline: none;
        color: ${theme.color.greyDark};

        @media ${theme.mediaQueries.queryLg} {
            align-items: flex-start;
            flex-direction: column;
        }
    `}
`;

export const ProductSearchResultImageWrapperStyled = styled.div`
    ${({ theme }) => css`
        margin-right: 10px;
        position: relative;
        max-width: ${localVariables.imageMaxWidth};

        img {
            max-width: ${localVariables.imageMaxWidthMobile};
            max-height: ${localVariables.imageMaxHeight};

            @media ${theme.mediaQueries.queryLg} {
                max-width: ${localVariables.imageMaxWidth};
            }
        }
    `}
`;

export const ProductSearchResultNameStyled = styled.span`
    margin-bottom: 5px;
    margin-right: 0;
    flex: 1;
`;
export const ProductSearchResultPriceStyled = styled.span`
    ${({ theme }) => css`
        color: ${theme.color.primary};
        font-weight: 700;
    `}
`;

export const NoResultsMessageWrapperStyled = styled.div`
    align-items: center;
    display: flex;
    padding: 0;
`;

export const NoResultsMessageStyled = styled.span`
    flex: 1;
    padding-left: 15px;

    font-size: 14px;
`;

export const ShowAllResultsButtonWrapper = styled.div`
    display: flex;
    justify-content: center;
`;
