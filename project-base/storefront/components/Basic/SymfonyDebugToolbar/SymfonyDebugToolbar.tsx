import { useRequests } from './symfonyDebugToolbarUtils';
import symfonyImage from '/public/images/symfony.svg';
import { Image } from 'components/Basic/Image/Image';
import { Button } from 'components/Forms/Button/Button';
import dynamic from 'next/dynamic';
import { useState } from 'react';

interface SymfonyDebugToolbarProps {
    tokenLinkHeader?: string;
    tokenHeader?: string;
}

const RequestsTable = dynamic(
    () =>
        import('components/Basic/SymfonyDebugToolbar/SymfonyDebugToolbarRequestsTable').then(
            (component) => component.RequestsTable,
        ),
    { ssr: false },
);

export const SymfonyDebugToolbar: FC<SymfonyDebugToolbarProps> = ({
    tokenHeader = 'x-debug-token',
    tokenLinkHeader = 'x-debug-token-link',
}) => {
    const [isTableDisplayed, setIsTableDisplayed] = useState(false);
    const { responses, reset } = useRequests(tokenHeader, tokenLinkHeader);

    return (
        <div
            className="fixed bottom-2 right-2 z-maximum flex flex-col items-end gap-2 rounded bg-backgroundMost p-3"
            onMouseEnter={() => setIsTableDisplayed(true)}
            onMouseLeave={() => setIsTableDisplayed(false)}
        >
            <div className="relative order-2 h-6 w-6">
                <Image alt="Symfony Logo" height={24} src={symfonyImage} width={24} />
                <span className="absolute -bottom-[5px] -right-[5px] flex h-4 w-4 items-center justify-center rounded-full bg-backgroundAccent text-xs font-bold leading-normal text-textInverted">
                    {responses.length}
                </span>
            </div>

            {!!responses.length && isTableDisplayed && (
                <div className="order-1 flex-col items-center justify-center gap-2">
                    <div className="flex items-center justify-between bg-tableBackgroundHeader p-3 text-tableTextHeader">
                        <div className="text-lg font-bold">Number of requests: {responses.length}</div>

                        <Button size="small" onClick={() => reset()}>
                            Clear
                        </Button>
                    </div>

                    <RequestsTable responses={responses} />
                </div>
            )}
        </div>
    );
};
