import { ArticleAnchorNavigation } from 'components/Blocks/ArticleAnchorNavigation/ArticleAnchorNavigation';
import { BlogSignpost } from 'components/Blocks/BlogSignpost/BlogSignpost';
import { useBlogCategories } from 'graphql/requests/blogCategories/queries/BlogCategoriesQuery.generated';
import { type ArticleHeading } from 'types/articleHeading';
import { Webline } from './Webline/Webline';

type BlogLayoutProps = {
    activeCategoryUuid: string;
    headings?: ArticleHeading[];
};

export const BlogLayout: FC<BlogLayoutProps> = ({ children, activeCategoryUuid, headings = [] }) => {
    const [{ data: blogCategoriesData }] = useBlogCategories();

    return (
        <Webline className="scroll-mt-5">
            <div className="flex flex-col-reverse gap-3 md:gap-10 xl:flex-row xl:gap-[60px]">
                <div className="flex w-full flex-col gap-4 xl:max-w-[840px] xl:flex-1">{children}</div>

                <div className="flex w-full flex-col gap-8 xl:sticky xl:top-2.5 xl:max-h-[calc(100vh-20px)] xl:w-[300px] xl:self-start xl:overflow-y-auto xl:overscroll-contain xl:pr-2">
                    <ArticleAnchorNavigation headings={headings} />

                    <BlogSignpost
                        activeItem={activeCategoryUuid}
                        blogCategoryItems={blogCategoriesData?.blogCategories}
                    />
                </div>
            </div>
        </Webline>
    );
};
