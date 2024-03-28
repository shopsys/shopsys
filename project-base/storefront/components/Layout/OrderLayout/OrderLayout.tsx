import { SeoMeta } from 'components/Basic/Head/SeoMeta';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { Footer } from 'components/Layout/Footer/Footer';
import { Header } from 'components/Layout/Header/Header';
import { NotificationBars } from 'components/Layout/NotificationBars/NotificationBars';
import { Webline } from 'components/Layout/Webline/Webline';
import useTranslation from 'next-translate/useTranslation';

export const OrderLayout: FC = ({ children }) => {
    const { t } = useTranslation();

    return (
        <>
            <SeoMeta defaultTitle={t('Order')} />

            <NotificationBars />

            <Webline className="relative mb-8" type="colored">
                <Header simpleHeader />
            </Webline>

            <Adverts withGapBottom withWebline positionName="header" />

            <Webline>{children}</Webline>

            <Adverts withGapBottom withWebline positionName="footer" />

            <Webline type="dark">
                <Footer simpleFooter />
            </Webline>
        </>
    );
};
