import { ArticleDate } from 'components/Basic/ArticleDate/ArticleDate';
import { GrapesJsParser } from 'components/Basic/UserText/GrapesJsParser';
import { VISIBLE_SLIDER_ITEMS_ARTICLE } from 'components/Blocks/Product/ProductsSlider';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import { TypeArticleDetailFragment } from 'graphql/requests/articlesInterface/articles/fragments/ArticleDetailFragment.generated';

type ArticleDetailContentProps = {
    article: TypeArticleDetailFragment;
};

export const ArticleDetailContent: FC<ArticleDetailContentProps> = ({ article }) => {
    return (
        <Webline width="vl">
            <VerticalStack gap="sm">
                <h1>{article.seoH1 || article.articleName}</h1>

                <ArticleDate date={article.createdAt} tid={TIDs.article_creation_date} />

                {article.text !== null && (
                    <GrapesJsParser text={article.text} visibleSliderItems={VISIBLE_SLIDER_ITEMS_ARTICLE} />
                )}
            </VerticalStack>
        </Webline>
    );
};
