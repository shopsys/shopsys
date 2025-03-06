'use client';

import { BreadcrumbsSpan, breadcrumbsTwClass } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import { Fragment } from 'react';
import Skeleton from 'react-loading-skeleton';
import { twJoin } from 'tailwind-merge';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';
import { twMergeCustom } from 'utils/twMerge';

type SkeletonModuleBreadcrumbsProps = {
    count: number;
};

export const SkeletonModuleBreadcrumbs: FC<SkeletonModuleBreadcrumbsProps> = ({ count }) => (
    <Webline className={twMergeCustom(breadcrumbsTwClass)}>
        {createEmptyArray(count).map((_, index) => (
            <Fragment key={index}>
                <Skeleton containerClassName={twJoin('w-28', index >= 1 && 'hidden lg:block')} />
                {index < count - 1 && <BreadcrumbsSpan>/</BreadcrumbsSpan>}
            </Fragment>
        ))}
    </Webline>
);
