import { BlogPreviewProps } from './BlogPreview';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import useTranslation from 'next-translate/useTranslation';

type BlogPreviewPlaceholderProps = Pick<BlogPreviewProps, 'blogArticles' | 'blogUrl'>;

export const BlogPreviewPlaceholder: FC<BlogPreviewPlaceholderProps> = ({ blogArticles, blogUrl }) => {
    const { t } = useTranslation();

    return (
        <div>
            <h2 className="text-creamWhite">{t('Magazine')}</h2>

            {!!blogUrl && (
                <ExtendedNextLink className="text-creamWhite " href={blogUrl} type="blogCategory">
                    {t('All articles')}
                </ExtendedNextLink>
            )}

            {blogArticles?.slice(0, 2).map(
                (item) =>
                    item?.node && (
                        <div key={item.node.uuid}>
                            {item.node.blogCategories.map(
                                (blogCategory) =>
                                    !!blogCategory.parent && (
                                        <ExtendedNextLink
                                            key={blogCategory.uuid}
                                            href={blogCategory.link}
                                            type="blogCategory"
                                        >
                                            {blogCategory.name}
                                        </ExtendedNextLink>
                                    ),
                            )}

                            <ExtendedNextLink className="text-text" href={item.node.link} type="blogArticle">
                                {item.node.name}
                            </ExtendedNextLink>

                            <div>{item.node.perex}</div>
                        </div>
                    ),
            )}

            {blogArticles?.slice(2).map(
                (item) =>
                    item?.node && (
                        <div key={item.node.uuid}>
                            {item.node.blogCategories.map(
                                (blogCategory) =>
                                    !!blogCategory.parent && (
                                        <ExtendedNextLink
                                            key={blogCategory.uuid}
                                            href={blogCategory.link}
                                            type="blogCategory"
                                        >
                                            {blogCategory.name}
                                        </ExtendedNextLink>
                                    ),
                            )}

                            <ExtendedNextLink className="text-text" href={item.node.link} type="blogArticle">
                                {item.node.name}
                            </ExtendedNextLink>

                            <div>{item.node.perex}</div>
                        </div>
                    ),
            )}
        </div>
    );
};
