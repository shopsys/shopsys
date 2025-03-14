import { PersonalDataExportContent } from 'app/_components/Page/PersonalData/Export/PersonalDataExportContent';
import { getPersonalDataTextQuery } from 'app/_queries/getPersonalDataTextQuery';

const PersonalDataExportPage = async () => {
    const { data: personalData } = await getPersonalDataTextQuery();

    // TODO: add gtm
    // const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    // useGtmPageViewEvent(gtmStaticPageViewEvent);

    return <PersonalDataExportContent contentSiteText={personalData?.personalDataPage?.exportSiteContent} />;
};

export default PersonalDataExportPage;
