import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const NewsletterForm = dynamic(() => import('./NewsletterForm').then((component) => ({
    default: component.NewsletterForm
})), {
    ssr: false,
});

export const DeferredNewsletterForm: FC = () => {
    const shouldRender = useDeferredRender('newsletter');

    return shouldRender ? <NewsletterForm /> : null;
};
