import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

export const MainItemStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        flex-direction: column;
        width: 100%;

        @media ${theme.mediaQueries.queryLg} {
            padding-left: 43.5px;
            width: 50%;
        }

        @media ${theme.mediaQueries.queryVl} {
            margin-bottom: 11px;
            width: 100%;
        }

        @media ${theme.mediaQueries.queryXl} {
            padding-left: 87px;
        }
    `}
`;

export const MainImageStyled = styled.div`
    display: flex;
    max-width: 328px;
    width: 100%;
`;

export const MainImageLinkStyled = styled.a`
    ${({ theme }) => css`
        display: flex;
        position: relative;
        margin-bottom: 10px;
        width: 100%;

        font-size: 0;

        img {
            max-height: 179px;

            border-radius: ${theme.radius.medium};
        }
    `}
`;

export const MainContentStyled = styled.div`
    flex: 1;
`;

export const MainNameStyled = styled.a`
    ${({ theme }) => css`
        display: block;
        line-height: 22px;
        margin-bottom: 6px;

        color: ${theme.color.white};
        font-weight: 700;
        font-size: ${theme.fontSize.bigger};
        text-decoration: none;

        &:hover {
            color: ${theme.color.white};
            text-decoration: underline;
        }
    `}
`;

export const MainDescriptionStyled = styled.div`
    ${({ theme }) => css`
        line-height: 21px;

        color: ${theme.color.white};
    `}
`;
