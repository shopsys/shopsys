import styled, { css } from 'styled-components';
import { Theme } from 'theme/main';

export const StyledShopsysHeading1 = styled.h1`
    ${({ theme }) => css`
        font-size: 32px;
        font-weight: 700;
        line-height: 36px;
        letter-spacing: 0.4;
        color: ${theme.color.base};
    `}
`;

export const StyledShopsysHeading2 = styled.h2`
    ${({ theme }: { theme: Theme }) => css`
        font-size: 24px;
        font-weight: 700;
        line-height: 30px;
        letter-spacing: 0.3;
        color: ${theme.color.heading};
    `}
`;

export const StyledShopsysHeading3 = styled.h3`
    ${({ theme }: { theme: Theme }) => css`
        font-size: 18px;
        font-weight: 700;
        line-height: 22px;
        color: ${theme.color.heading};
    `}
`;

export const StyledShopsysHeading4 = styled.h4`
    ${({ theme }: { theme: Theme }) => css`
        font-size: 16px;
        font-weight: 700;
        line-height: 20px;
        color: ${theme.color.heading};
    `}
`;
