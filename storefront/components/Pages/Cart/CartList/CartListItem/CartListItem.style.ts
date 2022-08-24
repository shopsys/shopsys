import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

const localVariables = {
    imageCellWidth: '93px',
} as const;

export const ItemStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        position: relative;
        padding: 20px 0;

        border-bottom: 1px solid ${theme.color.greyLighter};

        @media ${theme.mediaQueries.queryTablet} {
            padding: 11px;
        }
    `}
`;

export const ImageCellStyled = styled.div`
    ${({ theme }) => css`
        width: ${localVariables.imageCellWidth};
        align-items: center;
        display: flex;
        padding-right: 15px;

        @media ${theme.mediaQueries.queryNotLargeDesktop} {
            margin-bottom: 26px;
        }
    `}
`;

export const InfoCellStyled = styled.div`
    ${({ theme }) => css`
        text-align: center;
        width: calc(100% - ${localVariables.imageCellWidth});
        align-items: center;
        display: flex;
        padding-right: 15px;

        font-size: 13px;
        font-weight: 700;

        @media ${theme.mediaQueries.queryNotLargeDesktop} {
            align-items: flex-start;
            flex-direction: column;
            margin-bottom: 20px;
            padding-right: 30px;
        }

        @media ${theme.mediaQueries.queryVl} {
            flex: 1;
        }
    `}
`;

export const SpinboxCellStyled = styled.div`
    ${({ theme }) => css`
        padding-right: 15px;
        width: 150px;
        align-items: center;
        display: flex;

        @media ${theme.mediaQueries.queryNotLargeDesktop} {
            padding-right: 0;
            width: 120px;
        }
    `}
`;

export const ItemPriceCellStyled = styled.div`
    ${({ theme }) => css`
        margin-left: 0;
        padding-right: 15px;
        align-items: center;
        display: flex;

        @media ${theme.mediaQueries.queryNotLargeDesktop} {
            margin-left: auto;
            padding-right: 0;
        }

        @media ${theme.mediaQueries.queryVl} {
            width: 136px;
        }
    `}
`;

export const ItemPriceStyled = styled.span`
    ${({ theme }) => css`
        font-size: ${theme.fontSize.small};
    `}
`;

export const TotalPriceCellStyled = styled.div`
    ${({ theme }) => css`
        margin-left: 0;
        padding-right: 15px;
        justify-content: flex-end;
        width: 136px;
        align-items: center;
        display: flex;

        @media ${theme.mediaQueries.queryNotLargeDesktop} {
            justify-content: flex-end;
            margin-left: auto;
            padding-right: 0;
        }
    `}
`;

export const TotalPriceStyled = styled.span`
    ${({ theme }) => css`
        color: ${theme.color.primary};
    `}
`;

export const RemoveButtonCellStyled = styled.div`
    ${({ theme }) => css`
        position: static;
        align-items: center;
        display: flex;

        @media ${theme.mediaQueries.queryNotLargeDesktop} {
            right: 0;
            top: 15px;
            padding-right: 0;
            position: absolute;
        }

        @media ${theme.mediaQueries.queryTablet} {
            right: 11px;
            top: 11px;
        }
    `}
`;

export const ImageWrapperStyled = styled.a`
    position: relative;
    width: 100%;
    height: 100%;
`;
