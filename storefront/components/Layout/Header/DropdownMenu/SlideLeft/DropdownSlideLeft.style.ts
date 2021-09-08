import { css } from 'styled-components';
import { styled } from 'theme/main';

export const DropdownSlideLeftStyled = styled.span`
    ${({ theme }) => css`
        align-items: center;
        cursor: pointer;
        display: inline-flex;
        line-height: 10px;
        position: relative;
        top: -26px;
        margin-left: 30px;
        text-transform: uppercase;

        color: ${theme.color.base};
        font-size: 10px;

        img {
            transform: rotate(90deg);
            margin-right: 9px;
        }
    `}
`;
