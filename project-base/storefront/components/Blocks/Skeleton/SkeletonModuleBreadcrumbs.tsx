import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { BreadcrumbsSpan, breadcrumbsTwClass } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import { Fragment } from 'react';
import { twJoin } from 'tailwind-merge';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';
import { twMergeCustom } from 'utils/twMerge';

type SkeletonModuleBreadcrumbsProps = {
    count: number;
};

export const SkeletonModuleBreadcrumbs: FC<SkeletonModuleBreadcrumbsProps> = ({ count }) => (
    <Webline className={twMergeCustom('mb-4', breadcrumbsTwClass)}>
        {createEmptyArray(count).map((_, index) => (
            <Fragment key={index}>
                <Skeleton className={twJoin('h-4 w-20 rounded-sm', index >= 1 && 'hidden lg:block')} />

                {index < count - 1 && <BreadcrumbsSpan>/</BreadcrumbsSpan>}
            </Fragment>
        ))}
    </Webline>
);
