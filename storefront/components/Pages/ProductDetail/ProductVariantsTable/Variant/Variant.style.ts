import { css } from 'styled-components';
import { PopupStyled } from 'components/Layout/Popup/Popup.style';
import { styled } from 'components/Theme/main';

export const VariantCellStyled = styled.td`
    ${({ theme }) => css`
        vertical-align: middle;

        text-align: center;

        @media ${theme.mediaQueries.queryTablet} {
            padding-left: 50px;
            display: block;
            text-align: left;
        }

        @media ${theme.mediaQueries.queryLg} {
            padding: 5px;
            text-align: left;

            border-bottom: 1px solid ${theme.color.greyLighter};
            font-size: 12px;
        }
    `}
`;
export const VariantImageCellStyled = styled(VariantCellStyled)`
    ${({ theme }) => css`
        @media ${theme.mediaQueries.queryTablet} {
            float: left;
            padding-left: 0;
            width: 40px;
        }

        @media ${theme.mediaQueries.queryLg} {
            width: 100px;
        }
    `}
`;

export const VariantImageWrapperStyled = styled.div`
    height: 60px;
    width: 60px;
`;

export const VariantAvailabilityCellStyled = styled(VariantCellStyled)`
    cursor: pointer;
`;

export const VariantPriceCellStyled = styled(VariantCellStyled)`
    ${({ theme }) => css`
        @media ${theme.mediaQueries.queryLg} {
            text-align: right;
        }
    `}
`;

export const VariantActionCellStyled = styled(VariantCellStyled)`
    ${({ theme }) => css`
        @media ${theme.mediaQueries.queryTablet} {
            clear: both;
            padding-left: 0;
        }

        @media ${theme.mediaQueries.queryLg} {
            width: 240px;
            text-align: right;
        }
    `}
`;

export const VariantActionStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        justify-content: space-around;

        @media ${theme.mediaQueries.queryTablet} {
            justify-content: space-between;
        }
    `}
`;

export const AvailabilityPopupStyled = styled(PopupStyled)`
    ${({ theme }) => css`
        @media ${theme.mediaQueries.queryNotLargeDesktop} {
            width: 80%;
        }

        @media ${theme.mediaQueries.queryTablet} {
            width: 96%;
        }
    `}
`;
