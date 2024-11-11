import { getServerT } from 'utils/getServerTranslation';

export default async function IndexPage() {
    const t = await getServerT();

    return (
        <div>
            <div>
                <p>This text is rendered on the server: {t('Delivery in {{count}} days', { count: 1 })}</p>
            </div>
        </div>
    );
}
