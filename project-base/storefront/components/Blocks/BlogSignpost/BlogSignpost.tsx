import { BlogSignpostIcon } from './BlogSignpostIcon';
import { BlogSignpostItem } from './BlogSignpostItem';
import { Children } from './Children';
import { Heading } from 'components/Basic/Heading/Heading';
import useTranslation from 'next-translate/useTranslation';
import { Fragment } from 'react';
import { ListedBlogCategoryRecursiveType } from 'types/blogCategory';

type BlogSingpostProps = {
    blogCategoryItems?: ListedBlogCategoryRecursiveType[];
    activeItem: string;
};

const TEST_IDENTIFIER = 'blocks-blogsignpost-';

export const BlogSignpost: FC<BlogSingpostProps> = ({ blogCategoryItems, activeItem }) => {
    const { t } = useTranslation();

    return (
        <div className="flex flex-col rounded bg-primary p-7">
            <Heading type="h2">{t('Article categories')}</Heading>
            {!!blogCategoryItems &&
                blogCategoryItems.map((blogCategory, index) => {
                    const isActive = activeItem === blogCategory.uuid;

                    return (
                        <Fragment key={blogCategory.uuid}>
                            <BlogSignpostItem
                                dataTestId={TEST_IDENTIFIER + index}
                                href={blogCategory.link}
                                isActive={isActive}
                            >
                                <BlogSignpostIcon isActive={isActive} />
                                {blogCategory.name}
                            </BlogSignpostItem>
                            {!!blogCategory.children?.length && (
                                <Children activeItem={activeItem} blogCategory={blogCategory} itemLevel={1} />
                            )}
                        </Fragment>
                    );
                })}
        </div>
    );
};
