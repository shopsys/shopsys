import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

const localVariables = {
    titleFontSize: '45px',
    titleLineheight: '42px',
    radius: '8px',
} as const;

export const BlogArticleWrapper = styled.div`
    padding: 0 20px;
`;

export const BlogArticleTextContent = styled.div`
    display: flex;
    flex-direction: column;
    width: 100%;
    margin-bottom: 50px;
`;

export const BlogArticleDate = styled.div(
    ({ theme }) => css`
        font-size: ${theme.fontSize.extraSmall};
        font-weight: 600;
        text-align: left;
        color: ${theme.color.grey};

        margin-bottom: 8px;
    `,
);

export const ArticleImageWrapper = styled.div`
    display: flex;
    margin-bottom: 40px;
    border-radius: ${localVariables.radius};
    overflow: hidden;

    div {
        flex: 1;

        img {
            object-fit: fill;
        }
    }
`;
