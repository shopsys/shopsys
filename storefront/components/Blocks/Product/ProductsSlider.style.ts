import { styled } from 'components/Theme/main';
import { css } from 'styled-components';
import tinycolor from 'tinycolor2';

export const ProductSliderWrapperStyled = styled.div`
    position: relative;
`;

export const ProductSliderStyled = styled.div`
    position: relative;
    margin: 0 -10px;
    display: flex;
    overflow: hidden;
`;

export const ProductSliderControlsStyled = styled.div`
    ${({ theme }) => css`
        align-items: center;
        justify-content: center;
        position: absolute;
        top: -42px;
        right: 0;
        display: flex;

        button {
            width: 32px;
            height: 32px;
            margin-left: 5px;

            color: ${theme.color.creamWhite};
            outline: none;
            border: none;
            background-color: ${theme.color.greyDark};
            border-radius: ${theme.radius.small};
            transition: ${theme.transition};
            cursor: pointer;

            &:hover {
                background-color: ${tinycolor(theme.color.grey).darken(10).toString()};
            }
        }

        @media ${theme.mediaQueries.queryTablet} {
            display: none;
        }
    `}
`;
