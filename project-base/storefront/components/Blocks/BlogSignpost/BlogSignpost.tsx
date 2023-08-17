import { BlogSignpostIcon } from './BlogSignpostIcon';
import { BlogSignpostItem } from './BlogSignpostItem';
import { Children } from './Children';
import { Heading } from 'components/Basic/Heading/Heading';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { Fragment } from 'react';
import { ListedBlogCategoryRecursiveType } from 'types/blogCategory';

type BlogSingpostProps = {
    blogCategoryItems?: ListedBlogCategoryRecursiveType[];
    activeItem: string;
};

const TEST_IDENTIFIER = 'blocks-blogsignpost-';

export const BlogSignpost: FC<BlogSingpostProps> = ({ blogCategoryItems, activeItem }) => {
    const t = useTypedTranslationFunction();

    return (
        <div className="flex flex-col rounded bg-primary p-7">
            <Heading type="h2">{t('Article categories')}</Heading>
            {!!blogCategoryItems &&
                blogCategoryItems.map((blogCategory, index) => {
                    const isActive = activeItem === blogCategory.uuid;

                    return (
                        <Fragment key={blogCategory.uuid}>
                            <BlogSignpostItem
                                isActive={isActive}
                                dataTestId={TEST_IDENTIFIER + index}
                                href={blogCategory.link}
                            >
                                <BlogSignpostIcon isActive={isActive} />
                                {blogCategory.name}
                            </BlogSignpostItem>
                            {!!blogCategory.children?.length && (
                                <Children blogCategory={blogCategory} activeItem={activeItem} itemLevel={1} />
                            )}
                        </Fragment>
                    );
                })}
        </div>
    );
};
