import { useSettingsQueryApi } from 'graphql/generated';
import Head from 'next/head';
import { FC } from 'react';

type SeoMetaProps = {
    title?: string | null;
    description?: string | null;
};

const SeoMeta: FC<SeoMetaProps> = ({ title, description }) => {
    const [{ data }] = useSettingsQueryApi();

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

export default SeoMeta;
