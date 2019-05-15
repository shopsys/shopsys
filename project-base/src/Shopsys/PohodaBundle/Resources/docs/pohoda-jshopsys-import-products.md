# JShopsys - Import products into eshop

## Steps

The process of exporting products from Pohoda and import of these products into the shop is executed in several steps:

1. JShopsys makes a request on Pohoda
2. Pohoda returns XML file with exported products
3. JShopsys converts XML with products into multiple CSV files
4. These files are uploaded to FTP of eshop
5. JShopsys makes a request on eshop for each uploaded CSV

## Structure of processed CSV file

Original XML file with products contains lots of product attributes.
JShopsys selects only a part of these attributes from the original XML file.
Selected values are then exported as CSV file.

The attributes, that jShopsys uses for CSV creation, can be defined using a file '/web/db/pohoda/config/atributy.ini'.

### List of product attributes from Pohoda

* **stk:stockHeader:stk:id**
    - internal product ID from Pohoda
* **stk:stockHeader:stk:code**
    - catnum
* **stk:stockHeader:stk:EAN**
    - ean
* **stk:stockHeader:stk:sellingRateVAT**
    - Pohoda uses vat-types instead of numeric values
    - possible values are - high, low, none
* **stk:stockHeader:stk:name**
    - name of product
* **stk:stockHeader:stk:unit**
    - unit of measure, i.e. ks, m
* **stk:stockHeader:stk:purchasingPrice**
    - purchasing price of product
* **stk:stockHeader:stk:count**
    - available stock quantity
* **stk:stockHeader:stk:description**
    - description
* **stk:stockPriceItem:stk:stockPrice:typ:id**
    - internal ID of pricing group from Pohoda
    - this csv attribute/column is followed with 2 another attributes
        - followed with **stk:stockPriceItem:stk:stockPrice:typ:ids** - name of pricing group
        - followed with **stk:stockPriceItem:stk:stockPrice:typ:price** - price for this pricing group
    - this attribute can appear multiple times in final CSV file if the product in Pohoda has set price for multiple pricing groups
* ... and more

All product attributes can be derived from the official XML schema for Stock agenda.
This XML schema is available on https://www.stormware.cz/xml/schema/version_2/stock.xsd.



