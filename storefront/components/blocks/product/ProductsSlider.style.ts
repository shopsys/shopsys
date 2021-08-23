import styled, { css } from 'styled-components';
import { Theme } from 'theme/main';

const localVariables = {
    listSliderProductsItemGap: '20px',
    listSliderProductsItemTopSpace: '20px',
    listSliderProductsItemBottomSpace: '125px',
};

export const ProductSliderWrapperStyled = styled.div`
    position: relative;
`;

export const ProductSliderStyled = styled.div`
    ${({ theme }: { theme: Theme }) => css`
        position: relative;
        margin: 0 -10px;
        display: flex;
        overflow: hidden;

        &:hover {
            margin-bottom: ${`-${localVariables.listSliderProductsItemBottomSpace}`};
            padding-bottom: ${localVariables.listSliderProductsItemBottomSpace};

            @media ${theme.mediaQueries.queryLg} {
                margin-bottom: ${`-${localVariables.listSliderProductsItemBottomSpace}`};
            }
        }
    `}
`;

export const ProductSliderControlsStyled = styled.div`
    ${({ theme }: { theme: Theme }) => css`
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
                background-color: ${theme.color.greyDarker};
            }
        }

        @media ${theme.mediaQueries.queryTablet} {
            display: none;
        }
    `}
`;
