import { styled, Theme } from '../../Theme/main';
import { css } from 'styled-components';

const baseStyleOfHeading = (theme: Theme) => {
    return css`
        color: ${theme.color.base};
        font-weight: 700;
        text-rendering: optimizelegibility;
        word-wrap: break-word;
    `;
};

export const StyledShopsysHeading1 = styled.h1`
    ${({ theme }) => css`
        margin: 0 0 10px 0;

        ${baseStyleOfHeading(theme)};
        font-size: 24px;

        @media ${theme.mediaQueries.queryLg} {
            margin-bottom: 15px;

            font-size: 32px;
        }
    `}
`;

export const StyledShopsysHeading2 = styled.h2`
    ${({ theme }) => css`
        margin: 0 0 10px 0;

        ${baseStyleOfHeading(theme)};
        font-size: 18px;

        @media ${theme.mediaQueries.queryLg} {
            font-size: 24px;
        }
    `}
`;

export const StyledShopsysHeading3 = styled.h3`
    ${({ theme }) => css`
        margin: 0 0 10px 0;

        ${baseStyleOfHeading(theme)};
        font-size: ${theme.fontSize.default};

        @media ${theme.mediaQueries.queryLg} {
            margin-bottom: 15px;

            font-size: 18px;
        }
    `}
`;

export const StyledShopsysHeading4 = styled.h4`
    ${({ theme }) => css`
        margin: 0 0 10px 0;

        ${baseStyleOfHeading(theme)};
        font-size: ${theme.fontSize.small};

        @media ${theme.mediaQueries.queryLg} {
            font-size: ${theme.fontSize.default};
        }
    `}
`;
