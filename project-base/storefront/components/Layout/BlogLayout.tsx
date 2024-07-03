import { Webline } from './Webline/Webline';
import { BlogSignpost } from 'components/Blocks/BlogSignpost/BlogSignpost';
import { useBlogCategories } from 'graphql/requests/blogCategories/queries/BlogCategoriesQuery.generated';
import { useRef } from 'react';

type BlogLayoutProps = {
    activeCategoryUuid: string;
};

export const BlogLayout: FC<BlogLayoutProps> = ({ children, activeCategoryUuid }) => {
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);
    const [{ data: blogCategoriesData }] = useBlogCategories();

    return (
        <Webline>
            <div className="scroll-mt-5" ref={paginationScrollTargetRef}>
                <div className="mb-16 flex flex-col vl:flex-row">
                    <div className="order-2 mb-16 flex flex-col w-full vl:order-1 vl:flex-1 vl:w-7/12 xl:w-2/3">
                        {children}
                    </div>
                    <div className="order-1 mb-7 flex w-full vl:w-5/12 xl:w-1/3 flex-col vl:order-2 vl:pl-8">
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
