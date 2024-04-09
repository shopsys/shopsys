import { useFooterArticles } from './utils';
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const Footer = dynamic(() => import('./Footer').then((component) => component.Footer), {
    ssr: false,
});

const FooterPlaceholder = dynamic(() => import('./FooterPlaceholder').then((component) => component.FooterPlaceholder));

const dummyData = {
    phone: '+420 111 222 333',
    opening: 'Po - Út, 10 - 16 hod',
};

export const DeferredFooter = () => {
    const footerArticles = useFooterArticles();
    const shouldRender = useDeferredRender('footer');

    return shouldRender ? (
        <Footer footerArticles={footerArticles} opening={dummyData.opening} phone={dummyData.phone} />
    ) : (
        <FooterPlaceholder footerArticles={footerArticles} opening={dummyData.opening} phone={dummyData.phone} />
    );
};
