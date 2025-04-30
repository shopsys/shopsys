import { GrapesJsParser } from 'app/_components/Basic/UserText/GrapesJsParser';
import { getArticleDetailQuery } from 'app/_queries/getArticleDetailQuery';
import { getFormatDate } from 'app/_utils/formatting/getFormatDate';
import { ArticleDate } from 'components/Basic/ArticleDate/ArticleDate';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { notFound } from 'next/navigation';
import { VISIBLE_SLIDER_ITEMS_ARTICLE } from 'utils/productSlider';

const ArticleDetailPage = async ({ params }: { params: Promise<{ articleSlug: string }> }) => {
    const { articleSlug } = await params;
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
            <VerticalStack gap="md">
                <h1>{article.seoH1 || article.articleName}</h1>

                <ArticleDate date={formatDate(article.createdAt)} />

                {article.text !== null && (
                    <div className="order-2 mb-16 flex w-full flex-col">
                        <GrapesJsParser text={article.text} visibleSliderItems={VISIBLE_SLIDER_ITEMS_ARTICLE} />
                    </div>
                )}
            </VerticalStack>
        </Webline>
    );
};

export default ArticleDetailPage;
