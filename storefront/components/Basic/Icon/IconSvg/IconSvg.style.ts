import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

export const IconSvgStyled = styled.i`
    ${({ theme }) => css`
        display: inline-flex;
        line-height: 0;
        width: 14px;
        height: 14px;
        text-align: center;

        color: ${theme.color.base};
        font-style: normal;
        text-transform: none;

        svg,
        img {
            vertical-align: top;
            width: 100%;
            height: 100%;

            font-size: inherit;
        }
    `};
`;
