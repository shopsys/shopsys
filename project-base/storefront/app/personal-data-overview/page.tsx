import { PersonalDataOverviewContent } from 'app/_components/Page/PersonalData/Overview/PersonalDataOverviewContent';
import { getPersonalDataTextQuery } from 'app/_queries/getPersonalDataTextQuery';

export const dynamic = 'force-dynamic';

const PersonalDataOverviewPage = async () => {
    const { data: personalData } = await getPersonalDataTextQuery();

    return <PersonalDataOverviewContent contentSiteText={personalData?.personalDataPage?.displaySiteContent} />;
};

export default PersonalDataOverviewPage;
