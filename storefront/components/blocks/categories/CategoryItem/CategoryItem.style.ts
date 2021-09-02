import { css } from 'styled-components';
import { styled } from 'theme/main';

export const CategoryItemBlockStyled = styled.a`
    ${({ theme }) => css`
        display: flex;
        align-items: center;
        flex-direction: column;
        width: 100%;
        height: 100%;
        padding: 10px 6px;
        cursor: pointer;

        background-color: ${theme.color.greyVeryLight};
        border-radius: ${theme.radius.big};
        text-decoration: none;
        transition: ${theme.transition};

        @media ${theme.mediaQueries.queryLg} {
            flex-direction: row;
            padding: 10px 12px;
        }

        &:hover {
            background-color: ${theme.color.whitesmoke};
            text-decoration: none;
        }
    `}
`;

export const CategoryItemImageStyled = styled.div`
    ${({ theme }) => css`
        position: relative;
        width: 64px;
        height: 48px;
        margin-bottom: 6px;

        @media ${theme.mediaQueries.queryLg} {
            margin-bottom: 0;
        }

        img {
            mix-blend-mode: multiply;
            max-height: 100%;
        }
    `}
`;

export const CategoryItemNameWrapperStyled = styled.div`
    flex: 1;
    margin: 0;
    line-height: 18px;
    max-width: 100%;
`;

export const CategoryItemNameStyled = styled.span`
    ${({ theme }) => css`
        max-width: 100%;

        word-wrap: break-word;
        font-size: ${theme.fontSize.small};
        word-break: break-all;
        white-space: nowrap;
        color: ${theme.color.base};

        @media ${theme.mediaQueries.queryLg} {
            padding-left: 10px;
        }
    `}
`;

export const CategoryItemCountStyled = styled.span`
    ${({ theme }) => css`
        margin-left: 8px;

        white-space: nowrap;
        color: ${theme.color.greyLight};
        font-size: 11px;
    `}
`;
