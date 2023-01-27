import {
    ArticleImageWrapper,
    BlogArticleDate,
    BlogArticleTextContent,
    BlogArticleWrapper,
} from './BlogArticleDetailContent.style';
import { Image } from 'components/Basic/Image/Image';
import GrapeJsParser from 'components/Helpers/GrapeJsParser';
import { Webline } from 'components/Layout/Webline/Webline';
import { ArticleTitle } from 'components/Pages/Article/ArticleDetailContent.style';
import { formatDate } from 'helpers/formaters/formatDate';
import { FC } from 'react';
import { BlogArticleDetailType } from 'types/blogArticle';

type BlogArticleDetailContentProps = {
    blogArticle: BlogArticleDetailType;
};

const TEST_IDENTIFIER = 'pages-blogarticle-';

export const BlogArticleDetailContent: FC<BlogArticleDetailContentProps> = ({ blogArticle }) => {
    return (
        <Webline>
            <ArticleTitle data-testid={TEST_IDENTIFIER + 'title'}>{blogArticle.name}</ArticleTitle>
            <BlogArticleWrapper>
                <BlogArticleTextContent>
                    {blogArticle.image !== null && (
                        <ArticleImageWrapper data-testid={TEST_IDENTIFIER + 'image'}>
                            <Image image={blogArticle.image} type="default" alt={blogArticle.name} />
                        </ArticleImageWrapper>
                    )}
                    <BlogArticleDate data-testid={TEST_IDENTIFIER + 'date'}>
                        {formatDate(blogArticle.publishDate, 'l')}
                    </BlogArticleDate>
                    {blogArticle.text !== null && (
                        <GrapeJsParser text={blogArticle.text} allProducts={blogArticle.blogArticleProducts} />
                    )}
                </BlogArticleTextContent>
            </BlogArticleWrapper>
        </Webline>
    );
};
