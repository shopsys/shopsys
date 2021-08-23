import styled, { css } from 'styled-components';
import { Theme } from 'theme/main';

const localVariables = {
    listedProductsItemGap: '20px',
    listedProductsItemImageHeight: '160px',
    listedProductsItemShadow: '0 0 15px 0 rgba(0,0,0,.2);',
    listedProductsItemVerticalPadding: '10px',
    listedProductsItemTitleRows: '2',
    listedProductsItemTitleLineHeight: '20px',
};

export const ListedProductItemStyled = styled.div`
    ${({ theme }: { theme: Theme }) => css`
        width: 250px;
        min-width: 250px;
        padding: 10px;

        color: ${theme.color.base};

        &:hover {
            color: ${theme.color.primary};
        }
    `}
`;

export const ListedProductItemInStyled = styled.a`
    ${({ theme }: { theme: Theme }) => css`
        position: relative;
        display: flex;
        flex-direction: column;
        height: 100%;

        text-decoration: none;
        text-align: left;
        border-radius: ${theme.radius.big};

        &:hover {
            text-decoration: none;
            box-shadow: ${localVariables.listedProductsItemShadow};

            img {
                mix-blend-mode: multiply;
            }
        }
    `}
`;

export const ListedProductItemImageStyled = styled.div`
    display: flex;
    align-items: center;
    justify-content: center;
    height: ${`calc(${localVariables.listedProductsItemImageHeight} + 25px)`};
    position: relative;
    width: 100%;
    padding: 15px ${localVariables.listedProductsItemVerticalPadding} 10px;

    font-size: 0;
    background: transparent;

    img {
        mix-blend-mode: multiply;
    }
`;

export const ListedProductItemInfoStyled = styled.div`
    display: block;
    flex: 1;
    padding: 0 ${localVariables.listedProductsItemVerticalPadding} 20px;
    margin-top: auto;

    background: transparent;
    text-decoration: none;
`;

export const ListedProductItemTitleStyled = styled.h3`
    ${({ theme }: { theme: Theme }) => css`
        display: block;
        height: ${`calc(${localVariables.listedProductsItemTitleRows} *
            ${localVariables.listedProductsItemTitleLineHeight})`};
        line-height: ${localVariables.listedProductsItemTitleLineHeight};
        margin-bottom: 5px;
        overflow: hidden;

        color: ${theme.color.black};
        font-size: ${theme.fontSize.bigger};
        font-weight: 700;
        word-wrap: break-word;
    `}
`;
