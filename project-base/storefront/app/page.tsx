import { SkeletonPageHome } from 'components/Blocks/Skeleton/SkeletonPageHome';
import { Suspense } from 'react';

export default async function HomePage() {
    return (
        <Suspense fallback={<SkeletonPageHome />}>
            <h1>HOME PAGE</h1>
            <p>This is the home page.</p>
        </Suspense>
    );
}
