import { Icon } from 'components/Basic/Icon/Icon';
import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const localVariables = {
    itemInWidth: '211px',
    itemInWidthSmall: '182px',
    itemPadding: '20px',
    itemPaddingSmall: '12px',
} as const;

type ItemFlagStyledProps = {
    rgbColor: string;
    textColor: string;
};

export const ItemStyled = styled.th(
    ({ theme }) => css`
        position: relative;
        padding: 0 ${localVariables.itemPaddingSmall} ${localVariables.itemPaddingSmall};
        vertical-align: top;

        box-shadow: inset -1px 0px 0px 0px ${theme.color.greyVeryLight};

        @media ${theme.mediaQueries.querySm} {
            padding: 0 ${localVariables.itemPadding} ${localVariables.itemPadding};
        }
    `,
);

export const ItemInStyled = styled.div(
    ({ theme }) => css`
        display: flex;
        flex-direction: column;
        width: ${localVariables.itemInWidthSmall};

        @media ${theme.mediaQueries.querySm} {
            width: ${localVariables.itemInWidth};
        }
    `,
);

export const ItemRemoveStyled = styled.div(
    ({ theme }) => css`
        position: absolute;
        top: 6px;
        right: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;

        cursor: pointer;
        border: 1px solid ${theme.color.greyLight};
        border-radius: ${theme.radius.medium};
        transition: ${theme.transition} background-color;
        background-color: ${theme.color.white};

        &:hover {
            background-color: ${theme.color.greyVeryLight};
        }
    `,
);

export const ItemRemoveIconStyled = styled(Icon)(
    ({ theme }) => css`
        width: 14px;
        height: 14px;

        color: ${theme.color.grey};
    `,
);

export const ItemFlagsStyled = styled.div`
    position: absolute;
    left: 0;
    top: 0;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    margin-top: 28px;
`;

export const ItemFlagStyled = styled.div<ItemFlagStyledProps>(
    ({ theme, textColor, rgbColor }) => css`
        margin-bottom: 4px;
        padding: 2px 6px;

        font-size: 12px;
        font-weight: 500;
        text-transform: uppercase;
        background-color: ${rgbColor};
        color: ${textColor};
        border-radius: 0 ${theme.radius.medium} ${theme.radius.medium} 0;

        &.isDiscount {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
    `,
);

export const ItemImageStyled = styled.div`
    display: flex;
    align-items: center;
    justify-content: center;
    height: 200px;
    margin: 10px 0 13px;
`;

export const ItemNameStyled = styled.a(
    ({ theme }) => css`
        line-height: 24px;
        height: calc(24px * 4);
        overflow: hidden;

        color: ${theme.color.primary};
        text-decoration: none;
        font-size: 16px;

        &:hover {
            text-decoration: underline;
        }
    `,
);

export const ItemCatnumStyled = styled.p`
    font-size: 11px;
`;
