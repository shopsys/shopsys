# How to Work with Products

In the context of the products, there are several concepts on Shopsys Platform that can not be understood at first sight.
The article describes this concepts more in detail.

## Groups of products - "listable", "sellable", "offered", "visible"

Products can be grouped into several groups according to their current status or according to what they are used for.

**Visible** - products with an attribute `ProductVisibility->visible` set to the value `TRUE` for specific domain and pricing group.

The conditions which the product must satisfy to appear as visible:

- the product must not be set as hidden
- if the attribute "selling start date" is filled in, the value of this attribute must be set to the date in the past
- if the attribute "selling end date" is filled in, the value of this attribute must be set to the future date
- the product must have a name for the specific locale
- if the product is a variant, there must exist calculated price for this variant and for the specific pricing group
- if the product is a variant, its main variant must not be set as hidden
- if the product is the main variant, at least one of its variants must be visible

**Offered** - products that satisfied the conditions for **visible** and at the same time they have an attribute `Product->calculatedSellingDenied` set to the value `FALSE`.  
The `calculatedSellingDenied` attribute shows whether the product is already sold out or if the product is a variant with the main variant that is set up with selling denied on `TRUE`.

**Listable** - products that satisfied the conditions for **offered** and at the same time, these products are not the variants.  
Only the main variants are included in the product lists.

**Sellable** - products that satisfied the conditions for **offered** and at the same time, these products are not the main variants.  
Sellable products are products that can actually be purchased.
Even products that have their stock quantity 0 or less are still considered sellable.
Only the standard products or the specific variants can actually be purchased.

## How calculated attributes work?

Some attributes that are used on Shopsys Platform are not set directly, but their value is automatically calculated based on other attributes.
For example, if a category of products does not have a name for a locale of the specific domain, this category will be automatically set as not visible on this domain.
The visibility and selling denial of products are recalculated asynchronously, together with the export of products to Elasticsearch.
When a product is changed, a message is dispatched to the message broker, and the recalculation is done later by a consumer of the message.

For example, a method `edit` of a class `Shopsys\FrameworkBundle\Model\Product\ProductFacade` calls a method `dispatchSingleProductId` of a class `Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher`.
It means that the product does not have recalculated attributes immediately after the method `edit` is completed, but only after the dispatched message is consumed.
You can read more about the recalculations in [Product Recalculations](../asynchronous-processing/product-recalculations.md).

The console command `shopsys:recalculations` (see a class `Shopsys\FrameworkBundle\Command\RecalculationsCommand`) recalculates the visibility of categories and the visibility, selling denial and automatic flags of all products at once.
