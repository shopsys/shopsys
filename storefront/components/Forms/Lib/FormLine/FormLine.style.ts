import { css } from 'styled-components';
import { FormLineProps } from './types';
import { styled } from 'components/Theme/main';

export const FormLineStyled = styled.div<FormLineProps>`
    ${({ theme, bottomGap, Width, Xs, Sm, Md, Lg, Vl, Xl }) => css`
        ${bottomGap &&
        css`
            padding-bottom: 12px;
        `}
        ${Width !== undefined
            ? css`
                  width: ${Width};
              `
            : 'flex: 1;'}

        ${Xs !== undefined &&
        css`
            @media ${theme.mediaQueries.queryXs} {
                width: ${Xs};
                flex: none;
            }
        `}

        ${Sm !== undefined &&
        css`
            @media ${theme.mediaQueries.querySm} {
                width: ${Sm};
                flex: none;
            }
        `}

        ${Md !== undefined &&
        css`
            @media ${theme.mediaQueries.queryMd} {
                width: ${Md};
                flex: none;
            }
        `}

        ${Lg !== undefined &&
        css`
            @media ${theme.mediaQueries.queryLg} {
                width: ${Lg};
                flex: none;
            }
        `}

        ${Vl !== undefined &&
        css`
            @media ${theme.mediaQueries.queryVl} {
                width: ${Vl};
                flex: none;
            }
        `}

        ${Xl !== undefined &&
        css`
            @media ${theme.mediaQueries.queryXl} {
                width: ${Xl};
                flex: none;
            }
        `}
    `};
`;
