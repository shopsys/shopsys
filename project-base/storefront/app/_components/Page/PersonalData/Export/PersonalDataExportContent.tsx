import { PersonalDataExportForm } from './PersonalDataExportForm';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { UserText } from 'app/_components/Basic/UserText/UserText';
import { Webline } from 'components/Layout/Webline/Webline';

type PersonalDataExportContentProps = {
    contentSiteText: string | undefined;
};

export const PersonalDataExportContent = async ({ contentSiteText }: PersonalDataExportContentProps) => {
    const t = await getTranslation();

    return (
        <Webline className="flex flex-col items-center">
            <h1 className="w-full max-w-3xl">{t('Personal data export')}</h1>
            {!!contentSiteText && (
                <div className="mb-5 block max-w-3xl text-justify">
                    <UserText htmlContent={contentSiteText} />
                </div>
            )}
            <PersonalDataExportForm />
        </Webline>
    );
};
