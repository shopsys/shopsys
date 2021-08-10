import { FC } from 'react';
import { ProductDetailType } from '../../../connectors/products/ProductDetailType';
import ShopsysHeading from '../../basic/ShopsysHeading';
import { useTranslation } from 'next-i18next';

type ProductDetailProps = {
    data: ProductDetailType;
};

const ProductDetail: FC<ProductDetailProps> = (props) => {
    const { t } = useTranslation();
    const data = props.data;

    return (
        <>
            <p>{data.namePrefix}</p>
            <ShopsysHeading type="h1">
                {data.name} {data.nameSuffix}
            </ShopsysHeading>
            <p>
                {t('Code')}: {data.catalogNumber}
            </p>
            <div dangerouslySetInnerHTML={{ __html: data.description }} />
        </>
    );
};

export default ProductDetail;
