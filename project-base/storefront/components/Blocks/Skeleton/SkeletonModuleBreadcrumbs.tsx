import { BreadcrumbsSpan, breadcrumbsTwClass } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { Fragment } from 'react';
import Skeleton from 'react-loading-skeleton';
import { twJoin } from 'tailwind-merge';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';
import { twMergeCustom } from 'utils/twMerge';

type SkeletonModuleBreadcrumbsProps = {
    count: number;
};

export const SkeletonModuleBreadcrumbs: FC<SkeletonModuleBreadcrumbsProps> = ({ count }) => (
    <div className={twMergeCustom('mb-8', breadcrumbsTwClass)}>
        {createEmptyArray(count).map((_, index) => (
            <Fragment key={index}>
                <Skeleton className="w-40" containerClassName={twJoin(index >= 1 && 'hidden lg:block')} />
                {index < count - 1 && <BreadcrumbsSpan>/</BreadcrumbsSpan>}
            </Fragment>
        ))}
    </div>
);
