import { ArticleDate } from 'components/Basic/ArticleDate/ArticleDate';
import { GrapesJsParser } from 'components/Basic/UserText/GrapesJsParser';
import { VISIBLE_SLIDER_ITEMS_ARTICLE } from 'components/Blocks/Product/ProductsSlider';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeArticleDetailFragment } from 'graphql/requests/articlesInterface/articles/fragments/ArticleDetailFragment.generated';
import { useFormatDate } from 'utils/formatting/useFormatDate';

type ArticleDetailContentProps = {
    article: TypeArticleDetailFragment;
};

export const ArticleDetailContent: FC<ArticleDetailContentProps> = ({ article }) => {
    const { formatDate } = useFormatDate();

    return (
        <VerticalStack gap="sm">
            <Webline width="md">
                <h1 className="mb-4">{article.seoH1 || article.articleName}</h1>

                <ArticleDate date={formatDate(article.createdAt)} />
            </Webline>

            {article.text !== null && (
                <Webline className="flex w-full flex-col" width="md">
                    <GrapesJsParser text={article.text} visibleSliderItems={VISIBLE_SLIDER_ITEMS_ARTICLE} />
                </Webline>
            )}
        </VerticalStack>
    );
};
