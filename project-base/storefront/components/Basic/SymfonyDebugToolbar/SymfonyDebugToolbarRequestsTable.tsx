import { ResponseInfo } from './helpers';
import { RefObject, createRef, useEffect } from 'react';

type RequestsTableProps = {
    responses: ResponseInfo[];
};

export const RequestsTable: FC<RequestsTableProps> = ({ responses }) => {
    const responsesRefs: Array<RefObject<HTMLTableRowElement>> = Array(responses.length)
        .fill(null)
        .map(() => createRef());

    useEffect(() => {
        responsesRefs[responsesRefs.length - 1].current?.scrollIntoView();
    }, [responsesRefs]);

    return (
        <div className="max-h-[40vh] overflow-y-scroll bg-creamWhite">
            <table>
                <thead>
                    <tr className="border-b border-greyDark">
                        <TableHeaderCell>#</TableHeaderCell>
                        <TableHeaderCell>Profile</TableHeaderCell>
                        <TableHeaderCell>Type</TableHeaderCell>
                        <TableHeaderCell>Status</TableHeaderCell>
                        <TableHeaderCell>URL</TableHeaderCell>
                    </tr>
                </thead>
                <tbody>
                    {responses.map((response, index) => (
                        <tr key={index} className="border-b border-greyDark" ref={responsesRefs[index]}>
                            <TableCell>{index + 1}</TableCell>
                            <TableCell>
                                <TableLink href={response.profiler}>{response.token}</TableLink>
                            </TableCell>
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
