import { PopupStyled } from 'components/Layout/Popup/Popup.style';
import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

const localVariables = {
    addressListPadding: '20px',
    addressListDeleteIconSize: '12px',
} as const;

type ListItemStyledProps = {
    isActive: boolean;
};

export const ListStyled = styled.div`
    display: flex;
    flex-direction: column;
    width: 100%;
`;

export const ListItemStyled = styled.div<ListItemStyledProps>(
    ({ theme, isActive }) => css`
        position: relative;
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        width: 100%;
        margin-bottom: 20px;
        padding: ${localVariables.addressListPadding};

        border-radius: ${theme.radius.big};
        border: 1px solid ${theme.color.grey};

        strong {
            margin-right: 5px;
        }

        ${isActive
            ? css`
                  background: ${theme.color.greyVeryLight};
                  border: 1px solid ${theme.color.primary};
              `
            : css`
                  cursor: pointer;
              `}
    `,
);

export const ListPopupStyled = styled(PopupStyled)(
    ({ theme }) => css`
        @media ${theme.mediaQueries.queryNotLargeDesktop} {
            width: 80%;
        }

        @media ${theme.mediaQueries.queryTablet} {
            width: 96%;
        }
    `,
);

export const ListPopupInStyled = styled.div`
    display: flex;
    flex-direction: column;
`;

export const ButtonWrapperStyled = styled.div`
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    justify-content: space-between;
    margin-top: 15px;
`;
