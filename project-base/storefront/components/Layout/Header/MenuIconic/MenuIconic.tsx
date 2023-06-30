import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useHandleCompare } from 'hooks/product/useHandleCompare';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { MenuIconicItem, MenuIconicItemLink, MenuIconicItemIcon } from './MenuIconicElements';
import { MenuIconicItemLogin } from './MenuIconicItemLogin';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';

const TEST_IDENTIFIER = 'layout-header-menuiconic';

export const MenuIconic: FC = () => {
    const t = useTypedTranslationFunction();
    const { url } = useDomainConfig();
    const [storesUrl, productsComparisonUrl] = getInternationalizedStaticUrls(
        ['/stores', '/customer', '/customer/orders', '/customer/edit-profile', '/products-comparison'],
        url,
    );
    const { comparisonProducts } = useHandleCompare('');

    return (
        <ul className="hidden lg:flex" data-testid={TEST_IDENTIFIER}>
            <MenuIconicItem dataTestId={TEST_IDENTIFIER + '-0'}>
                <MenuIconicItemLink href="/">
                    <MenuIconicItemIcon icon="Chat" />
                    {t('Customer service')}
                </MenuIconicItemLink>
            </MenuIconicItem>
            <MenuIconicItem dataTestId={TEST_IDENTIFIER + '-1'}>
                <MenuIconicItemLink href={storesUrl}>
                    <MenuIconicItemIcon icon="Marker" />
                    {t('Stores')}
                </MenuIconicItemLink>
            </MenuIconicItem>
            <MenuIconicItem dataTestId={TEST_IDENTIFIER + '-2'}>
                <MenuIconicItemLogin />
            </MenuIconicItem>
            <MenuIconicItem data-testid={TEST_IDENTIFIER + '-3'}>
                <ExtendedNextLink href={productsComparisonUrl} passHref type="static">
                    <MenuIconicItemLink>
                        <MenuIconicItemIcon icon="Compare" />
                        {t('Comparison')}
                        {!!comparisonProducts.length && <span>({comparisonProducts.length})</span>}
                    </MenuIconicItemLink>
                </ExtendedNextLink>
            </MenuIconicItem>
        </ul>
    );
};
