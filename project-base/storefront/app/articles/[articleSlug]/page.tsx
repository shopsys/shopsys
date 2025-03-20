import { GrapesJsParser } from 'app/_components/Basic/UserText/GrapesJsParser';
import { getArticleDetailQuery } from 'app/_queries/getArticleDetailQuery';
import { getFormatDate } from 'app/_utils/formatting/getFormatDate';
import { Webline } from 'components/Layout/Webline/Webline';
import { ArticleTitle } from 'app/_components/Blocks/Article/ArticleTitle';
import { notFound } from 'next/navigation';
import { VISIBLE_SLIDER_ITEMS_ARTICLE } from 'utils/productSlider';

const ArticleDetailPage = async ({ params: { articleSlug } }: { params: { articleSlug: string } }) => {
    const articleDetailData = await getArticleDetailQuery(articleSlug);

    const article = articleDetailData?.__typename === 'ArticleSite' ? articleDetailData : null;

    if (!article) {
        return notFound();
    }

    const { formatDate } = await getFormatDate();

    // const pageViewEvent = useGtmFriendlyPageViewEvent(article);
    // useGtmPageViewEvent(pageViewEvent, isArticleDetailFetching);

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

export default ArticleDetailPage;
