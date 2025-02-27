import { Webline } from './Webline/Webline';
import { BlogSignpost } from 'components/Blocks/BlogSignpost/BlogSignpost';
import { useBlogCategories } from 'graphql/requests/blogCategories/queries/BlogCategoriesQuery.generated';

type BlogLayoutProps = {
    activeCategoryUuid: string;
};

export const BlogLayout: FC<BlogLayoutProps> = ({ children, activeCategoryUuid }) => {
    const [{ data: blogCategoriesData }] = useBlogCategories();

    return (
        <Webline className="scroll-mt-5">
            <div className="flex flex-col-reverse gap-3 md:gap-10 xl:flex-row xl:gap-[60px]">
                <div className="flex w-full flex-col gap-4 xl:max-w-[840px] xl:flex-1">{children}</div>

                <div className="w-full xl:w-[300px]">
                    <BlogSignpost
                        activeItem={activeCategoryUuid}
                        blogCategoryItems={blogCategoriesData?.blogCategories}
                    />
                </div>
            </div>
        </Webline>
    );
};
