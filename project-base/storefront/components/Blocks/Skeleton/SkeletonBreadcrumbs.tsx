import { BreadcrumbsSpan, breadcrumbsTwClass } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { createEmptyArray } from 'helpers/arrayUtils';
import { twMergeCustom } from 'helpers/twMerge';
import { Fragment } from 'react';
import Skeleton from 'react-loading-skeleton';
import { twJoin } from 'tailwind-merge';

type SkeletonBreadcrumbsProps = {
    count: number;
};

export const SkeletonBreadcrumbs: FC<SkeletonBreadcrumbsProps> = ({ count }) => (
    <div className={twMergeCustom('mb-8', breadcrumbsTwClass)}>
        {createEmptyArray(count).map((_, index) => (
            <Fragment key={index}>
                <Skeleton className="w-40" containerClassName={twJoin(index >= 1 && 'hidden lg:block')} />
                {index < count - 1 && <BreadcrumbsSpan>/</BreadcrumbsSpan>}
            </Fragment>
        ))}
    </div>
);
