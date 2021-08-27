import { css } from 'styled-components';
import { styled } from 'theme/main';

const localVariables = {
    productsItemGap: '20px',
    productsItemImageHeight: '160px',
    productsItemShadow: '0 0 15px 0 rgba(0,0,0,.2);',
    productsItemVerticalPadding: '10px',
    productsItemTitleRows: '2',
    productsItemTitleLineHeight: '20px',
};

export const ProductItemStyled = styled.div`
    ${({ theme }) => css`
        width: calc(100% / 2);
        padding-left: 8px;
        padding-top: 24px;

        @media ${theme.mediaQueries.queryLg} {
            width: calc(100% / 3);
        }

        @media ${theme.mediaQueries.queryVl} {
            border-top: 1px solid ${theme.color.greyLighter};
        }

        @media ${theme.mediaQueries.queryXl} {
            width: calc(100% / 3);
        }
    `}
`;

export const ProductItemInStyled = styled.a`
    ${({ theme }) => css`
        position: relative;
        display: flex;
        flex-direction: column;
        height: 100%;
        text-align: left;

        border-radius: ${theme.radius.big};
        color: ${theme.color.white};
        text-decoration: none;

        &:hover {
            text-decoration: none;

            @media ${theme.mediaQueries.queryLg} {
                z-index: ${theme.zIndex.above};

                box-shadow: 0 0 15px 0 rgba(0, 0, 0, 0.2);
                background-color: ${theme.color.white};
                border-radius: ${theme.radius.big};

                img {
                    mix-blend-mode: multiply;
                }
            }
        }
    `}
`;

export const ProductItemImageStyled = styled.div`
    display: flex;
    align-items: center;
    justify-content: center;
    height: ${`calc(${localVariables.productsItemImageHeight} + 25px)`};
    position: relative;
    width: 100%;
    padding: 15px ${localVariables.productsItemVerticalPadding} 10px;

    font-size: 0;
    background: transparent;

    img {
        mix-blend-mode: multiply;
    }
`;

export const ProductItemInfoStyled = styled.div`
    display: block;
    flex: 1;
    padding: 0 ${localVariables.productsItemVerticalPadding} 20px;
    margin-top: auto;

    background: transparent;
    text-decoration: none;
`;

export const ProductItemTitleStyled = styled.h3`
    ${({ theme }) => css`
        display: block;
        height: ${`calc(${localVariables.productsItemTitleRows} *
            ${localVariables.productsItemTitleLineHeight})`};
        line-height: ${localVariables.productsItemTitleLineHeight};
        margin-bottom: 5px;
        overflow: hidden;

        color: ${theme.color.black};
        font-size: ${theme.fontSize.bigger};
        font-weight: 700;
        word-wrap: break-word;
    `}
`;
