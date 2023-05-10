import { Footer } from './Footer/Footer';
import { NewsletterForm } from './Footer/NewsletterForm';
import { Header } from './Header/Header';
import { Navigation } from './Header/Navigation/Navigation';
import { NotificationBars } from './NotificationBars/NotificationBars';
import { Webline } from './Webline/Webline';
import { SeoMeta } from 'components/Basic/Head/SeoMeta';
import { Adverts } from 'components/Blocks/Adverts/Adverts';

type CommonLayoutProps = {
    title?: string | null;
    description?: string | null;
};

export const CommonLayout: FC<CommonLayoutProps> = ({ children, description, title }) => (
    <>
        <SeoMeta defaultTitle={title} defaultDescription={description} />
        <NotificationBars />
        <Webline type="colored" style={{ marginBottom: '32px', position: 'relative' }}>
            <Header />
            <Navigation />
        </Webline>
        <Adverts positionName="header" withGapBottom withWebline />
        {children}
        <Adverts positionName="footer" withGapBottom withGapTop withWebline />
        <Webline type="light">
            <NewsletterForm />
        </Webline>
        <Webline type="dark">
            <Footer />
        </Webline>
    </>
);
