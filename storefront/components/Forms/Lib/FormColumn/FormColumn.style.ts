import { css } from 'styled-components';
import { FormColumnProps } from './types';
import { FormLineStyled } from 'components/Forms/Lib/FormLine/FormLine.style';
import { styled } from 'components/Theme/main';

const localVariables = {
    formColumnGap: '12px',
};

export const FormColumnStyled = styled.div<FormColumnProps>`
    ${({ theme, Width, Xs, Sm, Md, Lg, Vl, Xl }) => css`
        display: flex;
        flex-wrap: wrap;
        margin-left: -${localVariables.formColumnGap};

        ${Width !== undefined &&
        css`
            width: ${Width};
        `}

        ${Xs !== undefined &&
        css`
            @media ${theme.mediaQueries.queryXs} {
                width: calc(${Xs} + ${localVariables.formColumnGap});
            }
        `}

        ${Sm !== undefined &&
        css`
            @media ${theme.mediaQueries.querySm} {
                width: calc(${Sm} + ${localVariables.formColumnGap});
            }
        `}

        ${Md !== undefined &&
        css`
            @media ${theme.mediaQueries.queryMd} {
                width: calc(${Md} + ${localVariables.formColumnGap});
            }
        `}

        ${Lg !== undefined &&
        css`
            @media ${theme.mediaQueries.queryLg} {
                width: calc(${Lg} + ${localVariables.formColumnGap});
            }
        `}

        ${Vl !== undefined &&
        css`
            @media ${theme.mediaQueries.queryVl} {
                width: calc(${Vl} + ${localVariables.formColumnGap});
            }
        `}

        ${Xl !== undefined &&
        css`
            @media ${theme.mediaQueries.queryXl} {
                width: calc(${Xl} + ${localVariables.formColumnGap});
            }
        `}

        ${FormLineStyled} {
            padding-left: ${localVariables.formColumnGap};
        }
    `};
`;
