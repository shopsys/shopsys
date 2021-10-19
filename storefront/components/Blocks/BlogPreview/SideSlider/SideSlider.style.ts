import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

export const SideSliderItemStyled = styled.div`
    display: flex;
    flex-direction: column;
`;

export const SideSliderImageStyled = styled.div`
    display: flex;
    width: 100%;
`;

export const SideSliderImageLinkStyled = styled.a`
    ${({ theme }) => css`
        display: flex;
        position: relative;
        margin-bottom: 8px;
        width: 100%;

        font-size: 0;

        img {
            max-height: 127px;

            border-radius: ${theme.radius.medium};
        }
    `}
`;

export const SideSliderContentStyled = styled.div`
    flex: 1;
`;

export const SideSliderNameStyled = styled.a`
    ${({ theme }) => css`
        display: block;
        line-height: 22px;

        color: ${theme.color.creamWhite};
        text-decoration: none;
        font-weight: 700;
        font-size: ${theme.fontSize.bigger};

        &:hover {
            color: ${theme.color.creamWhite};
        }
    `}
`;
