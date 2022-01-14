import {
    ArticleImageWrapper,
    BlogArticleDate,
    BlogArticleTextContent,
    BlogArticleWrapper,
    ProductSectionTitle,
    ProductSectionWrapper,
} from './BlogArticleDetail.style';
import { ArticleTitle } from 'components/Pages/Article/ArticleDetail.style';
import { BlogArticleDetailType } from 'types/blogArticle';
import { FC } from 'react';
import Image from 'components/Basic/Image/Image';
import ProductsSlider from 'components/Blocks/Product/ProductsSlider';
import UserText from 'components/Helpers/UserText';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

type BlogArticleDetailProps = {
    blogArticle: BlogArticleDetailType;
};
const BlogDetail: FC<BlogArticleDetailProps> = (props) => {
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
            <ArticleTitle>{props.blogArticle.name}</ArticleTitle>
            <BlogArticleWrapper>
                <BlogArticleTextContent>
                    {props.blogArticle.image === null ? null : (
                        <ArticleImageWrapper>
                            <Image image={props.blogArticle.image} alt={props.blogArticle.name} />
                        </ArticleImageWrapper>
                    )}
                    <BlogArticleDate>{props.blogArticle.publishDate}</BlogArticleDate>
                    {textWithProductNames === null ? null : <UserText htmlContent={textWithProductNames} />}
                </BlogArticleTextContent>

                {props.blogArticle.blogArticleProducts.length === 0 ? null : (
                    <ProductSectionWrapper>
                        <ProductSectionTitle>{t('Products mentioned in this article')}</ProductSectionTitle>
                        <ProductsSlider products={props.blogArticle.blogArticleProducts} />
                    </ProductSectionWrapper>
                )}
            </BlogArticleWrapper>
        </Webline>
    );
};

export default BlogDetail;
