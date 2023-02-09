import { Heading, HeadingProps } from './Heading';
import { DEFAULT_PAGE_SIZE } from 'components/Blocks/Pagination/Pagination';
import { parsePageNumberFromQuery } from 'helpers/pagination/parsePageNumberFromQuery';
import { PAGE_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useMediaMin } from 'hooks/ui/useMediaMin';
import { usePagination } from 'hooks/ui/usePagination';
import { useRouter } from 'next/router';
import { FC } from 'react';

type HeadingPaginatedProps = HeadingProps & {
    totalCount: number;
};

export const HeadingPaginated: FC<HeadingPaginatedProps> = (props) => {
    const t = useTypedTranslationFunction();
    const router = useRouter();
    const currentPage = parsePageNumberFromQuery(router.query[PAGE_QUERY_PARAMETER_NAME]);
    const isDesktop = useMediaMin('sm');
    const paginationButtons = usePagination(props.totalCount, currentPage, !isDesktop, DEFAULT_PAGE_SIZE);
    const totalPages = paginationButtons?.length ?? undefined;

    return (
        <Heading {...props}>
            {props.children}
            {totalPages !== undefined && totalPages > 1
                ? ' ' +
                  t('page {{ currentPage }} from {{ totalPages }}', {
                      totalPages: totalPages,
                      currentPage: currentPage,
                  })
                : ''}
        </Heading>
    );
};
