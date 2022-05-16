import Heading from 'components/Basic/Heading';
import Icon from 'components/Basic/Icon';
import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

const localVariables = {
    StoresListGap: '30px',
    infoWidth: '420px',
    mapHeight: '500px',
    mapHeightTablet: '350px',
    mapHeightMobile: '250px',
};

type ButtonBottomNameStyledProps = {
    type?: 'right';
};

type ButtonBottomIconStyledProps = {
    type?: 'right';
};

export const StoresStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        flex-direction: column;
        width: 100%;
        margin-bottom: ${localVariables.StoresListGap};

        @media ${theme.mediaQueries.queryVl} {
            flex-direction: row;
            height: ${localVariables.mapHeight};
        }
    `}
`;

export const InfoStyled = styled.div`
    ${({ theme }) => css`
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: ${localVariables.StoresListGap};
        overflow: hidden;

        border: 2px solid ${theme.color.greyLighter};
        border-radius: 0 0 ${theme.radius.big} ${theme.radius.big};

        @media ${theme.mediaQueries.queryTablet} {
            border-top: none;
        }

        @media ${theme.mediaQueries.queryVl} {
            width: ${localVariables.infoWidth};
            height: 100%;

            border-left: none;
            border-radius: 0 ${theme.radius.big} ${theme.radius.big} 0;
        }
    `}
`;

export const InfoTitleStyled = styled(Heading)`
    ${({ theme }) => css`
        margin: 0;

        @media ${theme.mediaQueries.queryLg} {
            margin-top: 25px;
        }
    `}
`;

export const ImageStyled = styled.div`
    position: relative;
`;

export const ImageTextStyled = styled.span`
    ${({ theme }) => css`
        position: absolute;
        right: 10%;
        bottom: 12px;
        width: 52px;
        height: 52px;
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: ${theme.zIndex.above};

        font-size: 25px;
        font-weight: 500;
        color: ${theme.color.white};
        background: linear-gradient(180deg, #ffcf09 0%, #ffb235 100%);
        border-radius: 50%;

        @media ${theme.mediaQueries.queryMobileXs} {
            width: 40px;
            height: 40px;

            font-size: 20px;
        }
    `}
`;

export const MapStyled = styled.div`
    ${({ theme }) => css`
        width: 100%;
        height: ${localVariables.mapHeightMobile};

        @media ${theme.mediaQueries.queryMd} {
            height: ${localVariables.mapHeightTablet};
        }

        @media ${theme.mediaQueries.queryVl} {
            width: calc(100% - ${localVariables.infoWidth});
            height: auto;
        }
    `}
`;

export const StoresList = styled.div`
    ${({ theme }) => css`
        margin-bottom: 40px;

        @media ${theme.mediaQueries.queryLg} {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-gap: ${localVariables.StoresListGap};
        }
    `}
`;

export const ButtonBottomStyled = styled.a`
    ${({ theme }) => css`
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 16px 16px 24px;
        margin-bottom: 15px;

        border-radius: ${theme.radius.big};
        border: 1px solid ${theme.color.greyLighter};
        transition: all ${theme.transition};

        @media ${theme.mediaQueries.queryTablet} {
            width: 100%;
        }

        &:hover {
            text-decoration: none;

            @media ${theme.mediaQueries.queryVl} {
                transform: translateY(-4px);
                box-shadow: 0 10px 20px 5px ${theme.color.greyLighter};
            }
        }
    `}
`;

export const ButtonBottomItemStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        flex-direction: row;
        align-items: center;

        color: ${theme.color.primary};
        font-size: ${theme.fontSize.bigger};
    `}
`;

export const ButtonBottomNameStyled = styled.div<ButtonBottomNameStyledProps>`
    ${({ theme, type }) => css`
        position: relative;
        flex-grow: 1;

        color: ${theme.color.primary};
        font-size: ${theme.fontSize.default};
        font-weight: 400;

        @media ${theme.mediaQueries.queryMd} {
            font-size: ${theme.fontSize.bigger};
        }

        ${type === 'right' &&
        css`
            display: none;

            @media ${theme.mediaQueries.queryVl} {
                display: block;
                margin-left: 20px;
            }
        `}
    `}
`;

export const ButtonBottomIconStyled = styled(Icon)<ButtonBottomIconStyledProps>`
    ${({ theme, type }) => css`
        width: 24px;
        height: 24px;
        ${type === 'right' ? 'margin-left' : 'margin-right'}: 12px;

        font-size: 24px;
        color: ${type === 'right' ? `${theme.color.primary}` : `${theme.color.orange}`};

        @media ${theme.mediaQueries.queryXl} {
            ${type === 'right' ? 'margin-left' : 'margin-right'}: 20px;
        }
    `}
`;
