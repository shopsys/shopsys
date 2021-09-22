import { styled, Theme } from 'components/Theme/main';
import { css } from 'styled-components';

const localVariables = {
    h1FontSize: '32px',
    h1MobileFontSize: '24px',
    h2FontSize: '24px',
    h2MobileFontSize: '18px',
    h3FontSize: '18px',
    h3MobileFontSize: '16px',
    h4FontSize: '16px',
    h4MobileFontSize: '14px',
} as const;

const baseStyleOfHeading = (theme: Theme) => {
    return css`
        color: ${theme.color.base};
        font-weight: 700;
        text-rendering: optimizelegibility;
        word-wrap: break-word;
    `;
};

export const Heading1Styled = styled.h1`
    ${({ theme }) => css`
        margin: 0 0 10px 0;

        ${baseStyleOfHeading(theme)};
        font-size: ${localVariables.h1MobileFontSize};

        @media ${theme.mediaQueries.queryLg} {
            margin-bottom: 15px;

            font-size: ${localVariables.h1FontSize};
        }
    `}
`;

export const Heading2Styled = styled.h2`
    ${({ theme }) => css`
        margin: 0 0 10px 0;

        ${baseStyleOfHeading(theme)};
        font-size: ${localVariables.h2MobileFontSize};

        @media ${theme.mediaQueries.queryLg} {
            font-size: ${localVariables.h2FontSize};
        }
    `}
`;

export const Heading3Styled = styled.h3`
    ${({ theme }) => css`
        margin: 0 0 10px 0;

        ${baseStyleOfHeading(theme)};
        font-size: ${localVariables.h3MobileFontSize};

        @media ${theme.mediaQueries.queryLg} {
            margin-bottom: 15px;

            font-size: ${localVariables.h3FontSize};
        }
    `}
`;

export const Heading4Styled = styled.h4`
    ${({ theme }) => css`
        margin: 0 0 10px 0;

        ${baseStyleOfHeading(theme)};
        font-size: ${localVariables.h4MobileFontSize};

        @media ${theme.mediaQueries.queryLg} {
            font-size: ${localVariables.h4FontSize};
        }
    `}
`;
