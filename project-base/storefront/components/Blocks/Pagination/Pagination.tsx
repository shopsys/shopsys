import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { PAGE_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
import { useMediaMin } from 'hooks/ui/useMediaMin';
import { usePagination } from 'hooks/ui/usePagination';
import { useQueryParams } from 'hooks/useQueryParams';
import { useRouter } from 'next/router';
import { Fragment, RefObject } from 'react';
import { twJoin } from 'tailwind-merge';

type PaginationProps = {
    totalCount: number;
    containerWrapRef: RefObject<HTMLDivElement> | null;
};

const TEST_IDENTIFIER = 'blocks-pagination';
export const DEFAULT_PAGE_SIZE = 9;

export const Pagination: FC<PaginationProps> = ({ totalCount, containerWrapRef }) => {
    const router = useRouter();
    const isDesktop = useMediaMin('sm');
    const { currentPage } = useQueryParams();
    const paginationButtons = usePagination(totalCount, currentPage, !isDesktop, DEFAULT_PAGE_SIZE);

    const asPathWithoutQueryParams = router.asPath.split('?')[0];
    const queryParamsWithoutPage = { ...router.query };
    delete queryParamsWithoutPage.slugType;
    delete queryParamsWithoutPage[PAGE_QUERY_PARAMETER_NAME];

    const onChangePage = () => {
        if (containerWrapRef !== null && containerWrapRef.current !== null) {
            containerWrapRef.current.scrollIntoView();
        }
    };

    if (paginationButtons === null || paginationButtons.length === 1) {
        return null;
    }

    return (
        <div className="flex w-full justify-center vl:justify-end ">
            <div className="my-3 flex justify-center gap-1 vl:mr-5" data-testid={TEST_IDENTIFIER}>
                {paginationButtons.map((pageNumber, index, array) => (
                    <Fragment key={pageNumber}>
                        {isDotKey(array[index - 1] ?? null, pageNumber) && (
                            <PaginationButton isDotButton>&#8230;</PaginationButton>
                        )}
                        {currentPage === pageNumber ? (
                            <PaginationButton dataTestId={TEST_IDENTIFIER + '-' + pageNumber} isActive>
                                {pageNumber}
                            </PaginationButton>
                        ) : (
                            <ExtendedNextLink
                                href={{
                                    pathname: asPathWithoutQueryParams,
                                    query: {
                                        ...queryParamsWithoutPage,
                                        ...(pageNumber !== 1 ? { page: pageNumber } : {}),
                                    },
                                }}
                                passHref
                                shallow
                                scroll={false}
                                type="static"
                            >
                                <PaginationButton
                                    dataTestId={TEST_IDENTIFIER + '-' + pageNumber}
                                    onClick={onChangePage}
                                >
                                    {pageNumber}
                                </PaginationButton>
                            </ExtendedNextLink>
                        )}
                    </Fragment>
                ))}
            </div>
        </div>
    );
};

const isDotKey = (prevPage: number | null, currentPage: number): boolean => {
    return prevPage !== null && prevPage !== currentPage - 1;
};

type PaginationButtonProps = {
    isActive?: boolean;
    isDotButton?: boolean;
    href?: string;
    onClick?: () => void;
};

const PaginationButton: FC<PaginationButtonProps> = ({
    children,
    dataTestId,
    isActive,
    isDotButton,
    href,
    onClick,
}) => {
    const button = (
        <a
            className={twJoin(
                'flex h-11 w-11 items-center justify-center rounded border border-white bg-white font-bold no-underline hover:no-underline',
                isActive && 'border-none bg-orange hover:cursor-default',
                isDotButton && 'hover:cursor-default',
            )}
            onClick={onClick}
            data-testid={dataTestId}
        >
            {children}
        </a>
    );

    if (href !== undefined) {
        return (
            <ExtendedNextLink href={href} passHref shallow scroll={false} type="static">
                {button}
            </ExtendedNextLink>
        );
    }

    return button;
};
