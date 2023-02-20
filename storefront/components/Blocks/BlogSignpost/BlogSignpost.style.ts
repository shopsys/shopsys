import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

type BlogSignpostItemStyledProps = {
    isActive: boolean;
    itemLevel?: number;
};

export const BlogSignpostStyled = styled.div(
    ({ theme }) => css`
        display: flex;
        flex-direction: column;
        padding: 30px;

        background-color: ${theme.color.primary};
        border-radius: ${theme.radius.big};
    `,
);

export const BlogSignpostItemStyled = styled.a<BlogSignpostItemStyledProps>(
    ({ theme, isActive, itemLevel }) => css`
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
    `,
);
