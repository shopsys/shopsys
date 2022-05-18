import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

const localVariables = {
    listSliderProductsItemGap: '20px',
    listSliderProductsItemImageHeight: '160px',
    listSliderProductsItemShadow: '0 0 15px 0 rgba(0,0,0,.2);',
    listSliderProductsItemVerticalPadding: '10px',
    listSliderProductsItemTitleRows: '2',
    listSliderProductsItemTitleLineHeight: '20px',
};

export const SliderProductItemStyled = styled.div`
    ${({ theme }) => css`
        width: 250px;
        min-width: 250px;
        padding: 10px;

        color: ${theme.color.base};

        &:hover {
            color: ${theme.color.primary};
        }
    `}
`;

export const SliderProductItemInStyled = styled.div`
    ${({ theme }) => css`
        position: relative;
        display: flex;
        flex-direction: column;
        height: 100%;
        text-align: left;

        border-radius: ${theme.radius.big};

        &:hover {
            box-shadow: ${localVariables.listSliderProductsItemShadow};

            img {
                mix-blend-mode: multiply;
            }
        }
    `}
`;

export const SliderProductItemLinkStyled = styled.a`
    position: relative;
    display: flex;
    flex-direction: column;
    height: 100%;

    text-decoration: none;

    &:hover {
        text-decoration: none;
    }
`;

export const SliderProductItemImageStyled = styled.div`
    display: flex;
    align-items: center;
    justify-content: center;
    height: ${`calc(${localVariables.listSliderProductsItemImageHeight} + 25px)`};
    position: relative;
    width: 100%;
    padding: 15px ${localVariables.listSliderProductsItemVerticalPadding} 10px;

    font-size: 0;
    background: transparent;

    img {
        mix-blend-mode: multiply;
    }
`;

export const SliderProductItemFlagsStyled = styled.div`
    position: absolute;
    display: flex;
    flex-direction: column;
    top: 10px;
    left: 14px;

    font-size: 0;
`;

export const SliderProductItemInfoStyled = styled.div`
    display: block;
    flex: 1;
    padding: 0 ${localVariables.listSliderProductsItemVerticalPadding} 20px;
    margin-top: auto;

    background: transparent;
    text-decoration: none;
`;

export const SliderProductItemTitleStyled = styled.h3`
    ${({ theme }) => css`
        display: block;
        height: ${`calc(${localVariables.listSliderProductsItemTitleRows} *
            ${localVariables.listSliderProductsItemTitleLineHeight})`};
        line-height: ${localVariables.listSliderProductsItemTitleLineHeight};
        margin-bottom: 5px;
        overflow: hidden;

        color: ${theme.color.black};
        font-size: ${theme.fontSize.bigger};
        font-weight: 700;
        word-wrap: break-word;
    `}
`;
