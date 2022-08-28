import { PopupStyled } from 'components/Layout/Popup/Popup.style';
import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const PickupPlacePopupWrapperStyled = styled(PopupStyled)(
    ({ theme }) => css`
        width: 96%;

        @media ${theme.mediaQueries.queryVl} {
            width: 900px;
            max-width: 900px;
        }
    `,
);

export const PopupButtonWrapperStyled = styled.div`
    margin-top: 20px;
    display: flex;
    justify-content: space-between;
`;
