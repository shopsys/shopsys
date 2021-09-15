import { css } from 'styled-components';
import Icon from '../../../../Basic/Icon';
import { styled } from '../../../../Theme/main';

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

export const DropdownSlideRightIconStyled = styled(Icon)`
    transform: rotate(-90deg);
    width: 17px;
    height: 17px;
`;
