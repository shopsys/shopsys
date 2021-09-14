import { ButtonPrimaryStyled } from 'components/Forms/Button/Button.style';
import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

const localVariables = {
    wrapBorderRadius: '6px',
};

export const AddToCartWrapperStyled = styled.div`
    ${({ theme }) => css`
        display: block;
        margin-bottom: 16px;
        padding: 10px;

        background-color: ${theme.color.blueLight};
        border-radius: ${localVariables.wrapBorderRadius};

        @media ${theme.mediaQueries.queryLg} {
            margin-bottom: 12px;
        } ;
    `}
`;

export const AddToCartPriceStyled = styled.div`
    ${({ theme }) => css`
        margin-bottom: 15px;

        color: ${theme.color.primary};
        font-size: 24px;
        font-weight: 700;
    `}
`;

export const AddToCartFormStyled = styled.div`
    ${({ theme }) => css`
        line-height: ${theme.lineHeight.default};

        font-size: ${theme.fontSize.small};

        @media ${theme.mediaQueries.queryVl} {
            font-size: ${theme.fontSize.default};
        }
    `}
`;

export const AddToCartButtonsWrapperStyled = styled.div`
    display: flex;
    justify-content: space-between;
    align-items: center;
`;

export const AddToCartButtonWrapperStyled = styled.div`
    flex: 1;
    margin-left: 10px;
`;

export const AddToCartButtonStyled = styled(ButtonPrimaryStyled)`
    ${({ theme }) => css`
        width: 100%;

        border-radius: ${theme.radius.big};
    `}
`;
