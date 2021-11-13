import Breadcrumbs from 'components/Layout/Breadcrumbs';
import { FC } from 'react';
import Heading from 'components/Basic/Heading';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useRouter } from 'next/router';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

const Search: FC = () => {
    const router = useRouter();
    const t = useTypedTranslationFunction();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [searchUrl] = useGetInternationalizedStaticUrls(['/search'], domainUrl);
    return (
        <>
            <Breadcrumbs breadcrumb={[{ name: t('Search'), slug: searchUrl }]} />
            <Webline>
                <Heading type={'h1'}>{`${t('Search results for')} "${router.query.q}"`}</Heading>
            </Webline>
        </>
    );
};

export default Search;
