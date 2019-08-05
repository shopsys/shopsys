# Product Visibility and Exclude from Sale
This article describes visibility of products and what can make product invisible.

## Product states
**Visible product** - is displayed on the Front-end of the e-shop in products overview, search results, XML feeds etc.

**Invisible product** - is not displayed on the Front-end of the e-shop, and page with 404 error is displayed hitting product URL.

**Product exclude from sale** - is not displayed on Front-end of the e-shop but the product details are displayed using URL, however without the option to add product to cart.

## Parameters that impact product visibility:
- **zero/missing price** - price for the customer's price group is not filled or is zero
- **unassigned category** - product with unassigned category
- **category is not visible** - the category in which the product is assigned is not visible
- **missing name** - the product name for one of domains is not filled in
- **invalid sales time** - the product does not have a valid selling date (that is, either the set selling date has not yet started or the selling date ended)
- **hide product -> yes** - the product is set as hidden (if any of the other conditions for hiding the product are met then the product may not be set to be hidden and yet not visible on the eshop)
- **action after sellout -> hide** - the product out of stock and the action has been set to hide after the stock has been sold out

### Conversion of products visibility:
1. **Midnight Cron module** - runs according to the current configuration once a day (at midnight) and recalculates all products.
2. **Immediately once the product is edited** - when the product is edited, message about `Product` change is produced to queue and `ProductVisibilityRecalculateConsumer` calculates visibility as soon as possible.

## Exclude from sale
Excluded from the sale is used if we do not want to see a 404 error page hitting the product URL.

The product is removed from the sale if any of the conditions are set in the product administration, in particular:
- **Exclude from sale -> Yes**
- **Action after sellout -> Exclude from sale**

### Behavior of product that is excluded from the sale:
- the product can be viewed only via URL
- the product is not displayed on the front-end of the product listing, or in the search and filter results
- the product cannot be added to cart and customer is informed (by the text) about the end of the sale of the product instead of the purchase button
- the product is not generated to XML feeds
