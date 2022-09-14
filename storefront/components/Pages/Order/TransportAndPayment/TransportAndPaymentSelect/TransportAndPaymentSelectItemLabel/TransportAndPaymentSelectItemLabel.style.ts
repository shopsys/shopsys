import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const SelectItemLabelStyled = styled.div(
    ({ theme }) => css`
        width: 100%;
        align-items: center;
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;

        @media ${theme.mediaQueries.queryLg} {
            flex: 1;
            width: auto;
        }
    `,
);

export const NameWrapperStyled = styled.div(
    ({ theme }) => css`
        width: 100%;
        margin-right: 15px;
        display: flex;
        flex-direction: column;
        flex-wrap: wrap;
        font-size: 14px;

        @media ${theme.mediaQueries.queryLg} {
            flex: 1;
            width: auto;
        }
    `,
);

export const InfoStyled = styled.span(
    ({ theme }) => css`
        color: ${theme.color.greyLight};
        font-size: 14px;
    `,
);

export const DescriptionStyled = styled(InfoStyled)(
    ({ theme }) => css`
        display: none;

        @media ${theme.mediaQueries.queryNotLargeDesktop} {
            display: initial;
        }
    `,
);

export const TransportDaysUntilDeliveryStyled = styled.span(
    ({ theme }) => css`
        width: 50%;

        color: ${theme.color.inStock};
        font-size: 13px;

        @media ${theme.mediaQueries.queryLg} {
            text-align: right;
            align-self: center;
            width: 135px;
        }
    `,
);

export const PriceStyled = styled.strong(
    ({ theme }) => css`
        width: 50%;

        font-size: ${theme.fontSize.small};
        text-align: right;

        @media ${theme.mediaQueries.queryLg} {
            width: 100px;
        }
    `,
);
