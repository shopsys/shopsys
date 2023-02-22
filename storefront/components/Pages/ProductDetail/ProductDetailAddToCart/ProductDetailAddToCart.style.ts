import { ButtonStyled } from 'components/Forms/Button/Button.style';
import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const AddToCartButtonStyled = styled(ButtonStyled)(
    ({ theme }) => css`
        width: 100%;

        border-radius: ${theme.radius.big};
    `,
);
