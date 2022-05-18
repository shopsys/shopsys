import { styled } from 'components/Theme/main';
import { css } from 'styled-components';
import tinycolor from 'tinycolor2';

const localVariables = {
    productDetailImageSliderThumbnailControlsWidth: '307px',
    productDetailImagesButtonSize: '32px',
} as const;

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

export const ProductDetailImageSliderItemStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        min-height: 330px;
        max-height: 330px;

        @media ${theme.mediaQueries.queryMobile} {
            min-height: 300px;
            max-height: 300px;
        }

        @media ${theme.mediaQueries.queryMobileXs} {
            min-height: 250px;
            max-height: 250px;
        }

        ${SliderItemImageStyled} {
            height: 100%;
            object-fit: contain;
        }
    `}
`;

export const SliderItemImageStyled = styled.img`
    width: 100%;
`;

const ImageSliderControlStyled = styled.button`
    ${({ theme }) => css`
        width: ${localVariables.productDetailImagesButtonSize};
        height: ${localVariables.productDetailImagesButtonSize};
        position: absolute;
        top: calc(50% - (${localVariables.productDetailImagesButtonSize} / 2));

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
    `}
`;

export const ImageSliderControlPreviousStyled = styled(ImageSliderControlStyled)`
    left: 0;
`;
export const ImageSliderControlNextStyled = styled(ImageSliderControlStyled)`
    right: 0;
`;
