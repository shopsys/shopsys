import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { FooterContainer } from 'components/Layout/Footer/FooterContainer';

export const NewsletterFormPlaceholder: FC = () => (
    <FooterContainer className="border-t-0!">
        <div className="grid grid-cols-1 vl:grid-cols-[minmax(20rem,28rem)_minmax(0,32rem)] items-center vl:justify-center gap-4 vl:gap-12">
            <div className="flex flex-col gap-2">
                <Skeleton className="h-3 w-24 bg-white-alpha-200" />
                <Skeleton className="h-12 w-full max-w-md rounded-lg bg-white-alpha-100" />
            </div>

            <div className="flex w-full flex-col gap-2 lg:gap-3">
                <div className="flex items-start gap-2 lg:gap-3">
                    <Skeleton className="h-12 min-w-0 flex-1 rounded-lg bg-white-alpha-100" />
                    <Skeleton className="h-12 w-16 shrink-0 rounded-lg bg-white-alpha-100" />
                </div>
                <Skeleton className="h-4 w-64 bg-white-alpha-200" />
            </div>
        </div>
    </FooterContainer>
);
