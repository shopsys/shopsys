'use client';

import { CategoryDetailProvider } from 'components/providers/CategoryDetailProvider';
import { TypeCategoryDetailFragment } from 'graphql/requests/categories/fragments/CategoryDetailFragment.ssr';
import { useRef } from 'react';

type CategoryDetailContentProps = {
    categoryDetail: TypeCategoryDetailFragment;
    header?: React.ReactNode;
};

export const CategoryDetailContent: FC<CategoryDetailContentProps> = ({ children, categoryDetail }) => {
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);

    return (
        <div ref={paginationScrollTargetRef}>
            <CategoryDetailProvider
                categoryDetailUuid={categoryDetail.uuid}
                paginationScrollTargetRef={paginationScrollTargetRef}
            >
                {children}
            </CategoryDetailProvider>
        </div>
    );
};
