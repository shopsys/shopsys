import { HeadMainSquareStyled, HeadRowStyled } from './Head.style';
import Item from './Item/Item';
import { ButtonRemoveAll } from 'components/Pages/ProductsComparison/ButtonRemoveAll/ButtonRemoveAll';
import { ComparedProductFragmentApi } from 'graphql/generated';
import { FC } from 'react';

type HeadProps = {
    productsCompare: ComparedProductFragmentApi[];
};

const Head: FC<HeadProps> = (props) => {
    return (
        <thead>
            <HeadRowStyled id="js-table-compare-head">
                <HeadMainSquareStyled>
                    <ButtonRemoveAll />
                </HeadMainSquareStyled>
                {props.productsCompare.map((product, index) => {
                    return (
                        <Item
                            product={product}
                            key={`head-${product.uuid}`}
                            productsCompareCount={props.productsCompare!.length}
                            listIndex={index}
                        />
                    );
                })}
            </HeadRowStyled>
        </thead>
    );
};

export default Head;
