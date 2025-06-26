'use client';

import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonLayout: FC = () => (
    <section className="flex flex-col gap-4">
        <Skeleton className="h-[calc(140px+2.25rem)] rounded-none" />
        <Webline>
            <Skeleton className="h-full min-h-[80vh] rounded-xl" />
        </Webline>
    </section>
);
