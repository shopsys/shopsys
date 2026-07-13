import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { Image } from 'components/Basic/Image/Image';
import { TIDs } from 'cypress/tids';
import { TypeBlogArticleAuthorFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/BlogArticleAuthorFragment.generated';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type BlogArticleAuthorBoxProps = {
    author: TypeBlogArticleAuthorFragment;
};

export const BlogArticleAuthorBox: FC<BlogArticleAuthorBoxProps> = ({ author }) => {
    const { t } = useTranslation();

    return (
        <div className="flex flex-col gap-4 rounded bg-background-more p-5" data-tid={TIDs.blog_article_author_box}>
            <span className="h5">{t('Author of the article')}</span>

            <div className="flex items-start gap-4">
                {author.mainImage ? (
                    <Image
                        alt={author.mainImage.name || author.name}
                        className="size-16 shrink-0 rounded-full object-cover"
                        height={64}
                        src={author.mainImage.url}
                        width={64}
                    />
                ) : (
                    <UserIcon className="size-16 shrink-0 rounded-full bg-background p-3 text-text-less" />
                )}

                <div className="flex flex-col gap-1">
                    <span className="font-bold">{author.name}</span>
                    {!!author.jobTitle && <span className="text-sm text-text-less">{author.jobTitle}</span>}
                    {!!author.description && <p className="mt-1 whitespace-pre-line text-sm">{author.description}</p>}
                </div>
            </div>
        </div>
    );
};
