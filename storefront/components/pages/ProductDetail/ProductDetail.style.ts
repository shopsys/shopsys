import { css } from 'styled-components';
import { styled } from 'theme/main';

const localVariables = {
    detailInfoWidthSmall: '346px',
    detailInfoWidth: '512px',
};

export const StyledProductDetail = styled.div`
    ${({ theme }) => css`
        display: flex;
        flex-direction: column;
        flex-wrap: wrap;
        margin-bottom: 20px;

        @media ${theme.mediaQueries.queryLg} {
            flex-direction: row;
        }
    `}
`;

export const StyledProductDetailImage = styled.div`
    ${({ theme }) => css`
        @media ${theme.mediaQueries.queryLg} {
            width: calc(100% - ${localVariables.detailInfoWidthSmall});
            margin-bottom: 0;
        }

        @media ${theme.mediaQueries.queryVl} {
            width: calc(100% - ${localVariables.detailInfoWidth});
        }

        // this div is added automaticaly by simple-react-lightbox
        > div {
            position: relative;
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            justify-content: flex-start;
            width: 100%;
            margin-bottom: 20px;

            border-radius: ${theme.radius.big};
            font-size: 0;
            overflow: hidden;
        }
    `}
`;

export const StyledProductDetailInfo = styled.div`
    ${({ theme }) => css`
        width: 100%;
        margin-bottom: 16px;

        @media ${theme.mediaQueries.queryLg} {
            padding-left: 26px;
            width: ${localVariables.detailInfoWidthSmall};
            margin-bottom: 30px;
        }

        @media ${theme.mediaQueries.queryVl} {
            width: ${localVariables.detailInfoWidth};
        }
    `}
`;

export const StyledProductDetailPrefix = styled.div`
    ${({ theme }) => css`
        margin-bottom: 4px;

        color: ${theme.color.greyLight};
        font-size: ${theme.fontSize.default};
        font-weight: 400;
    `}
`;

export const StyledProductDetailHeading = styled.h1`
    ${({ theme }) => css`
        margin-bottom: 8px;

        color: ${theme.color.black};
        font-size: 24px;
        font-weight: 700;
    `}
`;

export const StyledProductDetailCode = styled.div`
    ${({ theme }) => css`
        margin-bottom: 20px;

        color: ${theme.color.greyLight};
        font-size: 13px;
    `}
`;
