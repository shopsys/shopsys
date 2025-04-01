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
            className="z-maximum bg-background-most fixed right-2 bottom-2 flex flex-col items-end gap-2 rounded-sm p-3"
            onMouseEnter={() => setIsTableDisplayed(true)}
            onMouseLeave={() => setIsTableDisplayed(false)}
        >
            <div className="relative order-2 h-6 w-6">
                <Image alt="Symfony Logo" height={24} src={symfonyImage} width={24} />
                <span className="bg-background-accent text-text-inverted absolute -right-[5px] -bottom-[5px] flex h-4 w-4 items-center justify-center rounded-full text-xs leading-normal font-bold">
                    {responses.length}
                </span>
            </div>

            {!!responses.length && isTableDisplayed && (
                <div className="order-1 flex-col items-center justify-center gap-2">
                    <div className="bg-table-bg-header text-table-bg-default flex items-center justify-between p-3">
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
