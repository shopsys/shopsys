import { css } from 'styled-components';
import { styled } from '../../Theme/main';

const localVariables = {
    productDetailImageSliderThumbnailControlsWidth: '307px',
    productDetailImagesButtonSize: '32px',
} as const;

type ProductDetailImageSliderItemProps = {
    sliderItemImageUrl: string;
    sliderItemImageHeight: number;
};

export const ProductDetailImageSliderBoxStyled = styled.div`
    ${({ theme }) => css`
        display: none;

        @media ${theme.mediaQueries.queryTablet} {
            position: relative;
            display: flex;
            flex-direction: column;
            width: 100%;
            padding-bottom: 0;
        }
    `}
`;

export const ProductDetailImageSliderStyled = styled.div`
    ${({ theme }) => css`
        width: calc(100% - ${localVariables.productDetailImageSliderThumbnailControlsWidth});

        cursor: pointer;

        @media ${theme.mediaQueries.queryTablet} {
            width: 100%;
        }
    `}
`;

export const ProductDetailImageSliderItemStyled = styled.div<ProductDetailImageSliderItemProps>`
    ${({ theme, sliderItemImageUrl, sliderItemImageHeight }) => css`
        height: ${`${sliderItemImageHeight}px`};

        background: ${`url(${sliderItemImageUrl}) center  no-repeat`};
        border-radius: ${theme.radius.big};
    `}
`;

export const ProductDetailImageSliderControlsStyled = styled.div`
    ${({ theme }) => css`
        display: none;

        @media ${theme.mediaQueries.queryTablet} {
            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            justify-content: space-between;
            top: calc(50% - (${localVariables.productDetailImagesButtonSize} / 2));
            right: 0;
        }

        button {
            width: ${localVariables.productDetailImagesButtonSize};
            height: ${localVariables.productDetailImagesButtonSize};

            color: ${theme.color.creamWhite};
            outline: none;
            border: none;
            background-color: ${theme.color.greyDark};
            border-radius: ${theme.radius.small};
            transition: ${theme.transition};
            cursor: pointer;

            &:hover {
                background-color: ${theme.color.greyDarker};
            }
        }
    `}
`;
