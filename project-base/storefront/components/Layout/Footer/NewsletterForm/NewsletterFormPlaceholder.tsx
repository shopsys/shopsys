import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { FooterContainer } from 'components/Layout/Footer/FooterContainer';

export const NewsletterFormPlaceholder: FC = () => (
    <FooterContainer className="bg-background-accent-less">
        <div className="vl:gap-44 grid grid-cols-1 items-center gap-5 lg:grid-cols-2">
            <Skeleton className="h-12 w-full lg:h-6 lg:w-80" />

            <div className="grid grid-cols-3 items-start gap-2 lg:gap-3">
                <Skeleton className="col-span-2 h-12 rounded-lg" />
                <Skeleton className="col-start-3 row-start-1 h-12 w-full rounded-lg" />
                <Skeleton className="col-span-3 col-start-1 row-start-2 mt-2 h-4 w-64" />
            </div>
        </div>
    </FooterContainer>
);
