import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

type DropdownItemProps = {
    variant?: 'small';
};

export const DropdownItemStyled = styled.div<DropdownItemProps>`
    ${({ theme, variant }) => css`
        display: flex;
        ${variant === 'small' && 'margin: 0 30px'};

        border-bottom: 1px solid ${theme.color.greyLighter};

        &:last-child {
            border-bottom: 0;
        }
    `}
`;

export const DropdownItemLinkStyled = styled.a<DropdownItemProps>`
    ${({ theme, variant }) => css`
        padding: ${variant === 'small' ? '15px 0 14px 0' : '20px 45px 18px 30px'};
        flex: 1;

        font-size: ${variant === 'small' ? `${theme.fontSize.small}` : `${theme.fontSize.default}`};
        font-weight: 700;
        text-transform: ${variant === 'small' ? 'none' : 'uppercase'};
        text-decoration: none;
        color: ${theme.color.base};
    `}
`;
