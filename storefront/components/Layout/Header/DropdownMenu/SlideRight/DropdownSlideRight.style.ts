import { css } from 'styled-components';
import { styled } from 'theme/main';

export const DropdownSlideRightStyled = styled.span`
    ${({ theme }) => css`
        display: flex;
        align-items: center;
        justify-content: center;
        width: 45px;

        cursor: pointer;
        color: ${theme.color.base};

        img {
            transform: rotate(-90deg);
        }
    `}
`;
