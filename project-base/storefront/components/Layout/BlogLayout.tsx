import { ArticleAnchorNavigation } from 'components/Blocks/ArticleAnchorNavigation/ArticleAnchorNavigation';
import { BlogSignpost } from 'components/Blocks/BlogSignpost/BlogSignpost';
import { TIDs } from 'cypress/tids';
import { useBlogCategories } from 'graphql/requests/blogCategories/queries/BlogCategoriesQuery.generated';
import { type ReactNode } from 'react';
import { twJoin } from 'tailwind-merge';
import { type ArticleHeading } from 'types/articleHeading';
import { Webline } from './Webline/Webline';

type BlogLayoutProps = {
    activeCategoryUuid: string;
    heading?: ReactNode;
    headings?: ArticleHeading[];
};

export const BlogLayout: FC<BlogLayoutProps> = ({ children, activeCategoryUuid, heading, headings = [] }) => {
    const [{ data: blogCategoriesData }] = useBlogCategories();
    const hasHeading = heading !== undefined && heading !== null;

    return (
        <Webline className="scroll-mt-5">
            <div className="grid justify-center gap-3 md:gap-10 xl:grid-cols-[minmax(0,52.5rem)_18.75rem] xl:gap-x-5 xl:gap-y-4">
                {hasHeading && <div className="xl:col-start-1 xl:row-start-1">{heading}</div>}

                <div
                    className={twJoin(
                        'flex w-full flex-col gap-4 md:gap-8 xl:sticky xl:top-[calc(var(--sticky-navigation-offset,0px)+0.625rem)] xl:col-start-2 xl:row-start-1 xl:max-h-[calc(100vh-var(--sticky-navigation-offset,0px)-var(--spacing-5))] xl:self-start xl:overflow-y-auto xl:overscroll-contain xl:pr-2',
                        hasHeading && 'xl:row-span-2',
                    )}
                    data-tid={TIDs.blog_sidebar}
                >
                    <ArticleAnchorNavigation headings={headings} />

                    <BlogSignpost
                        activeItem={activeCategoryUuid}
                        blogCategoryItems={blogCategoriesData?.blogCategories}
                    />
                </div>

                <div
                    className={twJoin(
                        'flex w-full min-w-0 flex-col gap-4 xl:col-start-1',
                        hasHeading ? 'xl:row-start-2' : 'xl:row-start-1',
                    )}
                >
                    {children}
                </div>
            </div>
        </Webline>
    );
};
