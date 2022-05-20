import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const RemoveCartItemButtonStyled = styled.button`
    ${({ theme }) => css`
        align-items: center;
        display: flex;
        height: 20px;
        justify-content: center;
        transition: all ${theme.transition};
        width: 20px;
        padding: 0;

        background-color: ${theme.color.whitesmoke};
        border-radius: 50%;
        cursor: pointer;
        outline: none;
        border: none;

        svg {
            flex: 0 0 8px;
            margin: 0 auto;
        }

        &:hover {
            background-color: #e3e3ff;
        }
    `}
`;
