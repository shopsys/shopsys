import { css } from 'styled-components';
import { FormColumnProps } from './types';
import { FormLineStyled } from 'components/Forms/Lib/FormLine/FormLine.style';
import { styled } from 'components/Theme/main';

const localVariables = {
    formColumnGap: '12px',
};

export const FormColumnStyled = styled.div<FormColumnProps>`
    ${({ theme, width, xs, sm, md, lg, vl, xl }) => css`
        display: flex;
        flex-wrap: wrap;
        margin-left: -${localVariables.formColumnGap};

        ${width !== undefined &&
        css`
            width: ${width};
        `}

        ${xs !== undefined &&
        css`
            @media ${theme.mediaQueries.queryXs} {
                width: calc(${xs} + ${localVariables.formColumnGap});
            }
        `}

        ${sm !== undefined &&
        css`
            @media ${theme.mediaQueries.querySm} {
                width: calc(${sm} + ${localVariables.formColumnGap});
            }
        `}

        ${md !== undefined &&
        css`
            @media ${theme.mediaQueries.queryMd} {
                width: calc(${md} + ${localVariables.formColumnGap});
            }
        `}

        ${lg !== undefined &&
        css`
            @media ${theme.mediaQueries.queryLg} {
                width: calc(${lg} + ${localVariables.formColumnGap});
            }
        `}

        ${vl !== undefined &&
        css`
            @media ${theme.mediaQueries.queryVl} {
                width: calc(${vl} + ${localVariables.formColumnGap});
            }
        `}

        ${xl !== undefined &&
        css`
            @media ${theme.mediaQueries.queryXl} {
                width: calc(${xl} + ${localVariables.formColumnGap});
            }
        `}

        ${FormLineStyled} {
            padding-left: ${localVariables.formColumnGap};
        }
    `};
`;
