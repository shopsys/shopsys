import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

type HamburgerMenuProps = {
    isOpen: boolean;
};

export const HamburgerMenuStyled = styled.div<HamburgerMenuProps>`
    ${({ theme, isOpen }) => css`
        display: flex;
        align-items: center;
        padding: 0 10px;
        width: 100%;
        height: 40px;

        cursor: pointer;
        background-color: ${theme.color.orangeLight};
        border-radius: ${theme.radius.big};

        ${isOpen === true &&
        css`
            z-index: ${theme.zIndex.aboveMenu};
        `}
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
