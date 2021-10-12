import { css } from 'styled-components';
import Icon from 'components/Basic/Icon';
import { styled } from 'components/Theme/main';

const localVariables = {
    productFilterGroupBorderWidth: '1px',
} as const;

type ProductFilterGroupProps = {
    isOpen?: boolean;
};

export const ProductFilterGroupStyled = styled.div`
    ${({ theme }) => css`
        margin-bottom: -${localVariables.productFilterGroupBorderWidth};
        border-bottom: ${localVariables.productFilterGroupBorderWidth} solid ${theme.color.border};
    `}
`;

export const ProductFilterGroupTitleStyled = styled.div`
    ${({ theme }) => css`
        display: block;
        position: relative;
        padding: 25px 20px 25px 0;
        margin: 0;

        text-transform: uppercase;
        color: ${theme.color.black};
        font-size: ${theme.fontSize.default};
        font-weight: 700;
        cursor: pointer;
    `}
`;

export const ProductFilterGroupContentStyled = styled.div<ProductFilterGroupProps>`
    ${({ isOpen }) => css`
        flex-wrap: wrap;
        flex-direction: column;
        margin-bottom: 24px;

        ${isOpen
            ? css`
                  display: flex;
              `
            : css`
                  display: none;
              `};
    `}
`;

export const ProductFilterItemCheckboxStyled = styled.div`
    position: relative;
    display: inline-block;
    width: 100%;
`;

export const ProductFilterGroupColorStyled = styled.div`
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
`;

export const ProductFilterGroupArrowStyled = styled(Icon)<ProductFilterGroupProps>`
    ${({ theme, isOpen }) => css`
        position: absolute;
        right: 0;
        top: 50%;

        font-size: 12px;
        user-select: none;
        transform: translateY(-50%) rotate(0deg);
        transition: ${theme.transition};

        ${isOpen
            ? css`
                  transform: translateY(-50%) rotate(180deg);
                  transition: ${theme.transition};
              `
            : css`
                  transform: translateY(-50%) rotate(0deg);
                  transition: ${theme.transition};
              `};
    `}
`;

export const ProductFilterGroupCheckboxStyled = styled.div`
    margin-bottom: 10px;
`;
