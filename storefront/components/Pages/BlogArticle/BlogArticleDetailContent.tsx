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
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC, useMemo } from 'react';
import { BlogArticleDetailType } from 'types/blogArticle';

type BlogArticleDetailContentProps = {
    blogArticle: BlogArticleDetailType;
};

const TEST_IDENTIFIER = 'pages-blogarticle-';
const PRODUCT_STRING_PATTERN =
    /<div class="gjs-product" data-product=".+?"><\/div>|<div data-product=".+?" class="gjs-product"><\/div>/g;
const PRODUCTS_STRING_PATTERN =
    /<div class="gjs-products" data-products=".+?"><\/div>|<div data-products=".+?" class="gjs-products"><\/div>/g;

export const BlogArticleDetailContent: FC<BlogArticleDetailContentProps> = ({ blogArticle }) => {
    const t = useTypedTranslationFunction();

    const textWithProductNames = useMemo(() => {
        const addProductNamesToText = function useRegex(text: string | null): string | null {
            if (text === null) {
                return null;
            }

            return text
                .replaceAll(PRODUCT_STRING_PATTERN, '')
                .replaceAll(PRODUCTS_STRING_PATTERN, (productsComponents) => {
                    const products = /data-products="(?<products>.+?)"/g.exec(productsComponents)?.groups?.products;

                    if (products !== undefined) {
                        return products
                            .split(',')
                            .map((product) => {
                                const namedProduct = blogArticle.blogArticleProducts.find(
                                    (blogArticleProduct) => blogArticleProduct.catalogNumber.toString() === product,
                                );

                                return namedProduct
                                    ? `<a href='${namedProduct.slug}'> ${namedProduct.fullName}</a>`
                                    : undefined;
                            })
                            .filter(Boolean)
                            .join(', ');
                    }

                    return '';
                });
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
                        <div className="gjs-editable" data-gjs-type="editable">
                            <UserText htmlContent={textWithProductNames} testIdentifier={TEST_IDENTIFIER + 'content'} />
                        </div>
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
