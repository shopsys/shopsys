import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

export const ListStyled = styled.ul`
    ${({ theme }) => css`
        display: flex;
        flex-direction: column;
        flex-wrap: wrap;
        width: 100%;

        @media ${theme.mediaQueries.queryMd} {
            flex-direction: row;
        }
    `}
`;

export const ListItemStyled = styled.li`
    ${({ theme }) => css`
        display: flex;
        flex-direction: column;
        width: 100%;
        margin-bottom: 55px;
        padding: 0;

        @media ${theme.mediaQueries.queryMd} {
            flex-direction: row;
        }
    `}
`;

export const ListItemImageStyled = styled.div`
    ${({ theme }) => css`
        width: 100%;
        text-align: center;
        margin-bottom: 10px;

        @media ${theme.mediaQueries.queryMd} {
            width: 200px;
            margin-bottom: 0;
        }
    `}
`;

export const ListItemContentStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        flex-direction: column;
        width: 100%;

        @media ${theme.mediaQueries.queryMd} {
            padding-left: 40px;
        }
    `}
`;

export const ListItemTitleStyled = styled.a`
    ${({ theme }) => css`
        &:hover {
            text-decoration: none;

            h2 {
                color: ${theme.color.primary};
            }
        }
    `}
`;

export const ListItemContentTextStyled = styled.p`
    ${({ theme }) => css`
        line-height: 30px;
        margin-bottom: 10px;

        font-size: ${theme.fontSize.default};
    `}
`;

export const ListItemContentDateStyled = styled.p`
    ${({ theme }) => css`
        font-weight: 700;
        font-size: ${theme.fontSize.small};
    `}
`;
