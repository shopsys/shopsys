import { Webline } from './Webline/Webline';
import { BlogSignpost } from 'components/Blocks/BlogSignpost/BlogSignpost';
import { useBlogCategories } from 'graphql/requests/blogCategories/queries/BlogCategoriesQuery.generated';

type BlogLayoutProps = {
    activeCategoryUuid: string;
};

export const BlogLayout: FC<BlogLayoutProps> = ({ children, activeCategoryUuid }) => {
    const [{ data: blogCategoriesData }] = useBlogCategories();

    return (
        <Webline>
            <div className="scroll-mt-5">
                <div className="mb-[60px] flex flex-col gap-3 md:gap-10 xl:flex-row xl:gap-[60px]">
                    <div className="order-2 flex w-full flex-col xl:order-1 xl:max-w-[840px] xl:flex-1">{children}</div>
                    <div className="order-1 w-full xl:order-2 xl:w-[300px]">
                        <BlogSignpost
                            activeItem={activeCategoryUuid}
                            blogCategoryItems={blogCategoriesData?.blogCategories}
                        />
                    </div>
                </div>
            </div>
        </Webline>
    );
};
