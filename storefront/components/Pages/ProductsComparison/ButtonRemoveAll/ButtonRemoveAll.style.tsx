import { Icon } from 'components/Basic/Icon/Icon';
import { styled } from 'components/Theme/main';
import { css } from 'styled-components';
import tinycolor from 'tinycolor2';

export const ButtonRemoveAllStyled = styled.div(
    ({ theme }) => css`
        display: none;
        align-items: center;
        padding: 6px 16px;
        transition: ${theme.transition} background-color;

        cursor: pointer;
        background-color: ${theme.color.greyVeryLight};
        border-radius: ${theme.radius.medium};

        &:hover {
            background-color: ${tinycolor(theme.color.greyVeryLight).darken(10).toString()};
        }

        @media ${theme.mediaQueries.querySm} {
            display: inline-flex;
        }

        &.displayOnMobile {
            display: inline-flex;
            margin-bottom: 20px;

            @media ${theme.mediaQueries.querySm} {
                display: none;
            }
        }
    `,
);

export const ButtonRemoveAllTextStyled = styled.span`
    margin-right: 10px;
    line-height: 20px;

    font-size: 13px;
`;

export const ButtonRemoveAllIconStyled = styled(Icon)`
    width: 10px;
    height: 10px;
`;
