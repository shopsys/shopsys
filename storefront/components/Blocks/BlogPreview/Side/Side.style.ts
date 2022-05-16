import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const SideItemStyled = styled.div`
    display: flex;
    flex-direction: row;
    margin-bottom: 11px;
    width: 100%;
`;

export const SideImageStyled = styled.div`
    display: flex;
    max-width: 148px;
    width: 100%;
`;

export const SideImageLinkStyled = styled.a`
    ${({ theme }) => css`
        display: flex;
        position: relative;
        width: 100%;

        font-size: 0;

        img {
            max-height: 82px;

            border-radius: ${theme.radius.medium};
        }
    `}
`;

export const SideContentStyled = styled.div`
    flex: 1;
    margin-left: 20px;
`;

export const SideNameStyled = styled.a`
    ${({ theme }) => css`
        display: block;
        line-height: 20px;
        margin-bottom: 6px;

        color: ${theme.color.creamWhite};
        text-decoration: none;
        font-weight: 700;
        font-size: ${theme.fontSize.default};

        &:hover {
            color: ${theme.color.creamWhite};
        }
    `}
`;
