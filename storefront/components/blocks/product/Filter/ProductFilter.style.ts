import { css } from 'styled-components';
import { styled } from 'theme/main';

export const ProductFilterStyled = styled.div`
    ${({ theme }) => css`
        position: relative;
        overflow: hidden;
        padding: 0 14px;
        z-index: ${theme.zIndex.popup};

        background-color: ${theme.color.blueLight};
        border-radius: ${theme.radius.big};
    `}
`;
