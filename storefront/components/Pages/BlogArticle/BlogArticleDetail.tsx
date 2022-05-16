import {
    ArticleImageWrapper,
    BlogArticleDate,
    BlogArticleTextContent,
    BlogArticleWrapper,
    ProductSectionTitle,
    ProductSectionWrapper,
} from './BlogArticleDetail.style';
import Image from 'components/Basic/Image/Image';
import ProductsSlider from 'components/Blocks/Product/ProductsSlider';
import UserText from 'components/Helpers/UserText';
import Webline from 'components/Layout/Webline';
import { ArticleTitle } from 'components/Pages/Article/ArticleDetail.style';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { BlogArticleDetailType } from 'types/blogArticle';

type BlogArticleDetailProps = {
    blogArticle: BlogArticleDetailType;
};
const BlogDetail: FC<BlogArticleDetailProps> = (props) => {
    const testIdentifier = 'pages-blogarticle-';

    const t = useTypedTranslationFunction();

    const addProductNamesToText = function useRegex(text: string | null): string | null {
        if (text === null) {
            return null;
        }

        const productStringPattern = /\{[^}]*\}/g;
        const replaceProductString = (matchedString: string): string => {
            const catalogNumbersPattern = /[0-9]+/g;
            const replaceProducts = (product: string): string => {
                const namedProduct = props.blogArticle.blogArticleProducts.find(
                    (blogArticleProduct) => blogArticleProduct.catalogNumber.toString() === product,
                );
                if (namedProduct === undefined) {
                    return ' ';
                }
                return `<a href='${namedProduct.slug}'> ${namedProduct.name}</a>`;
            };
            return matchedString.replaceAll(catalogNumbersPattern, replaceProducts).slice(10).slice(0, -1);
        };
        return text.replaceAll(productStringPattern, replaceProductString);
    };

    const textWithProductNames = addProductNamesToText(props.blogArticle.text);

    return (
        <Webline>
            <ArticleTitle data-testid={testIdentifier + 'title'}>{props.blogArticle.name}</ArticleTitle>
            <BlogArticleWrapper>
                <BlogArticleTextContent>
                    {props.blogArticle.image === null ? null : (
                        <ArticleImageWrapper data-testid={testIdentifier + 'image'}>
                            <Image image={props.blogArticle.image} type="default" alt={props.blogArticle.name} />
                        </ArticleImageWrapper>
                    )}
                    <BlogArticleDate data-testid={testIdentifier + 'date'}>
                        {props.blogArticle.publishDate}
                    </BlogArticleDate>
                    {textWithProductNames === null ? null : (
                        <UserText htmlContent={textWithProductNames} data-testid={testIdentifier + 'content'} />
                    )}
                </BlogArticleTextContent>

                {props.blogArticle.blogArticleProducts.length === 0 ? null : (
                    <ProductSectionWrapper data-testid={testIdentifier + 'products'}>
                        <ProductSectionTitle>{t('Products mentioned in this article')}</ProductSectionTitle>
                        <ProductsSlider products={props.blogArticle.blogArticleProducts} />
                    </ProductSectionWrapper>
                )}
            </BlogArticleWrapper>
        </Webline>
    );
};

export default BlogDetail;
