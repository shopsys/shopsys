import { ArticleTitle } from './ArticleTitle';
import { GrapesJsParser } from 'components/Basic/UserText/GrapesJsParser';
import { VISIBLE_SLIDER_ITEMS_ARTICLE } from 'components/Blocks/Product/ProductsSlider';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeArticleDetailFragment } from 'graphql/requests/articlesInterface/articles/fragments/ArticleDetailFragment.generated';
import { useFormatDate } from 'utils/formatting/useFormatDate';

type ArticleDetailContentProps = {
    article: TypeArticleDetailFragment;
};

export const ArticleDetailContent: FC<ArticleDetailContentProps> = ({ article }) => {
    const { formatDate } = useFormatDate();

    return (
        <Webline>
            <ArticleTitle>{article.seoH1 || article.articleName}</ArticleTitle>
            <p className="text-textDisabled mb-2 px-5 text-left text-xs font-semibold">
                {formatDate(article.createdAt)}
            </p>
            {article.text !== null && (
                <div className="order-2 mb-16 flex w-full flex-col">
                    <GrapesJsParser text={article.text} visibleSliderItems={VISIBLE_SLIDER_ITEMS_ARTICLE} />
                </div>
            )}
        </Webline>
    );
};
