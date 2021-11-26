import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

const localVariables = {
    TitleFontSize: '45px',
    TitleLineheight: '42px',
    Radius: '8px',
};

export const BlogArticleWrapper = styled.div`
    padding: 0 20px;
`;

export const ProductSectionWrapper = styled.div`
    ${({ theme }) => css`
        padding: 3px;
        margin-bottom: 35px;

        border: 0;
        border-radius: ${localVariables.Radius};
        background-color: ${theme.color.white};
    `}
`;

export const ProductSectionTitle = styled.h3`
    ${({ theme }) => css`
        margin-bottom: 25px;
        line-height: ${theme.lineHeight.default};

        text-transform: uppercase;
        font-size: ${theme.fontSize.default};
        color: ${theme.color.primary};
        font-weight: 600;
    `}
`;

export const BlogArticleTextContent = styled.div`
    display: flex;
    flex-direction: column;
    width: 100%;
    margin-bottom: 50px;
`;

export const BlogArticleDate = styled.div`
    ${({ theme }) => css`
        font-size: ${theme.fontSize.extraSmall};
        font-weight: 600;
        text-align: left;
        color: ${theme.color.grey};
    `}
`;

export const ArticleImageWrapper = styled.div`
    display: flex;
    margin-bottom: 40px;
    border-radius: ${localVariables.Radius};
    overflow: hidden;

    div {
        flex: 1;

        img {
            object-fit: fill;
        }
    }
`;
