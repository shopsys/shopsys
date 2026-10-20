import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { TypeAdditionalServiceFragment } from 'graphql/requests/additionalServices/fragments/AdditionalServiceFragment.generated';

type SkeletonModuleProductDetailAdditionalServicesProps = {
    additionalServices: Pick<TypeAdditionalServiceFragment, 'uuid' | 'deliveryDaysExtension'>[];
};

export const SkeletonModuleProductDetailAdditionalServices: FC<SkeletonModuleProductDetailAdditionalServicesProps> = ({
    additionalServices,
}) => (
    <div className="flex min-w-0 flex-col gap-3">
        <div className="flex items-center gap-2">
            <Skeleton className="size-4 shrink-0" />
            <Skeleton className="h-5 w-36" />
        </div>
        <div className="flex flex-col gap-4 md:gap-2">
            {additionalServices.slice(0, 4).map((service) => (
                <div className="flex min-h-10 items-center gap-2" key={service.uuid}>
                    <Skeleton className="size-5 shrink-0" />
                    <Skeleton className="size-8 shrink-0" />
                    <div className="flex min-w-0 flex-1 flex-col gap-1">
                        <Skeleton className="h-5 w-4/5" />
                        {(service.deliveryDaysExtension ?? 0) > 0 && <Skeleton className="h-4 w-3/5" />}
                    </div>
                </div>
            ))}
        </div>
    </div>
);
