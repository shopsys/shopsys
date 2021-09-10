import { css } from 'styled-components';
import Icon from '../../Basic/Icon';
import { styled } from '../../Theme/main';

export const OverlayStyled = styled.div`
    ${({ theme }) => css`
        bottom: 0;
        left: 0;
        position: fixed;
        right: 0;
        top: 0;
        width: 100%;
        height: 100%;
        z-index: ${theme.zIndex.overlay};
        display: flex;
        justify-content: center;
        align-items: center;

        background-color: rgba(0, 0, 0, 0.6);
        transition: all 0.2s cubic-bezier(0.8, 0.2, 0.48, 1);
        cursor: pointer;
    `}
`;

export const PopupStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        flex-direction: column;
        max-height: calc(100% - 20px);
        padding: 5px;
        z-index: ${theme.zIndex.popup};
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translateX(-50%) translateY(-50%);

        cursor: auto;
        border-radius: ${theme.radius.big};
        box-shadow: 0 30px 50px rgba(0, 0, 0, 0.2);
        transition: opacity 0.2s ease-in-out, transform 0.2s ease-in-out;
        background-color: ${theme.color.creamWhite};

        @media ${theme.mediaQueries.queryTablet} {
            max-width: 96%;
        }
    `}
`;

export const PopupHeaderStyled = styled.div`
    height: 36px;
    display: flex;
    justify-content: end;
    align-items: center;
`;

export const PopupButtonCloseStyled = styled.button`
    ${({ theme }) => css`
        display: flex;
        align-items: center;
        justify-content: center;
        height: 36px;
        width: 36px;

        background-color: ${theme.color.creamWhite};
        border: 0;
        border-radius: 100%;
        color: ${theme.color.grey};
        cursor: pointer;
        font-size: 10px;
        outline: 0;
        text-decoration: none;
    `}
`;

export const PopupButtonCloseIconStyled = styled(Icon)`
    ${({ theme }) => css`
        width: 24px;
        height: 24px;
        color: ${theme.color.primary};
    `}
`;

export const PopupContentStyled = styled.div`
    padding: 0 15px 15px 15px;
`;
