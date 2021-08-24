import { css } from 'styled-components';
import { styled } from 'theme/main';

export const CategoryItemBlockStyled = styled.div`
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
            background-color: #e8e8ea;
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

export const CategoryItemNameStyled = styled.div`
    ${({ theme }) => css`
        flex: 1;
        margin: 0;
        line-height: 18px;

        font-size: ${theme.fontSize.small};

        @media ${theme.mediaQueries.queryLg} {
            padding-left: 10px;
        }
    `}
`;

export const CategoryItemCountStyled = styled.div`
    ${({ theme }) => css`
        color: ${theme.color.baseLighter};
        font-size: 11px;
    `}
`;
