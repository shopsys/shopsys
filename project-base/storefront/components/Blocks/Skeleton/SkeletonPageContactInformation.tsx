import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { SkeletonModuleOrderAction } from 'components/Blocks/Skeleton/SkeletonModuleOrderAction';
import { Webline } from 'components/Layout/Webline/Webline';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const SkeletonPageContactInformation: FC = () => {
    const { t } = useTranslation();

    return (
        <Webline width="xl">
            <Skeleton className="mx-auto mt-1 mb-5 flex h-16 vl:h-11 w-full max-w-210 rounded-xl lg:mt-6 lg:mb-10" />

            <div className="grid vl:grid-cols-3 vl:gap-10">
                <div className="vl:col-span-2 flex flex-col gap-5">
                    <Skeleton className="h-80 rounded-xl" />

                    <Skeleton className="h-160 rounded-xl" />

                    <Skeleton className="h-20 rounded-xl" />

                    <Skeleton className="h-32 rounded-xl" />

                    <Skeleton className="mx-5 vl:mx-20 h-6 w-1/3" />

                    <div className="mx-5 vl:mx-20 flex flex-col gap-1">
                        <Skeleton className="h-6" />
                        <Skeleton className="h-6 w-2/3" />
                    </div>

                    <SkeletonModuleOrderAction buttonNext={t('Submit order')} />
                </div>

                <div className="vl:col-span-1">
                    <Skeleton className="h-64 rounded-xl" />
                </div>
            </div>
        </Webline>
    );
};
