import { BlogSignpostIcon } from './BlogSignpostIcon';
import { BlogSignpostItem } from './BlogSignpostItem';
import { Children } from './Children';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import useTranslation from 'next-translate/useTranslation';
import { Fragment, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { ListedBlogCategoryRecursiveType } from 'types/blogCategory';

type BlogSingpostProps = {
    activeItem: string;
    blogCategoryItems?: ListedBlogCategoryRecursiveType[];
};

export const BlogSignpost: FC<BlogSingpostProps> = ({ blogCategoryItems, activeItem }) => {
    const { t } = useTranslation();
    const [isBlogSignpostOpen, setIsBlogSignpostOpen] = useState(false);

    return (
        <>
            <div className="relative flex flex-col gap-y-2.5">
                <div className="cursor-pointer vl:cursor-text">
                    <div
                        className={twJoin(
                            'group relative mb-0 font-secondary font-bold vl:pointer-events-none vl:font-semibold',
                            'max-vl:flex max-vl:justify-between max-vl:rounded-lg max-vl:px-3 max-vl:py-[10px] max-vl:text-textAccent max-vl:outline-none max-vl:outline-2 max-vl:outline-offset-[-2px] max-vl:outline-actionInvertedBorder max-vl:transition-all',
                            'max-vl:bg-background max-vl:hover:text-actionInvertedTextHovered max-vl:hover:outline-actionInvertedBorderHovered',
                            'max-vl:active:text-actionInvertedTextActive max-vl:active:outline-actionInvertedBorderActive',
                            isBlogSignpostOpen && 'max-vl:z-aboveOverlay',
                        )}
                        onClick={() => setIsBlogSignpostOpen(!isBlogSignpostOpen)}
                    >
                        {t('Article categories')}
                        <ArrowIcon
                            className={twJoin(
                                'text-text transition-all group-hover:text-actionInvertedTextHovered vl:hidden',
                                isBlogSignpostOpen && 'rotate-180',
                            )}
                        />
                    </div>
                </div>

                {blogCategoryItems && (
                    <div
                        className={twJoin(
                            'flex w-full flex-col gap-y-2.5',
                            isBlogSignpostOpen
                                ? 'max-vl:absolute max-vl:top-full max-vl:z-aboveOverlay max-vl:mt-1 max-vl:rounded-xl max-vl:bg-background max-vl:p-2.5'
                                : 'max-vl:hidden',
                        )}
                    >
                        {blogCategoryItems.map((blogCategory) => {
                            const isActive = activeItem === blogCategory.uuid;

                            return (
                                <Fragment key={blogCategory.uuid}>
                                    <BlogSignpostItem href={blogCategory.link} isActive={isActive}>
                                        <BlogSignpostIcon isActive={isActive} />
                                        {blogCategory.name}
                                    </BlogSignpostItem>
                                    {!!blogCategory.children?.length && (
                                        <Children activeItem={activeItem} blogCategory={blogCategory} itemLevel={1} />
                                    )}
                                </Fragment>
                            );
                        })}
                    </div>
                )}
            </div>
            {isBlogSignpostOpen && (
                <Overlay isHiddenOnDesktop isActive={isBlogSignpostOpen} onClick={() => setIsBlogSignpostOpen(false)} />
            )}
        </>
    );
};
