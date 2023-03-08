import { Heading, HeadingProps } from './Heading';
import { DEFAULT_PAGE_SIZE } from 'components/Blocks/Pagination/Pagination';
import { parsePageNumberFromQuery } from 'helpers/pagination/parsePageNumberFromQuery';
import { PAGE_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useRouter } from 'next/router';

type HeadingPaginatedProps = HeadingProps & {
    totalCount: number;
};

export const HeadingPaginated: FC<HeadingPaginatedProps> = (props) => {
    const t = useTypedTranslationFunction();

    const router = useRouter();
    const currentPage = parsePageNumberFromQuery(router.query[PAGE_QUERY_PARAMETER_NAME]);

    const totalPages = Math.ceil(props.totalCount / DEFAULT_PAGE_SIZE);
    const additionalPaginationText =
        ' ' +
        t('page {{ currentPage }} from {{ totalPages }}', {
            totalPages: totalPages,
            currentPage: currentPage,
        });

    return (
        <Heading {...props}>
            {props.children}
            {totalPages > 1 ? additionalPaginationText : ''}
        </Heading>
    );
};
