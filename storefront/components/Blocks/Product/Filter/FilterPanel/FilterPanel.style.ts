import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

const localVariables = {
    panelHeight: '68px',
    closeButttonSize: '30px',
};

export const FilterPanelStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        flex-direction: column;
        z-index: ${theme.zIndex.aboveOverlay};
        height: 100%;
        padding: 0 0 5px;

        border-radius: 0;
        background-color: ${theme.color.blueLight};

        @media ${theme.mediaQueries.queryVl} {
            height: auto;

            border-radius: ${theme.radius.medium};
            z-index: ${theme.zIndex.above};
        }
    `}
`;

export const FilterPanelHeaderStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: ${localVariables.panelHeight};
        padding: 0 20px;

        background-color: ${theme.color.blueLight};
        font-size: 26px;
        line-height: 32px;

        @media ${theme.mediaQueries.queryVl} {
            display: none;
        }
    `}
`;

export const FilterPanelBodyScrollStyled = styled.div`
    ${({ theme }) => css`
        overflow-y: scroll;
        padding: 0 20px;
        height: 100%;

        @media ${theme.mediaQueries.queryVl} {
            position: static;
            overflow: visible;
        }
    `}
`;

export const FilterPanelFooterStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        align-items: center;
        justify-content: end;
        height: ${localVariables.panelHeight};
        padding: 0 20px;

        background-color: ${theme.color.white};
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.15);

        button {
            display: inline-block;

            text-transform: lowercase;

            &::first-letter {
                text-transform: uppercase;
            }
        }

        @media ${theme.mediaQueries.queryVl} {
            display: none;
        }
    `}
`;

export const FilterCloseButtonStyled = styled.span`
    ${({ theme }) => css`
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: ${localVariables.closeButttonSize};
        height: ${localVariables.closeButttonSize};
        position: relative;

        color: ${theme.color.primary};
        border: 1px solid ${theme.color.primary};
        border-radius: 50%;
        cursor: pointer;

        &:hover {
            background-color: ${theme.color.black};
            color: ${theme.color.white};
        }
    `}
`;
