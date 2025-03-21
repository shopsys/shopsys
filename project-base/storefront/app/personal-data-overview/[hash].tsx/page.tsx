import { PersonalDataDetailContent } from 'app/_components/Page/PersonalData/Detail/PersonalDataDetailContent';
import { getPersonalDataDetailQuery } from 'app/_queries/getPersonalDataDetailQuery';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { SkeletonPagePersonalDataOverview } from 'components/Blocks/Skeleton/SkeletonPagePersonalDataOverview';
import { Webline } from 'components/Layout/Webline/Webline';
import { Suspense } from 'react';

const PersonalDataOverviewByHashPage = async ({ params }: { params: Promise<{ hash: string }> }) => {
    const t = await getTranslation();
    const { hash } = await params;

    const { data: personalDataDetailData } = await getPersonalDataDetailQuery({
        hash,
    });

    if (!personalDataDetailData) {
        return (
            <Webline>
                <p className="my-28 text-center text-2xl">{t('Could not find personal data overview.')}</p>
            </Webline>
        );
    }

    return (
        <Suspense fallback={<SkeletonPagePersonalDataOverview />}>
            <PersonalDataDetailContent personalDataDetail={personalDataDetailData} />
        </Suspense>
    );
};

export default PersonalDataOverviewByHashPage;
