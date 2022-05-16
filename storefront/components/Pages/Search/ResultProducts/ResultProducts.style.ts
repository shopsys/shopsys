import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

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
