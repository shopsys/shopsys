import { Icon } from 'components/Basic/Icon/Icon';
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

export const ListItemIconStyled = styled(Icon)`
    position: relative;
    top: 2px;
    margin: 0 5px 0 0;
`;

export const ListItemDeleteStyled = styled(Icon)(
    ({ theme }) => css`
        position: absolute;
        right: ${localVariables.addressListPadding};
        top: ${localVariables.addressListPadding};
        width: ${localVariables.addressListDeleteIconSize};
        height: ${localVariables.addressListDeleteIconSize};

        cursor: pointer;
        color: ${theme.color.greyLight};

        &:hover {
            color: ${theme.color.red};
        }
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

export const ButtonBackIconStyled = styled(Icon)(
    ({ theme }) => css`
        position: relative;
        top: 1px;
        transform: rotate(90deg);
        margin-right: 15px;

        color: ${theme.color.white};
    `,
);

export const ButtonNextIconStyled = styled(Icon)(
    ({ theme }) => css`
        position: relative;
        top: 1px;
        transform: rotate(-90deg);
        margin-left: 15px;
        margin-right: 0;

        color: ${theme.color.white};
    `,
);
