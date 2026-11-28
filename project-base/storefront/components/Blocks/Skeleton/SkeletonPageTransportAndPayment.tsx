import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { SkeletonModuleOrderAction } from 'components/Blocks/Skeleton/SkeletonModuleOrderAction';
import { Webline } from 'components/Layout/Webline/Webline';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const SkeletonPageTransportAndPayment: FC = () => {
    const { t } = useTranslation();

    return (
        <Webline width="xl">
            <Skeleton className="mx-auto mt-1 mb-5 flex h-16 vl:h-11 w-full max-w-210 rounded-card lg:mt-6 lg:mb-10" />

            <div className="grid vl:grid-cols-3 vl:gap-10">
                <div className="vl:col-span-2">
                    <Skeleton className="h-80 rounded-surface" />

                    <SkeletonModuleOrderAction buttonNext={t('Contact information')} />
                </div>

                <div className="vl:col-span-1">
                    <Skeleton className="h-96 rounded-surface" />
                </div>
            </div>
        </Webline>
    );
};
