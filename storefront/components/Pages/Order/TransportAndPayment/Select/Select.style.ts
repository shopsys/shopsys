import { css } from 'styled-components';
import { PopupStyled } from 'components/Layout/Popup/Popup.style';
import { styled } from 'components/Theme/main';

const localVariables = {
    ResetButtonHoverBackgroundColor: '#d3d4e1',
} as const;

type ListItemStyledProps = {
    isActive: boolean;
};

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

export const PersonalPickupPopupWrapperStyled = styled(PopupStyled)`
    ${({ theme }) => css`
        width: 96%;

        @media ${theme.mediaQueries.queryVl} {
            width: 900px;
            max-width: 900px;
        }
    `}
`;

export const PopupButtonWrapperStyled = styled.div`
    margin-top: 20px;
    display: flex;
    justify-content: space-between;
`;
