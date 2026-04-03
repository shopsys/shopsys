import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';
import { NewsletterFormPlaceholder } from './NewsletterFormPlaceholder';

const NewsletterForm = dynamic(() => import('./NewsletterForm').then((component) => component.NewsletterForm), {
    ssr: false,
});

export const DeferredNewsletterForm: FC = () => {
    const shouldRender = useDeferredRender('newsletter');

    return shouldRender ? <NewsletterForm /> : <NewsletterFormPlaceholder />;
};
