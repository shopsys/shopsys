import { useFooterArticles } from './footerUtils';
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const Footer = dynamic(() => import('./Footer').then((component) => ({
    default: component.Footer
})), {
    ssr: false,
});

const FooterPlaceholder = dynamic(() => import('./FooterPlaceholder').then((component) => ({
    default: component.FooterPlaceholder
})));

export const DeferredFooter = () => {
    const footerArticles = useFooterArticles();
    const shouldRender = useDeferredRender('footer');

    return shouldRender ? (
        <Footer footerArticles={footerArticles} />
    ) : (
        <FooterPlaceholder footerArticles={footerArticles} />
    );
};
