import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

export const RemoveCartItemButtonStyled = styled.button`
    ${({ theme }) => css`
        align-items: center;
        display: flex;
        height: 20px;
        justify-content: center;
        transition: all ${theme.transition};
        width: 20px;

        background-color: ${theme.color.whitesmoke};
        border-radius: 50%;
        cursor: pointer;
        outline: none;
        border: none;

        &:hover {
            background-color: #e3e3ff;
        }
    `}
`;
