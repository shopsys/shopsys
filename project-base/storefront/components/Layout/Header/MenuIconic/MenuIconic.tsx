import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useComparison } from 'hooks/comparison/useComparison';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { MenuIconicItem, MenuIconicItemLink, MenuIconicItemIcon } from './MenuIconicElements';
import { MenuIconicItemLogin } from './MenuIconicItemLogin';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';

const TEST_IDENTIFIER = 'layout-header-menuiconic';

export const MenuIconic: FC = () => {
    const t = useTypedTranslationFunction();
    const { url } = useDomainConfig();
    const [storesUrl, productsComparisonUrl] = getInternationalizedStaticUrls(['/stores', '/products-comparison'], url);
    const { comparison } = useComparison();

    return (
        <ul className="flex" data-testid={TEST_IDENTIFIER}>
            <MenuIconicItem dataTestId={TEST_IDENTIFIER + '-chat'} className="max-vl:hidden">
                <MenuIconicItemLink href="/">
                    <MenuIconicItemIcon icon="Chat" />
                    {t('Customer service')}
                </MenuIconicItemLink>
            </MenuIconicItem>
            <MenuIconicItem dataTestId={TEST_IDENTIFIER + '-stores'} className="max-vl:hidden">
                <MenuIconicItemLink href={storesUrl}>
                    <MenuIconicItemIcon icon="Marker" />
                    {t('Stores')}
                </MenuIconicItemLink>
            </MenuIconicItem>
            <MenuIconicItem dataTestId={TEST_IDENTIFIER + '-login'} className="max-vl:mr-0">
                <MenuIconicItemLogin />
            </MenuIconicItem>
            <MenuIconicItem data-testid={TEST_IDENTIFIER + '-comparison'} className="max-vl:hidden">
                <ExtendedNextLink href={productsComparisonUrl} passHref type="static">
                    <MenuIconicItemLink>
                        <MenuIconicItemIcon icon="Compare" />
                        {t('Comparison')}
                        {!!comparison?.products.length && <span>({comparison.products.length})</span>}
                    </MenuIconicItemLink>
                </ExtendedNextLink>
            </MenuIconicItem>
        </ul>
    );
};
