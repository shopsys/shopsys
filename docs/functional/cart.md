# Cart
This article describes functional behavior of a cart. Article shortly summarize its discount codes, behavior of cart content when user logs in and others.

The cart is the first step of order and can be in these states:
- **empty** - nothing was added to the cart yet, the customer is informed that the cart is empty
- **contains goods** - the customer can see a list of products which have been added to the cart, customer can change quantity of products.

Product information displayed in the cart:
- Thumbnail
- Name with link to product detail
- Unit price including VAT
- VAT rate
- Amount
- Price including VAT - summed price for amount of added product

## Price
- total price including VAT is automatically recalculated if the product is removed or the amount is changed

## Discount code
- discount code can be created in the section pricing -> promo codes in the administration
- the input for entering the discount code is in the cart on the Front-end
- after entering the discount code in the cart, total price with VAT is recalculated using the button "Apply"
- discount code can be removed from the cart and total price with VAT will be recalculated automatically

## Cart behavior when logging in, logging out and register the user:
- if an unregistered customer adds the product to the cart and makes the registration (the system logs the customer in) then the contents of the cart will be retained
- if an unsigned customer adds the product to the cart and then logs in, the contents of the cart will be retained
- after the customer logs out, the contents of the cart will be removed
- if the logged in customer adds the product to the cart and logs out, the contents of the cart will be retained for next login
