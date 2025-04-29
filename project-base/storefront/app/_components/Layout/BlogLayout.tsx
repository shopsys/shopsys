import { BlogCategoryHeader } from 'app/_components/Page/BlogCategory/BlogCategoryHeader';
import { getBlogCategoriesQuery } from 'app/_queries/getBlogCategoriesQuery';
import { BlogSignpost } from 'components/Blocks/BlogSignpost/BlogSignpost';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeBlogCategoryDetailFragment } from 'graphql/requests/blogCategories/fragments/BlogCategoryDetailFragment.ssr';

type BlogLayoutProps = {
    blogCategory?: TypeBlogCategoryDetailFragment;
    activeCategoryUuid: string;
    children: React.ReactNode;
};

export const BlogLayout: FC<BlogLayoutProps> = async ({ children, blogCategory, activeCategoryUuid }) => {
    const blogCategoriesData = await getBlogCategoriesQuery();

    return (
        <>
            {blogCategory && (
                <BlogCategoryHeader
                    description={blogCategory.description}
                    image={blogCategory.mainImage}
                    title={blogCategory.name}
                />
            )}
            <Webline>
                <div className="scroll-mt-5">
                    <div className="mb-[60px] flex flex-col gap-3 md:gap-10 xl:flex-row xl:gap-[60px]">
                        <div className="order-2 flex w-full flex-col xl:order-1 xl:max-w-[840px] xl:flex-1">
                            {children}
                        </div>
                        <div className="order-1 w-full xl:order-2 xl:w-[300px]">
                            <BlogSignpost activeItem={activeCategoryUuid} blogCategoryItems={blogCategoriesData} />
                        </div>
                    </div>
                </div>
            </Webline>
        </>
    );
};
