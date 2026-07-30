import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { Button } from 'components/Forms/Button/Button';
import { useCallback, useEffect, useMemo, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { ListedBlogCategoryRecursiveType } from 'types/blogCategory';
import { findActiveBlogCategoryPath } from 'utils/blogCategory/findActiveBlogCategoryPath';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { BlogSignpostPanel } from './BlogSignpostPanel';

type BlogSignpostProps = {
    activeItem: string;
    blogCategoryItems?: ListedBlogCategoryRecursiveType[];
};

export const BlogSignpost: FC<BlogSignpostProps> = ({ blogCategoryItems, activeItem }) => {
    const { t } = useTranslation();
    const [isBlogSignpostOpen, setIsBlogSignpostOpen] = useState(false);
    const activeArticleCategoryPathUuids = useMemo(
        () => findActiveBlogCategoryPath(blogCategoryItems, activeItem),
        [activeItem, blogCategoryItems],
    );
    const mainBlogCategory = blogCategoryItems?.[0];
    const initialPanelUuid = useMemo(() => {
        if (!mainBlogCategory) {
            return undefined;
        }

        const activeBlogCategory = findBlogCategoryByUuid(blogCategoryItems, activeItem);

        if (activeBlogCategory?.children?.length) {
            return activeBlogCategory.uuid;
        }

        return activeArticleCategoryPathUuids[activeArticleCategoryPathUuids.length - 2] ?? mainBlogCategory.uuid;
    }, [activeArticleCategoryPathUuids, activeItem, blogCategoryItems, mainBlogCategory]);
    const [activePanelUuid, setActivePanelUuid] = useState(initialPanelUuid);
    const [previousPanelUuid, setPreviousPanelUuid] = useState<string>();
    const [panelUuidToFocus, setPanelUuidToFocus] = useState<string>();
    const activePanelPathUuids = useMemo(
        () => findActiveBlogCategoryPath(blogCategoryItems, activePanelUuid ?? ''),
        [activePanelUuid, blogCategoryItems],
    );

    useEffect(() => {
        setActivePanelUuid(initialPanelUuid);
        setPreviousPanelUuid(undefined);
    }, [initialPanelUuid]);

    const handlePanelChange = (panelUuid: string) => {
        setPreviousPanelUuid(activePanelUuid);
        setActivePanelUuid(panelUuid);
        setPanelUuidToFocus(panelUuid);
    };
    const handlePanelTransitionEnd = useCallback((panelUuid: string) => {
        setPreviousPanelUuid((currentPanelUuid) => (currentPanelUuid === panelUuid ? undefined : currentPanelUuid));
    }, []);
    const handlePanelFocused = useCallback((panelUuid: string) => {
        setPanelUuidToFocus((currentPanelUuid) => (currentPanelUuid === panelUuid ? undefined : currentPanelUuid));
    }, []);

    if (!mainBlogCategory || !activePanelUuid) {
        return null;
    }

    return (
        <>
            <div className="relative flex flex-col gap-y-2.5">
                <div className="cursor-pointer xl:cursor-text">
                    <Button
                        aria-controls="blog-signpost-navigation"
                        aria-expanded={isBlogSignpostOpen}
                        variant="secondary"
                        className={twJoin(
                            'relative w-full justify-between text-md!',
                            'xl:pointer-events-none xl:bg-transparent xl:p-0 xl:font-semibold xl:text-text-default xl:outline-hidden',
                            'max-xl:py-2.5 max-xl:font-default',
                            isBlogSignpostOpen && 'max-xl:z-aboveOverlay',
                        )}
                        onClick={() => setIsBlogSignpostOpen(!isBlogSignpostOpen)}
                    >
                        {t('Browse by topic')}
                        <ArrowIcon
                            className={twJoin('size-6 transition-all xl:hidden', isBlogSignpostOpen && 'rotate-180')}
                        />
                    </Button>
                </div>

                <nav
                    aria-label={t('Browse by topic')}
                    id="blog-signpost-navigation"
                    className={twJoin(
                        'w-full rounded-xl p-4 shadow-[inset_0_0_0_1px] shadow-border-less',
                        isBlogSignpostOpen
                            ? 'max-xl:absolute max-xl:top-full max-xl:z-aboveOverlay max-xl:mt-1 max-xl:max-h-[70dvh] max-xl:overflow-y-auto max-xl:rounded-2xl max-xl:bg-background-default max-xl:p-5'
                            : 'max-xl:hidden',
                    )}
                >
                    <div className="relative overflow-clip">
                        <BlogSignpostPanel
                            isRoot
                            activeArticleCategoryPathUuids={activeArticleCategoryPathUuids}
                            activeItem={activeItem}
                            activePanelPathUuids={activePanelPathUuids}
                            activePanelUuid={activePanelUuid}
                            blogCategory={mainBlogCategory}
                            panelUuidToFocus={panelUuidToFocus}
                            previousPanelUuid={previousPanelUuid}
                            onPanelChange={handlePanelChange}
                            onPanelFocused={handlePanelFocused}
                            onPanelTransitionEnd={handlePanelTransitionEnd}
                        />
                    </div>
                </nav>
            </div>

            {isBlogSignpostOpen && (
                <Overlay isHiddenOnDesktop isActive={isBlogSignpostOpen} onClick={() => setIsBlogSignpostOpen(false)} />
            )}
        </>
    );
};

const findBlogCategoryByUuid = (
    blogCategories: ListedBlogCategoryRecursiveType[] | undefined,
    uuid: string,
): ListedBlogCategoryRecursiveType | undefined => {
    for (const blogCategory of blogCategories ?? []) {
        if (blogCategory.uuid === uuid) {
            return blogCategory;
        }

        const foundBlogCategory = findBlogCategoryByUuid(blogCategory.children, uuid);

        if (foundBlogCategory) {
            return foundBlogCategory;
        }
    }

    return undefined;
};
