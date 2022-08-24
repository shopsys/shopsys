import { Heading } from 'components/Basic/Heading/Heading';
import { Icon } from 'components/Basic/Icon/Icon';
import { Link } from 'components/Basic/Link/Link';
import { Button } from 'components/Forms/Button/Button';
import { PopupStyled } from 'components/Layout/Popup/Popup.style';
import { styled } from 'components/Theme/main';
import { css, keyframes } from 'styled-components';

const localVariables = {
    imageSize: '100px',
    imageGap: '10px',
    buttonGap: '10px',
    checkmarkSize: '45px',
    checkmarkSizeMobile: '24px',
    checkmarkBgColor: '#8cc61c',
    fillAnimationColor: '#7ac142',
    productBorderColor: '#d7dee2',
};

const strokeAnimation = keyframes`
    100% { stroke-dashoffset: 0; }
`;

const scaleAnimation = keyframes`
    0%, 100% { transform: none; }
    50% { transform: scale3d(1.1, 1.1, 1); }
`;

const fillAnimation = keyframes`
    100% { box-shadow: inset 0 0 0 30px ${localVariables.fillAnimationColor}; }
`;

export const AddToCartPopupWrapperStyled = styled(PopupStyled)`
    ${({ theme }) => css`
        width: 600px;
        max-width: 96%;

        @media ${theme.mediaQueries.queryVl} {
            max-width: 600px;
        }
    `}
`;

export const HeadingStyled = styled(Heading)`
    ${({ theme }) => css`
        display: flex;
        align-items: center;
        width: 100%;
        margin: 15px 0 24px 0;

        color: ${theme.color.primary};
        font-size: 20px;
        text-transform: none;

        @media ${theme.mediaQueries.queryMobile} {
            margin-bottom: 16px;
        }
    `}
`;

export const Checkmark = styled(Icon)`
    ${({ theme }) => css`
        display: block;
        width: ${localVariables.checkmarkSize};
        height: ${localVariables.checkmarkSize};
        min-width: ${localVariables.checkmarkSize};
        margin-right: 15px;

        border-radius: 100%;
        stroke-width: 5;
        stroke: ${theme.color.white};
        stroke-miterlimit: 10;
        box-shadow: inset 0 0 0 ${localVariables.checkmarkBgColor};
        animation: ${fillAnimation} 0.8s ease-in-out 0.8s forwards, ${scaleAnimation} 0.6s ease-in-out 0.9s both;

        @media ${theme.mediaQueries.queryMd} {
            width: ${localVariables.checkmarkSizeMobile};
            height: ${localVariables.checkmarkSizeMobile};
            min-width: ${localVariables.checkmarkSizeMobile};
            margin-right: 10px;
        }

        .circle {
            stroke-dasharray: 166;
            stroke-dashoffset: 166;
            stroke-width: 2;
            stroke-miterlimit: 10;
            stroke: ${localVariables.checkmarkBgColor};
            fill: none;
            animation: ${strokeAnimation} 1.2s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }

        .check {
            transform-origin: 50% 50%;
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
            animation: ${strokeAnimation} 0.6s cubic-bezier(0.65, 0, 0.45, 1) 0.9s forwards;
        }
    `}
`;

export const ProductStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 12px;
        margin-bottom: 16px;

        border: 1px solid ${localVariables.productBorderColor};
        border-radius: ${theme.radius.medium};

        @media ${theme.mediaQueries.queryMd} {
            padding: 16px;
            flex-direction: row;
        }
    `}
`;

export const ImageStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        justify-content: center;
        align-items: center;
        width: ${localVariables.imageSize};
        min-width: ${localVariables.imageSize};
        max-width: ${localVariables.imageSize};
        padding: 0 ${localVariables.imageGap};

        @media ${theme.mediaQueries.queryMobile} {
            margin-bottom: 16px;
        }
    `}
`;

export const ContentStyled = styled.div`
    ${({ theme }) => css`
        width: 100%;

        @media ${theme.mediaQueries.queryMd} {
            padding-left: 16px;
        }

        @media ${theme.mediaQueries.queryLg} {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
    `}
`;

export const NameStyled = styled.div`
    ${({ theme }) => css`
        display: block;

        font-size: 15px;
        line-height: 1.5;
        font-weight: 400;
        color: ${theme.color.primary};
        word-break: break-word;
    `}
`;

export const PriceInfoStyled = styled.div`
    ${({ theme }) => css`
        margin-top: 6px;

        @media ${theme.mediaQueries.queryLg} {
            margin-top: 0;
            width: 45%;
            padding-left: 16px;
            text-align: right;
        }
    `}
`;

export const PriceStyled = styled.div`
    ${({ theme }) => css`
        display: block;

        color: ${theme.color.primary};
    `}
`;

export const ButtonsStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        flex-direction: column;
        margin: -${localVariables.buttonGap} 0 0 -${localVariables.buttonGap};
        text-align: center;

        @media ${theme.mediaQueries.queryMd} {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            padding: 0;
        }
    `}
`;

export const ButtonStyled = styled(Button)`
    ${({ theme }) => css`
        margin: ${localVariables.buttonGap} 0 0 ${localVariables.buttonGap};

        @media ${theme.mediaQueries.queryLg} {
            width: auto;
            justify-content: flex-start;
        }
    `}
`;

export const LinkStyled = styled(Link)`
    ${({ theme }) => css`
        margin: ${localVariables.buttonGap} 0 0 ${localVariables.buttonGap};

        @media ${theme.mediaQueries.queryLg} {
            width: auto;
            justify-content: flex-start;
        }
    `}
`;
