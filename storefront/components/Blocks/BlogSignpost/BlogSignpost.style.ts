import { Heading } from 'components/Basic/Heading/Heading';
import { Icon } from 'components/Basic/Icon/Icon';
import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

type BlogSignpostItemStyledProps = {
    isActive: boolean;
    itemLevel?: number;
};

type BlogSignpostItemIconStyledProps = {
    isActive: boolean;
};

export const BlogSignpostStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        flex-direction: column;
        padding: 30px;

        background-color: ${theme.color.primary};
        border-radius: ${theme.radius.big};
    `}
`;

export const BlogSignpostHeadingStyled = styled(Heading)`
    ${({ theme }) => css`
        color: ${theme.color.creamWhite};
    `}
`;

export const BlogSignpostItemStyled = styled.a<BlogSignpostItemStyledProps>`
    ${({ theme, isActive, itemLevel }) => css`
        position: relative;
        padding: 12px 35px;
        ${itemLevel !== undefined &&
        css`
            margin-left: calc(6px * ${itemLevel});
        `}

        color: ${isActive === true
            ? css`
                  ${theme.color.base}
              `
            : css`
                  ${theme.color.creamWhite}
              `};
        font-size: ${theme.fontSize.default};
        text-decoration: underline;
        border-radius: ${theme.radius.medium};
        ${isActive === true &&
        css`
            background-color: ${theme.color.creamWhite};
            text-decoration: none;
        `}

        &:hover {
            text-decoration: none;
            color: ${isActive === true
                ? css`
                      ${theme.color.base}
                  `
                : css`
                      ${theme.color.creamWhite}
                  `};
        }
    `}
`;

export const BlogSignpostItemIconStyled = styled(Icon)<BlogSignpostItemIconStyledProps>`
    ${({ theme, isActive }) => css`
        position: absolute;
        top: 50%;
        left: 10px;
        transform: rotate(-90deg) translate(50%);

        color: ${theme.color.creamWhite};

        ${isActive === true &&
        css`
            color: ${theme.color.base};
        `}
    `}
`;
