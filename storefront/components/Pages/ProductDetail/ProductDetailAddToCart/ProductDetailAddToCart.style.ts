import { css } from 'styled-components';
import Spinbox from 'components/Forms/Spinbox';
import { styled } from 'components/Theme/main';

const localVariables = {
    richBlue: '#3848f5',
};

export const AddToCartWrapperStyled = styled.div`
    ${({ theme }) => css`
        display: block;
        margin-bottom: 16px;
        padding: 10px;

        background-color: ${theme.color.blueLight};
        border-radius: ${theme.radius.medium};
    `}
`;

export const AddToCartPriceStyled = styled.div`
    ${({ theme }) => css`
        margin-bottom: 15px;

        color: ${theme.color.primary};
        font-size: 24px;
        font-weight: bold;
    `}
`;

export const AddToCartFormStyled = styled.form`
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

export const SpinboxStyled = styled(Spinbox)`
    ${({ theme }) => css`
        display: inline-flex;

        border: 2px solid ${theme.color.border};
        border-radius: ${theme.radius.big};
        background-color: ${theme.color.white};
        overflow: hidden;
    `}
`;

export const AddtoCartSingleButtonWrapper = styled.div`
    flex: 1;
    margin-left: 10px;
`;

export const AddToCartButtonStyled = styled.button`
    ${({ theme }) => css`
        display: inline-block;
        vertical-align: middle;
        padding: 11px 32px 10px;
        width: 100%;
        line-height: ${theme.lineHeight.default};
        min-height: ${theme.btnHeight};
        text-align: center;
        transition: ${theme.transition};
        outline: 0;

        background-color: ${theme.color.primary};
        border: 0;
        color: ${theme.color.white};
        cursor: pointer;
        font-size: ${theme.fontSize.default};
        font-weight: bold;
        text-transform: uppercase;
        text-decoration: none;
        border-radius: ${theme.radius.big};
        &:hover {
            background-color: ${localVariables.richBlue};
        }
    `}
`;
