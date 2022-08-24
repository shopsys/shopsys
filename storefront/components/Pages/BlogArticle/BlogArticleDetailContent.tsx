import {
    ArticleImageWrapper,
    BlogArticleDate,
    BlogArticleTextContent,
    BlogArticleWrapper,
    ProductSectionTitle,
    ProductSectionWrapper,
} from './BlogArticleDetailContent.style';
import { Image } from 'components/Basic/Image/Image';
import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
import { UserText } from 'components/Helpers/UserText/UserText';
import { Webline } from 'components/Layout/Webline/Webline';
import { ArticleTitle } from 'components/Pages/Article/ArticleDetailContent.style';
import { formatDate } from 'helpers/formaters/formatDate';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC, useMemo } from 'react';
import { BlogArticleDetailType } from 'types/blogArticle';

type BlogArticleDetailContentProps = {
    blogArticle: BlogArticleDetailType;
};

const TEST_IDENTIFIER = 'pages-blogarticle-';
const PRODUCT_STRING_PATTERN = /\{[^}]*\}/g;
const CATALOG_NUMBERS_PATTERN = /[0-9]+/g;

export const BlogArticleDetailContent: FC<BlogArticleDetailContentProps> = ({ blogArticle }) => {
    const t = useTypedTranslationFunction();

    const textWithProductNames = useMemo(() => {
        const addProductNamesToText = function useRegex(text: string | null): string | null {
            if (text === null) {
                return null;
            }

            const replaceProductString = (matchedString: string): string => {
                const replaceProducts = (product: string): string => {
                    const namedProduct = blogArticle.blogArticleProducts.find(
                        (blogArticleProduct) => blogArticleProduct.catalogNumber.toString() === product,
                    );
                    if (namedProduct === undefined) {
                        return ' ';
                    }
                    return `<a href='${namedProduct.slug}'> ${namedProduct.fullName}</a>`;
                };
                return matchedString.replaceAll(CATALOG_NUMBERS_PATTERN, replaceProducts).slice(10).slice(0, -1);
            };
            return text.replaceAll(PRODUCT_STRING_PATTERN, replaceProductString);
        };

        return addProductNamesToText(blogArticle.text);
    }, [blogArticle.blogArticleProducts, blogArticle.text]);

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
                    {textWithProductNames !== null && (
                        <UserText htmlContent={textWithProductNames} data-testid={TEST_IDENTIFIER + 'content'} />
                    )}
                </BlogArticleTextContent>

                {blogArticle.blogArticleProducts.length > 0 && (
                    <ProductSectionWrapper data-testid={TEST_IDENTIFIER + 'products'}>
                        <ProductSectionTitle>{t('Products mentioned in this article')}</ProductSectionTitle>
                        <ProductsSlider products={blogArticle.blogArticleProducts} gtmListName="blog article" />
                    </ProductSectionWrapper>
                )}
            </BlogArticleWrapper>
        </Webline>
    );
};
