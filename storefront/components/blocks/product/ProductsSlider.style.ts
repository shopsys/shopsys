import styled, { css } from 'styled-components';

const localVariables = {
    listSliderProductsItemGap: '20px',
    listSliderProductsItemTopSpace: '20px',
    listSliderProductsItemBottomSpace: '125px',
};

export const ProductSliderStyled = styled.div`
    ${({ theme }) => css`
        position: relative;
        z-index: ${theme.zIndex.above};
        display: flex;
        overflow: hidden;
        padding: ${localVariables.listSliderProductsItemTopSpace} ${localVariables.listSliderProductsItemGap} 0
            ${localVariables.listSliderProductsItemGap};
        margin: ${-localVariables.listSliderProductsItemTopSpace} ${-localVariables.listSliderProductsItemGap} 0
            ${-localVariables.listSliderProductsItemTopSpace};

        @media ${theme.mediaQueries.queryLg} {
            margin: ${-localVariables.listSliderProductsItemTopSpace} ${-localVariables.listSliderProductsItemGap} 0
                ${-localVariables.listSliderProductsItemGap} * 2;

            &:before,
            &:after {
                content: '';
                position: absolute;
                left: 100%;
                top: 0;
                bottom: 0;
                width: 100%;
                z-index: ${theme.zIndex.above};

                background-color: ${theme.color.white};
            }

            &:after {
                left: auto;
                right: 100%;
            }
        }

        &:hover {
            margin-bottom: ${-localVariables.listSliderProductsItemBottomSpace};
            padding-bottom: ${localVariables.listSliderProductsItemBottomSpace};

            @media @query-lg {
                margin-bottom: ${-localVariables.listSliderProductsItemBottomSpace};
            }
        }
    `}
`;
