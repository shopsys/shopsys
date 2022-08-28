import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const ListItemStyled = styled.li(
    ({ theme }) => css`
        display: flex;
        flex-direction: row;
        align-items: center;
        padding: 13px 0;
        width: 100%;

        border-bottom: 1px solid ${theme.color.greyLighter};
    `,
);

export const ListItemImageWrapperStyled = styled.div`
    margin-right: 15px;
    position: relative;
    width: 43px;
`;

export const ListItemDetailStyled = styled.div`
    align-items: center;
    display: flex;
    flex: 1;
    justify-content: space-between;
`;

export const ListItemTitleStyled = styled.a(
    ({ theme }) => css`
        line-height: 18px;
        padding-right: 10px;
        text-decoration: none;
        flex: 1;

        color: ${theme.color.greyDark};
        cursor: pointer;
        outline: none;
        font-size: 14px;
        font-weight: 700;
    `,
);

export const ListItemQuantityStyled = styled.span`
    padding-right: 10px;

    font-size: 14px;
`;

export const ListItemPriceStyled = styled.span(
    ({ theme }) => css`
        word-wrap: break-word;
        line-height: 1.3;
        padding-right: 18px;
        text-align: right;
        width: 118px;

        color: ${theme.color.primary};
        font-size: 14px;
        font-weight: 700;
    `,
);
