import { css } from 'styled-components';
import { PopupStyled } from 'components/Layout/Popup/Popup.style';
import { styled } from 'components/Theme/main';

export const ErrorPopupStyled = styled(PopupStyled)`
    ${({ theme }) => css`
        width: 96%;

        @media ${theme.mediaQueries.queryLg} {
            width: 500px;
            max-width: 500px;
        }
    `}
`;

export const ErrorListStyled = styled.ul`
    overflow-y: auto;
    max-height: 50vh;
`;
export const ErrorListItemStyled = styled.li`
    ${({ theme }) => css`
        margin-bottom: 6px;
        padding-bottom: 6px;

        border-bottom: 1px solid ${theme.color.greyLighter};
    `}
`;

export const ErrorMessageStyled = styled.span`
    ${({ theme }) => css`
        color: ${theme.color.red};
    `}
`;
