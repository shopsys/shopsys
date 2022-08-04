import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const TooltipStyled = styled.div(
    ({ theme }) => css`
        padding: 6px;
        display: block;

        background: rgba(0, 0, 0, 0.75);
        border-radius: 6px;
        color: ${theme.color.white};
    `,
);

export const TooltipWrapperStyled = styled.div`
    position: relative;
`;
