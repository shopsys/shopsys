import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

type ListItemStyledProps = {
    isActive: boolean;
};

const localVariables = {
    ResetButtonHoverBackgroundColor: '#d3d4e1',
} as const;

export const ListItemStyled = styled.li<ListItemStyledProps>`
    ${({ theme, isActive }) => css`
        display: flex;
        flex-wrap: wrap;
        min-width: 100%;
        order: 1;
        padding: 12px 10px;
        position: relative;

        border-bottom: 1px solid ${theme.color.greyLighter};
        cursor: pointer;

        ${isActive &&
        css`
            background-color: ${theme.color.blueLight};
            border-bottom: 0;
        `}
    `}
`;

export const PaymentListWrapper = styled.div`
    margin-top: 50px;
`;

export const ResetButtonStyled = styled.button`
    ${({ theme }) => css`
        align-items: center;
        display: flex;
        line-height: 17px;
        padding: 6px 13px;
        text-align: left;
        width: 100%;

        background-color: ${theme.color.greyLighter};
        border-radius: 0;
        color: ${theme.color.base};
        font-size: 13px;
        font-weight: 400;
        border: none;

        &:hover {
            background-color: ${localVariables.ResetButtonHoverBackgroundColor};
        }

        i {
            margin-left: 8px;
        }
    `}
`;
