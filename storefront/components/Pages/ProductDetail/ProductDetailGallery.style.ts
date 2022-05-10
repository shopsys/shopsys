import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

const localVariables = {
    productDetailGalleryRadius: '6px',
    productDetailGalleryThumbnailItemHoverBg: '#e8e8ea',
} as const;

export const ProductDetailGalleryThumbnailsStyled = styled.div`
    ${({ theme }) => css`
        display: none;

        @media ${theme.mediaQueries.queryLg} {
            position: relative;
            display: flex;
            flex-direction: column;
            width: 100px;
            padding-right: 24px;
            margin-bottom: 20px;
        }
    `}
`;

export const ProductDetailGalleryThumbnailsItemStyled = styled.div`
    ${({ theme }) => css`
        display: block;
        width: 76px;
        position: relative;
        cursor: pointer;

        @media ${theme.mediaQueries.queryLg} {
            height: 65px;
            padding: 6px;
            margin-bottom: 11px;

            background-color: ${theme.color.greyVeryLight};
            border-radius: ${localVariables.productDetailGalleryRadius};
            transition: ${theme.transition};
        }

        &:hover {
            @media ${theme.mediaQueries.queryLg} {
                background-color: ${localVariables.productDetailGalleryThumbnailItemHoverBg};
            }
        }

        img {
            max-height: 218px;

            @media ${theme.mediaQueries.queryLg} {
                position: absolute;
                left: 0;
                top: 0;
                right: 0;
                bottom: 0;
                height: auto;
                width: auto;
                margin: auto;
                max-height: 100%;
                max-width: 100%;

                mix-blend-mode: multiply;
            }
        }
    `}
`;

export const ProductDetailGalleryMainImageStyled = styled.div`
    ${({ theme }) => css`
        display: none;

        @media ${theme.mediaQueries.queryLg} {
            display: block;
            padding: 15px;
            position: relative;

            border-radius: ${theme.radius.big};
            overflow: hidden;
        }
    `}
`;

export const ProductDetailGalleryFlagsStyled = styled.div`
    position: absolute;
    display: flex;
    flex-direction: column;
    top: 10px;
    left: 14px;

    font-size: 0;
`;
