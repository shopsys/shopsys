import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

export const ResultProductsStyled = styled.div`
    ${({ theme }) => css`
        padding: 50px;
        text-align: center;

        font-size: ${theme.fontSize.default};

        div:first-of-type {
            margin-bottom: 20px;
        }
    `}
`;
