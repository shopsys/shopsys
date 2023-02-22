import { Heading } from 'components/Basic/Heading/Heading';
import { Icon } from 'components/Basic/Icon/Icon';
import { styled } from 'components/Theme/main';
import { css } from 'styled-components';
import tinycolor from 'tinycolor2';

export const ContentTopStyled = styled.div(
    ({ theme }) => css`
        display: flex;
        align-items: flex-end;
        flex-wrap: wrap;
        margin-bottom: 33px;

        .isRight {
            @media ${theme.mediaQueries.queryTablet} {
                right: auto;

                &:before {
                    right: auto;
                    left: 30px;
                }
            }
        }
    `,
);

export const ContentTopHeadingStyled = styled(Heading)(
    ({ theme }) => css`
        width: 100%;

        @media ${theme.mediaQueries.queryLg} {
            flex: 1;
            margin-bottom: 0;
            width: auto;
        }
    `,
);

export const ContentProductsTableWrapperStyled = styled.div`
    position: relative;
    overflow: hidden;
    margin-bottom: 100px;
`;

export const ContentProductsTableStyled = styled.table(
    ({ theme }) => css`
        border-collapse: collapse;
        transition: ${theme.transition} margin-left;
    `,
);

export const ContentArrowsStyled = styled.div`
    display: flex;
    justify-content: flex-end;
    margin-bottom: 4px;
`;

export const ContentArrowStyled = styled.div(
    ({ theme }) => css`
        display: none;
        position: absolute;
        right: 0;
        top: 158px;
        align-items: center;
        justify-content: center;
        margin-left: 12px;
        width: 42px;
        height: 42px;
        z-index: ${theme.zIndex.above + 2};

        border-radius: ${theme.radius.medium};
        border: 1px solid ${theme.color.greyVeryLight};
        cursor: pointer;
        background-color: ${theme.color.greyVeryLight};
        transition: ${theme.transition} background-color;

        &:hover {
            background-color: ${tinycolor(theme.color.greyVeryLight).darken(10).toString()};
        }

        &:first-child {
            margin-left: 0;
            right: auto;
            left: 0;
        }

        &.isInactive {
            pointer-events: none;
            background-color: ${theme.color.white};
            border: 1px solid ${theme.color.greyLight};

            i {
                color: ${theme.color.greyLight};
            }
        }

        &.isShowed {
            display: flex;
        }

        @media ${theme.mediaQueries.queryVl} {
            position: static;
        }
    `,
);

export const ContentArrowIconStyled = styled(Icon)(
    ({ theme }) => css`
        width: 19px;
        height: 19px;

        color: ${theme.color.base};
        transform: rotate(90deg);

        &.isRightArrow {
            transform: rotate(-90deg);
        }
    `,
);
