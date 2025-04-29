'use client';

import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonPageRegistrationApp: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={2} />

        <div className="mx-auto flex w-full max-w-3xl flex-col">
            <Skeleton className="h-10 w-72" />
            <Skeleton className="h-[1000px]" />
        </div>
    </Webline>
);
