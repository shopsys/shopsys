import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { useEffect, useRef } from 'react';
import { twJoin } from 'tailwind-merge';
import { ListedBlogCategoryRecursiveType } from 'types/blogCategory';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { BlogSignpostCategoryLink } from './BlogSignpostCategoryLink';
import { BlogSignpostItem } from './BlogSignpostItem';
import { getBlogSignpostPanelId } from './blogSignpostUtils';

type BlogSignpostPanelProps = {
    activeArticleCategoryPathUuids: string[];
    activeItem: string;
    activePanelPathUuids: string[];
    activePanelUuid: string;
    blogCategory: ListedBlogCategoryRecursiveType;
    isRoot?: boolean;
    panelUuidToFocus?: string;
    parentPanelUuid?: string;
    previousPanelUuid?: string;
    onPanelChange: (uuid: string) => void;
    onPanelFocused: (uuid: string) => void;
    onPanelTransitionEnd: (uuid: string) => void;
};

export const BlogSignpostPanel: FC<BlogSignpostPanelProps> = ({
    activeArticleCategoryPathUuids,
    activeItem,
    activePanelPathUuids,
    activePanelUuid,
    blogCategory,
    isRoot = false,
    panelUuidToFocus,
    parentPanelUuid,
    previousPanelUuid,
    onPanelChange,
    onPanelFocused,
    onPanelTransitionEnd,
}) => {
    const { t } = useTranslation();
    const panelRef = useRef<HTMLDivElement>(null);
    const isActive = activePanelUuid === blogCategory.uuid;
    const isAncestorOfActivePanel = activePanelPathUuids.includes(blogCategory.uuid);
    const isCurrent = activeItem === blogCategory.uuid;
    const isTransitioningPanel = previousPanelUuid === blogCategory.uuid;

    useEffect(() => {
        if (isActive && panelUuidToFocus === blogCategory.uuid) {
            const focusTarget = panelRef.current?.querySelector<HTMLElement>('button, a');

            if (focusTarget) {
                focusTarget.focus({ preventScroll: true });
                onPanelFocused(blogCategory.uuid);
            }
        }
    }, [blogCategory.uuid, isActive, onPanelFocused, panelUuidToFocus]);

    return (
        <>
            <div
                ref={panelRef}
                aria-hidden={!isActive || undefined}
                id={getBlogSignpostPanelId(blogCategory.uuid)}
                className={twJoin(
                    'flex flex-col gap-2 bg-background-default motion-safe:transition-transform motion-safe:duration-300 motion-safe:ease-out',
                    isActive ? 'relative translate-x-0' : 'pointer-events-none absolute inset-x-0 top-0',
                    !isActive && (isAncestorOfActivePanel ? '-translate-x-full' : 'translate-x-full'),
                    !isActive && !isTransitioningPanel && 'invisible',
                )}
                inert={!isActive || undefined}
                onTransitionEnd={(event) => {
                    if (event.target === event.currentTarget && isTransitioningPanel) {
                        onPanelTransitionEnd(blogCategory.uuid);
                    }
                }}
            >
                {!isRoot && parentPanelUuid && (
                    <>
                        <button
                            className="group flex w-fit cursor-pointer items-center gap-1 px-2 py-1 font-secondary text-sm text-text-less transition-colors hover:text-link-hovered"
                            type="button"
                            onClick={() => onPanelChange(parentPanelUuid)}
                        >
                            <ArrowIcon className="size-4 rotate-90 transition-transform group-hover:-translate-x-0.5" />
                            {t('Back')}
                        </button>

                        <span className="min-w-0 truncate px-3 pt-1 font-secondary font-semibold text-text-less text-xs uppercase">
                            {blogCategory.name}
                        </span>
                    </>
                )}

                {blogCategory.children?.map((blogCategoryChild) => (
                    <BlogSignpostItem
                        key={blogCategoryChild.uuid}
                        activeArticleCategoryPathUuids={activeArticleCategoryPathUuids}
                        activeItem={activeItem}
                        blogCategory={blogCategoryChild}
                        onPanelChange={onPanelChange}
                    />
                ))}

                <div className="mt-2 border-border-less border-t pt-3">
                    <BlogSignpostCategoryLink
                        blogCategory={blogCategory}
                        isCurrent={isCurrent}
                        label={isRoot ? t('All articles') : `${t('All articles')}: ${blogCategory.name}`}
                        variant="overview"
                    />
                </div>
            </div>

            {blogCategory.children?.map(
                (blogCategoryChild) =>
                    !!blogCategoryChild.children?.length && (
                        <BlogSignpostPanel
                            key={blogCategoryChild.uuid}
                            activeArticleCategoryPathUuids={activeArticleCategoryPathUuids}
                            activeItem={activeItem}
                            activePanelPathUuids={activePanelPathUuids}
                            activePanelUuid={activePanelUuid}
                            blogCategory={blogCategoryChild}
                            panelUuidToFocus={panelUuidToFocus}
                            parentPanelUuid={blogCategory.uuid}
                            previousPanelUuid={previousPanelUuid}
                            onPanelChange={onPanelChange}
                            onPanelFocused={onPanelFocused}
                            onPanelTransitionEnd={onPanelTransitionEnd}
                        />
                    ),
            )}
        </>
    );
};
