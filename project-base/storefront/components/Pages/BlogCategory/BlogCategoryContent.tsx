import { BlogCategoryArticlesWrapper } from './BlogCategoryArticlesWrapper';
import { HeadingPaginated } from 'components/Basic/Heading/HeadingPaginated';
import { BlogSignpost } from 'components/Blocks/BlogSignpost/BlogSignpost';
import { Webline } from 'components/Layout/Webline/Webline';
import { BlogCategoryDetailFragmentApi } from 'graphql/generated';

type BlogCategoryContentProps = {
    blogCategory: BlogCategoryDetailFragmentApi;
};

export const BlogCategoryContent: FC<BlogCategoryContentProps> = ({ blogCategory }) => {
    return (
        <Webline>
            <div>
                <HeadingPaginated type="h1" totalCount={blogCategory.articlesTotalCount}>
                    {blogCategory.name}
                </HeadingPaginated>
                <div className="mb-16 flex flex-col vl:flex-row">
                    <div className="order-2 mb-16 flex w-full flex-col vl:order-1 vl:flex-1">
                        <BlogCategoryArticlesWrapper uuid={blogCategory.uuid} />
                    </div>
                    <div className="order-1 mb-7 flex w-full flex-col vl:order-2 vl:w-[435px] vl:pl-12">
                        <BlogSignpost
                            blogCategoryItems={blogCategory.blogCategoriesTree}
                            activeItem={blogCategory.uuid}
                        />
                    </div>
                </div>
            </div>
        </Webline>
    );
};
