'use client';

import { ReactNode } from 'react';
import { twJoin } from 'tailwind-merge';
import { useMediaMin } from 'utils/ui/useMediaMin';

export type BlogPreviewArticlesProps = {
    BlogPreviewMainComponent: ReactNode;
    BlogPreviewSideComponent: ReactNode;
    BlogPreviewMobileComponent: ReactNode;
};

export function BlogPreviewArticles({
    BlogPreviewMainComponent,
    BlogPreviewSideComponent,
    BlogPreviewMobileComponent,
}: BlogPreviewArticlesProps) {
    const isDesktop = useMediaMin('vl');

    return (
        <div
            className={twJoin(
                'vl:flex vl:justify-between vl:gap-16 hide-scrollbar grid snap-x snap-mandatory grid-flow-col gap-5 overflow-x-auto overscroll-x-contain',
                'auto-cols-[60%] md:auto-cols-[40%] lg:auto-cols-[30%]',
            )}
        >
            {isDesktop ? (
                <>
                    {BlogPreviewMainComponent}
                    {BlogPreviewSideComponent}
                </>
            ) : (
                <>{BlogPreviewMobileComponent}</>
            )}
        </div>
    );
}
