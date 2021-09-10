import { css } from 'styled-components';
import Icon from '../../../../Basic/Icon';
import { styled } from '../../../../Theme/main';

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
    `}
`;

export const DropdownSlideLeftIconStyled = styled(Icon)`
    transform: rotate(90deg);
    margin-right: 9px;
`;
