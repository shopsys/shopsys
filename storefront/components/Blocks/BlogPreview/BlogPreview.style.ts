import { Icon } from 'components/Basic/Icon/Icon';
import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const BlogPreviewStyled = styled.div`
    ${({ theme }) => css`
        padding: 50px 0 40px;

        @media ${theme.mediaQueries.queryVl} {
            padding: 50px 0 60px;
        }
    `}
`;

export const BlogPreviewHeadingStyled = styled.div`
    align-items: baseline;
    display: flex;
    flex-wrap: wrap;
    margin-bottom: 20px;
`;

export const BlogPreviewHeadingTitleStyled = styled.h2`
    ${({ theme }) => css`
        line-height: 36px;
        margin: 0 32px 7px 0;

        color: ${theme.color.creamWhite};
        font-size: 32px;
        font-weight: 700;
        text-transform: none;
    `}
`;

export const BlogPreviewHeadingLinkStyled = styled.a`
    ${({ theme }) => css`
        align-items: center;
        display: flex;
        margin-bottom: 7px;
        text-transform: uppercase;

        font-weight: 700;
        text-decoration: none;
        color: ${theme.color.creamWhite};

        &:hover {
            text-decoration: none;
            color: ${theme.color.creamWhite};
        }
    `}
`;

export const BlogPreviewHeadingLinkIconStyled = styled(Icon)`
    ${({ theme }) => css`
        margin-left: 6px;
        position: relative;
        top: -1px;

        color: ${theme.color.creamWhite};
        font-size: ${theme.fontSize.extraSmall};
    `}
`;

export const BlogPreviewArticlesStyled = styled.div`
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
`;

export const BlogPreviewArticlesMainStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        flex-direction: column;
        margin-bottom: 30px;

        @media ${theme.mediaQueries.queryLg} {
            flex-direction: row;
            margin-left: -43.5px;
        }

        @media ${theme.mediaQueries.queryVl} {
            flex: 1;
            margin-bottom: 0;
        }

        @media ${theme.mediaQueries.queryXl} {
            flex: 1;
            margin-left: -87px;
        }
    `}
`;

export const BlogPreviewArticlesSideStyled = styled.div`
    ${({ theme }) => css`
        flex-direction: row;
        overflow: hidden;

        @media ${theme.mediaQueries.queryVl} {
            display: flex;
            flex-direction: column;
            margin-left: 50px;
            width: 346px;
        }

        @media ${theme.mediaQueries.queryXl} {
            margin-left: 103px;
        }
    `}
`;
