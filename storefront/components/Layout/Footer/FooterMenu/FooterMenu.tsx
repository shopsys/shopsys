import { FooterMenuStyled } from './FooterMenu.style';
import FooterMenuItem from 'components/Layout/Footer/FooterMenuItem';
import { FC } from 'react';

// TODO PRG
const dummyData = {
    items: [
        {
            title: 'O Commerce Cloudu',
            items: [
                { title: 'O nás' },
                { title: 'Práce v Shopsysu' },
                { title: 'Spolupráce' },
                { title: 'Pro média' },
                { title: 'Kontakty' },
            ],
        },
        {
            title: 'O Nákupu',
            items: [{ title: 'Péče o nábytek' }, { title: 'Nákup na splátky' }, { title: 'Reklamace' }],
        },
        {
            title: 'E-shop',
            items: [
                { title: 'Nejčastější dotazy FAQ' },
                { title: 'Doprava a platba' },
                { title: 'Obchodní podmínky e-shopu' },
            ],
        },
        {
            title: 'Prodejny',
            items: [
                { title: 'Kde nás najdete' },
                { title: 'Služby obchodních domů' },
                { title: 'Obchodní podmínky OD' },
            ],
        },
    ],
};

const FooterMenu: FC = () => {
    const testIdentifier = 'layout-footer-footermenu';

    return (
        <FooterMenuStyled data-testid={testIdentifier}>
            {dummyData.items.map((item, index) => (
                <FooterMenuItem key={index} title={item.title} items={item.items} />
            ))}
        </FooterMenuStyled>
    );
};

export default FooterMenu;
