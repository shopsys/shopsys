import { BlogCategoryArticlesWrapper } from './BlogCategoryArticlesWrapper';
import { Heading } from 'components/Basic/Heading/Heading';
import { BlogSignpost } from 'components/Blocks/BlogSignpost/BlogSignpost';
import { PaginationProvider } from 'components/Blocks/Pagination/PaginationProvider';
import { Webline } from 'components/Layout/Webline/Webline';
import { getNewPagination } from 'helpers/pagination/getNewPagination';
import { parsePageNumberFromQuery } from 'helpers/pagination/parsePageNumberFromQuery';
import { PAGE_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
import { useRouter } from 'next/router';
import { FC, useRef } from 'react';
import { BlogCategoryDetailType } from 'types/blogCategory';

type BlogCategoryContentProps = {
    blogCategory: BlogCategoryDetailType;
};

export const BlogCategoryContent: FC<BlogCategoryContentProps> = ({ blogCategory }) => {
    const router = useRouter();
    const containerWrapRef = useRef<null | HTMLDivElement>(null);
    const currentPage = parsePageNumberFromQuery(router.query[PAGE_QUERY_PARAMETER_NAME]);

    return (
        <PaginationProvider key={blogCategory.uuid} {...getNewPagination(currentPage)}>
            <Webline>
                <div ref={containerWrapRef}>
                    <Heading type="h1">{blogCategory.name}</Heading>
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
        </PaginationProvider>
    );
};
