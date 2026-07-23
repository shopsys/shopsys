import { ArticleAnchorNavigation } from 'components/Blocks/ArticleAnchorNavigation/ArticleAnchorNavigation';
import { BlogSignpost } from 'components/Blocks/BlogSignpost/BlogSignpost';
import { TIDs } from 'cypress/tids';
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
            <div className="flex flex-col-reverse gap-3 md:gap-10 xl:flex-row xl:gap-15">
                <div className="flex w-full flex-col gap-4 xl:max-w-210 xl:flex-1">{children}</div>

                <div
                    className="flex w-full flex-col gap-8 xl:sticky xl:top-[calc(var(--sticky-navigation-offset,0)+10px)] xl:max-h-[calc(100vh-var(--sticky-navigation-offset,0)-20px)] xl:w-75 xl:self-start xl:overflow-y-auto xl:overscroll-contain xl:pr-2"
                    data-tid={TIDs.blog_sidebar}
                >
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
