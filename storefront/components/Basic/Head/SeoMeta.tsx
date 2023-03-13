import { useSettingsQueryApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import Head from 'next/head';

type SeoMetaProps = {
    title?: string | null;
    description?: string | null;
};

export const SeoMeta: FC<SeoMetaProps> = ({ title, description }) => {
    const [{ data }] = useQueryError(useSettingsQueryApi());

    const titleFromApi = data?.settings?.seo.title;
    const descriptionFromApi = data?.settings?.seo.metaDescription;
    const suffixFromApi = data?.settings?.seo.titleAddOn;

    return (
        <Head>
            <title>
                {title ?? titleFromApi} {suffixFromApi}
            </title>
            <meta name="description" content={description ?? descriptionFromApi} />
        </Head>
    );
};
