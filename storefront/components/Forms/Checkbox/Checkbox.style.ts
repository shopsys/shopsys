import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const CheckboxStyled = styled.input(
    ({ theme }) => css`
        position: absolute;
        height: 1px;
        width: 1px;
        margin: -1px;
        padding: 0;
        z-index: ${theme.zIndex.hidden};
        overflow: hidden;

        clip: rect(0 0 0 0);
        border: 0;
    `,
);
