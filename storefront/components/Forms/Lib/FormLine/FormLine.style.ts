import { FormLinePropType } from './propTypes';
import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

type FormLineStyledProps = FormLinePropType;

export const FormLineStyled = styled.div<FormLineStyledProps>(
    ({ theme, bottomGap, width, xs, sm, md, lg, vl, xl }) => css`
        ${bottomGap &&
        css`
            padding-bottom: 12px;
        `}
        ${width !== undefined
            ? css`
                  width: ${width};
              `
            : 'flex: 1;'}

        ${xs !== undefined &&
        css`
            @media ${theme.mediaQueries.queryXs} {
                width: ${xs};
                flex: none;
            }
        `}

        ${sm !== undefined &&
        css`
            @media ${theme.mediaQueries.querySm} {
                width: ${sm};
                flex: none;
            }
        `}

        ${md !== undefined &&
        css`
            @media ${theme.mediaQueries.queryMd} {
                width: ${md};
                flex: none;
            }
        `}

        ${lg !== undefined &&
        css`
            @media ${theme.mediaQueries.queryLg} {
                width: ${lg};
                flex: none;
            }
        `}

        ${vl !== undefined &&
        css`
            @media ${theme.mediaQueries.queryVl} {
                width: ${vl};
                flex: none;
            }
        `}

        ${xl !== undefined &&
        css`
            @media ${theme.mediaQueries.queryXl} {
                width: ${xl};
                flex: none;
            }
        `}
    `,
);
