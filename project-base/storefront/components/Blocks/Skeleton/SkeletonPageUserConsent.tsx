import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModulePageHero } from './SkeletonModulePageHero';

export const SkeletonPageUserConsent: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={2} />

        <Webline width="lg">
            <VerticalStack gap="sm">
                <SkeletonModulePageHero />

                <div className="mx-auto flex w-full max-w-2xl flex-col gap-5">
                    <div className="divide-y divide-border-less overflow-hidden rounded-xl border border-border-less bg-background-more">
                        {[0, 1, 2].map((index) => (
                            <div className="flex min-h-16 items-center justify-between gap-4 px-4 py-3" key={index}>
                                <Skeleton className="h-5 w-32" />

                                <Skeleton className="h-6 w-11 rounded-full" />
                            </div>
                        ))}
                    </div>

                    <div className="flex flex-col-reverse gap-2.5 sm:flex-row sm:justify-end">
                        <Skeleton className="h-9 w-full sm:w-24" />
                        <Skeleton className="h-9 w-full sm:w-24" />
                        <Skeleton className="h-9 w-full sm:w-24" />
                    </div>
                </div>
            </VerticalStack>
        </Webline>
    </>
);
