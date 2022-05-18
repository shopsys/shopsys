import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const ListItemBlockStyled = styled.a`
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

export const ListItemImageStyled = styled.div`
    ${({ theme }) => css`
        flex: 0 0 auto;
        height: 48px;
        margin-bottom: 6px;
        position: relative;
        width: 64px;

        @media ${theme.mediaQueries.queryLg} {
            margin-bottom: 0;
        }

        img {
            mix-blend-mode: multiply;
            max-height: 100%;
        }
    `}
`;

export const ListItemNameWrapperStyled = styled.div`
    margin: 0;
    line-height: 18px;
    max-width: 100%;
`;

export const ListItemNameStyled = styled.span`
    ${({ theme }) => css`
        display: block;
        max-width: 100%;

        font-size: ${theme.fontSize.small};
        color: ${theme.color.base};

        @media ${theme.mediaQueries.queryLg} {
            padding-left: 10px;
        }
    `}
`;

export const ListItemCountStyled = styled.span`
    ${({ theme }) => css`
        margin-left: 8px;

        white-space: nowrap;
        color: ${theme.color.greyLight};
        font-size: 11px;
    `}
`;
