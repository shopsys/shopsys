import styled, { css } from 'styled-components';
import { Theme } from 'theme/main';

type StyledShopsysLinkIconProps = {
    iconWidth: number;
};

export const StyledShopsysLink = styled.a`
    ${({ theme }: { theme: Theme }) => css`
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
