import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

const localVariables = {
    brandDetailImageWidth: '220px',
} as const;

export const BrandDetailStyled = styled.div(
    ({ theme }) => css`
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        width: 100%;
        margin-bottom: 20px;

        @media ${theme.mediaQueries.queryMd} {
            flex-direction: row;
        }
    `,
);

export const BrandDetailImageStyled = styled.div`
    align-self: center;
    min-width: ${localVariables.brandDetailImageWidth};
    margin-right: 20px;
`;

export const BrandDetailTextStyled = styled.div(
    ({ theme }) => css`
        align-self: flex-start;

        @media ${theme.mediaQueries.queryMd} {
            align-self: center;
        }

        section,
        p {
            color: ${theme.color.base};
            font-size: ${theme.fontSize.default};
        }
    `,
);
