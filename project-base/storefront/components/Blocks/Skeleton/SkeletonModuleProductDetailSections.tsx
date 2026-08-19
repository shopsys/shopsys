import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';
import { SkeletonModuleProductSlider } from './SkeletonModuleProductSlider';

const SkeletonSectionHeading: FC = () => <Skeleton className="mb-4 h-8 w-32" />;

export const SkeletonModuleProductDetailSections: FC = () => (
    <div>
        <Webline className="flex gap-3 overflow-hidden py-4">
            <Skeleton className="h-9 w-20 shrink-0 rounded-full" />
            <Skeleton className="h-9 w-24 shrink-0 rounded-full" />
            <Skeleton className="h-9 w-28 shrink-0 rounded-full" />
            <Skeleton className="h-9 w-36 shrink-0 rounded-full" />
        </Webline>

        <VerticalStack gap="lg">
            <Webline width="vl">
                <SkeletonSectionHeading />

                <div className="flex flex-col gap-5">
                    <div className="flex flex-col gap-2">
                        <Skeleton className="h-5" />
                        <Skeleton className="h-5" />
                        <Skeleton className="h-5 w-5/6" />
                        <Skeleton className="h-5 w-3/5" />
                    </div>

                    <div className="flex flex-col gap-2">
                        <Skeleton className="h-5" />
                        <Skeleton className="h-5" />
                        <Skeleton className="h-5" />
                        <Skeleton className="h-5 w-11/12" />
                        <Skeleton className="h-5 w-2/3" />
                    </div>

                    <div className="flex flex-col gap-2">
                        <Skeleton className="h-5" />
                        <Skeleton className="h-5 w-4/5" />
                        <Skeleton className="h-5 w-1/2" />
                    </div>
                </div>
            </Webline>

            <Webline width="vl">
                <SkeletonSectionHeading />

                <div className="overflow-hidden rounded-xl border border-border-less bg-skeleton-less">
                    {createEmptyArray(4).map((_, index) => (
                        <div
                            key={index}
                            className="flex flex-col border-border-less border-b last:border-b-0 md:grid md:grid-cols-[42%_58%]"
                        >
                            <div className="px-4 pt-4 pb-1 md:border-border-less md:border-r md:px-6 md:py-4">
                                <Skeleton className="h-5 w-1/2" />
                            </div>

                            <div className="bg-background-default px-4 pt-0 pb-4 md:px-6 md:py-4">
                                <Skeleton className="h-5 w-2/3" />
                            </div>
                        </div>
                    ))}
                </div>
            </Webline>

            <Webline width="vl">
                <SkeletonSectionHeading />

                <div className="grid grid-cols-1 gap-3 lg:grid-cols-2">
                    {createEmptyArray(4).map((_, index) => (
                        <div
                            key={index}
                            className="flex items-center justify-between gap-5 rounded-xl bg-skeleton-less px-5 py-2.5"
                        >
                            <div className="flex w-2/3 flex-col gap-2">
                                <Skeleton className="h-5 w-3/4" />
                                <Skeleton className="h-4 w-1/3" />
                            </div>

                            <Skeleton className="size-6 shrink-0" />
                        </div>
                    ))}
                </div>
            </Webline>

            <Webline>
                <SkeletonModuleProductSlider />
            </Webline>
        </VerticalStack>
    </div>
);
