import styled, { css } from 'styled-components';
import { Theme } from 'theme/main';

type StyledShopsysButtonSize = 'default' | 'small';

type StyledShopsysButtonProps = {
    size: StyledShopsysButtonSize;
};

const localVariables = {
    btnPaddingVertical: '10px',
    btnPaddingHorizontal: '32px',
    btnBackgroundColorHover: '#dea700', // darken version of theme.color.orange
    btnSmallPaddingVertical: '3px',
    btnPrimaryBackgroundColorHover: '#3b4cfc', // darken version of theme.color.primary
};

const getSize = (size: StyledShopsysButtonSize, theme: Theme) => {
    switch (size) {
        case 'default':
            return css`
                padding: ${localVariables.btnPaddingVertical} ${localVariables.btnPaddingHorizontal};
                min-height: ${theme.btnHeight};
                line-height: 27px;

                font-size: ${theme.fontSize.default};
            `;
        case 'small':
            return css`
                padding: ${localVariables.btnSmallPaddingVertical} 17px ${localVariables.btnSmallPaddingVertical};
                min-height: 30px;
                line-height: 23px;

                font-size: ${theme.fontSize.small};
            `;
    }

    throw new Error('Wrong size provided for ShopsysButton.');
};

export const StyledShopsysButton = styled.button<StyledShopsysButtonProps>`
    ${({ size, theme }: { size: StyledShopsysButtonSize; theme: Theme }) => css`
        ${getSize(size, theme)};
        width: auto;
        vertical-align: middle;
        display: inline-block;
        transition: ${theme.transition} background-color, ${theme.transition} color;
        text-align: center;

        border: 0;
        border-radius: ${theme.radius.medium};
        color: ${theme.color.white};
        background-color: ${theme.color.orange};
        cursor: pointer;
        text-decoration: none;
        font-weight: 700;
        outline: 0;
        text-transform: uppercase;

        &:hover {
            color: ${theme.color.white};
            background-color: ${localVariables.btnBackgroundColorHover};
            text-decoration: none;
        }
    `}
`;

export const StyledShopsysButtonPrimary = styled(StyledShopsysButton)`
    ${({ theme }: { theme: Theme }) => css`
        color: ${theme.color.white};
        background-color: ${theme.color.primary};

        &:hover {
            color: ${theme.color.white};
            background-color: ${localVariables.btnPrimaryBackgroundColorHover};
        }
    `}
`;

export const StyledShopsysButtonSecondary = styled(StyledShopsysButton)`
    ${({ theme }: { theme: Theme }) => css`
        color: ${theme.color.black};
        background-color: ${theme.color.orangeLight};

        &:hover {
            color: ${theme.color.black};
            background-color: ${theme.color.white};
        }
    `}
`;
