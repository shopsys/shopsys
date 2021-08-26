import { css } from 'styled-components';
import { styled } from '../../../../Theme/main';

export const SubMenuStyled = styled.div`
    display: flex;
    flex-direction: column;
    margin-top: 20px;
`;

export const SubMenuItemStyled = styled.a`
    ${({ theme }) => css`
        line-height: 18px;
        padding: 0 30px;
        margin-bottom: 20px;

        text-decoration: none;
        font-size: ${theme.fontSize.small};
        color: ${theme.color.base};
    `}
`;
