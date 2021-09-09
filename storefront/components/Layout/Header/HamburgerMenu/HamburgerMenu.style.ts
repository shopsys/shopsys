import { css } from 'styled-components';
import Icon from '../../../Basic/Icon';
import { styled } from '../../../Theme/main';

export const HamburgerMenuStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        align-items: center;
        padding: 0 10px;
        width: 100%;
        height: 40px;

        cursor: pointer;
        background-color: ${theme.color.orangeLight};
        border-radius: ${theme.radius.big};
    `}
`;

export const HamburgerMenuTextStyled = styled.span`
    width: 29px;
    margin-left: 4px;

    font-size: 11px;
`;

export const HamburgerMenuImageStyled = styled.div`
    display: flex;
    align-items: center;
    justify-content: center;
    width: 16px;
`;

export const HamburgerMenuIconOpenStyled = styled(Icon)`
    width: 16px;
    height: 16px;
`;
