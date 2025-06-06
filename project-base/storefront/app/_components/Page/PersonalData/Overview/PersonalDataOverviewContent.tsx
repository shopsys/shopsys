import { PersonalDataOverviewForm } from './PersonalDataOverviewForm';
import { UserText } from 'app/_components/Basic/UserText/UserText';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { Webline } from 'components/Layout/Webline/Webline';

type PersonalDataOverviewContentProps = {
    contentSiteText: string | null | undefined;
};

export const PersonalDataOverviewContent = async ({ contentSiteText }: PersonalDataOverviewContentProps) => {
    const t = await getTranslation();

    return (
        <Webline className="flex flex-col items-center">
            <h1 className="w-full max-w-3xl">{t('Personal data overview')}</h1>
            {!!contentSiteText && (
                <div className="max-w-3xl [&_section]:mb-5 [&_section]:block [&_section]:text-justify">
                    <UserText htmlContent={contentSiteText} />
                </div>
            )}

            <PersonalDataOverviewForm />
        </Webline>
    );
};
