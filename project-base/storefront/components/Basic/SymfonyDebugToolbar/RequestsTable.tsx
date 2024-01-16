import { ResponseInfo } from './helpers/types';
import { Button } from 'components/Forms/Button/Button';
import { twMergeCustom } from 'helpers/twMerge';
import useTranslation from 'next-translate/useTranslation';

type RequestsTableProps = {
    isVisible: boolean;
    responses: ResponseInfo[];
    hasResponses: boolean;
    reset: () => void;
};

export const RequestsTable: FC<RequestsTableProps> = ({ isVisible, responses, hasResponses, reset }) => {
    const { t } = useTranslation();

    if (!hasResponses) {
        return null;
    }

    return (
        <div
            className={twMergeCustom(
                'word-break absolute bottom-9 right-0 max-h-[480px] w-[40vw] overflow-y-auto overflow-x-hidden break-words bg-primaryLight p-3 text-greyDark',
                isVisible ? 'block' : 'hidden',
            )}
        >
            <div className="flex justify-between">
                <div className="text-lg font-bold">
                    {t('Number of requests')}: {responses.length}
                </div>
                <Button size="small" onClick={() => reset()}>
                    {t('Clear')}
                </Button>
            </div>
            <table className="min-w-full leading-normal">
                <thead>
                    <tr className="border-b border-greyDark">
                        <TableHeaderCell>#</TableHeaderCell>
                        <TableHeaderCell>{t('Profile')}</TableHeaderCell>
                        <TableHeaderCell>{t('Method')}</TableHeaderCell>
                        <TableHeaderCell>{t('Type')}</TableHeaderCell>
                        <TableHeaderCell>{t('Status')}</TableHeaderCell>
                        <TableHeaderCell>URL</TableHeaderCell>
                    </tr>
                </thead>
                <tbody>
                    {responses.map((response, index) => (
                        <tr key={index} className="border-b border-greyDark">
                            <TableCell>{index + 1}</TableCell>
                            <TableCell>
                                <TableLink href={response.profiler}>{response.token}</TableLink>
                            </TableCell>
                            <TableCell>{response.method}</TableCell>
                            <TableCell>{response.type}</TableCell>
                            <TableCell>{response.status}</TableCell>
                            <TableCell>
                                <TableLink href={response.url}>{response.url}</TableLink>
                            </TableCell>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
};

const TableHeaderCell: FC = ({ children }) => (
    <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-greyDark">{children}</th>
);

const TableCell: FC = ({ children }) => <td className="truncate px-5 py-2">{children}</td>;

const TableLink: FC<{ href: string }> = ({ children, href }) => (
    <a className="hover:text-primaryDarker" href={href} rel="noreferrer" target="_blank">
        {children}
    </a>
);
