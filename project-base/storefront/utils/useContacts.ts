import useTranslation from 'next-translate/useTranslation';

export const useContacts = (): { phone: string; openingHours: string; email: string; emailSubtitle: string } => {
    const { t } = useTranslation();

    // TODO PRG
    const dummyData = {
        phone: '+420 777 123 654',
        openingHours: t('Mo-Fr 8:00-16:30'),
        email: 'info@shopsys.cz',
        emailSubtitle: t('Response within 24h'),
    };

    return {
        phone: dummyData.phone,
        openingHours: dummyData.openingHours,
        email: dummyData.email,
        emailSubtitle: dummyData.emailSubtitle,
    };
};
