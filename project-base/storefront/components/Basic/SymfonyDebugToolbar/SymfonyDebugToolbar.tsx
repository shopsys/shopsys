import { Image } from 'components/Basic/Image/Image';
import { Button } from 'components/Forms/Button/Button';
import dynamic from 'next/dynamic';
import symfonyImage from '/public/images/symfony.svg';
import { useRequests } from './symfonyDebugToolbarUtils';

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
    const { responses, reset } = useRequests(tokenHeader, tokenLinkHeader);

    return (
        <div className="group fixed right-2 bottom-2 z-maximum flex flex-col items-end gap-2 rounded-sm bg-background-most p-3">
            <div className="relative order-2 h-6 w-6">
                <Image alt="Symfony Logo" height={24} src={symfonyImage} width={24} />
                <span className="absolute -right-[5px] -bottom-[5px] flex h-4 w-4 items-center justify-center rounded-full bg-background-accent font-bold text-text-inverted text-xs leading-normal">
                    {responses.length}
                </span>
            </div>

            {!!responses.length && (
                <div className="order-1 hidden flex-col items-center justify-center gap-2 group-focus-within:flex group-hover:flex">
                    <div className="flex items-center justify-between bg-table-bg-header p-3 text-table-bg-default">
                        <div className="mr-2 font-bold text-lg">Number of requests: {responses.length}</div>

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
