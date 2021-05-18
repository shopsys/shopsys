import Register from 'framework/common/utils/Register';
import Responsive from '../utils/Responsive';

export default class InfoBoxHeight {

    constructor ($productList) {
        this.$productList = $productList;
    }

    addHeight (productsInRowResponsive, infoBoxHeight) {
        const listItems = infoBoxHeight.$productList.find('.js-list-products-item');
        const listItemsBlock = '.js-list-products-info-block';
        const rawValue = [];
        const finalValues = [];
        const productsInRow = productsInRowResponsive;

        $(listItemsBlock).css('height', ''); // important for calculate with real content height

        listItems.each(function () { // get values of height from every item
            let height = $(this).find(listItemsBlock).height();
            rawValue.push(height);
        });

        const dividedValues = new Array(Math.ceil(rawValue.length / productsInRow)).fill().map(_ => rawValue.splice(0, productsInRow)); // divide the array in equal chunks
        const countOfChunks = dividedValues.length;
        let biggestValue = 0;

        for (let i = 0; i < countOfChunks; i++) { // loop for getting the biggest value of height per everyone row
            for (let a = 0; a < productsInRow; a++) {
                let currentvalue = dividedValues[i][a];
                if (currentvalue > biggestValue) {
                    biggestValue = currentvalue;
                }
            }

            finalValues.push(biggestValue);
            biggestValue = 0; // reset biggest value
        }

        let valueOfProductsInRow = productsInRow;
        let biggestHeight = 0;

        listItems.each(function (index) {
            let infoBlock = $(this).find(listItemsBlock);

            if (valueOfProductsInRow > index) {
                infoBlock.css('height', finalValues[biggestHeight]);
            } else {
                valueOfProductsInRow = valueOfProductsInRow + productsInRow;
                biggestHeight = biggestHeight + 1;
                infoBlock.css('height', finalValues[biggestHeight]);
            }
        });
    }

    static init ($container) {
        const $productList = $container.filterAllNodes('.js-product-list');
        const infoBoxHeight = new InfoBoxHeight($productList);
        const productsInRowResponsive = [2, 3, 4];

        recalculateProductsRow(window.innerWidth);
        function recalculateProductsRow (breakpoint) {
            switch (true) {
                case breakpoint < Responsive.LG:
                    infoBoxHeight.addHeight(productsInRowResponsive[0], infoBoxHeight);
                    break;
                case breakpoint < Responsive.XL:
                    infoBoxHeight.addHeight(productsInRowResponsive[1], infoBoxHeight);
                    break;
                case breakpoint >= Responsive.XL:
                    infoBoxHeight.addHeight(productsInRowResponsive[2], infoBoxHeight);
                    break;
            }
        }

        $(window).resize(function () {
            recalculateProductsRow(window.innerWidth);
        });
    }
};

(new Register()).registerCallback(InfoBoxHeight.init);
