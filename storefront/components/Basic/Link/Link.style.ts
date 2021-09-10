import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

export const LinkStyled = styled.a`
    ${({ theme }) => css`
        display: inline-flex;
        align-items: center;

        color: ${theme.color.greyDark};
        text-decoration: underline;
        cursor: pointer;
        outline: none;
        background-color: transparent;

        &:hover {
            color: ${theme.color.primary};
        }

        img {
            margin-right: 15px;

            font-size: 0;
        }
    `}
`;
