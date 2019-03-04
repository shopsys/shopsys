# Cart
This article describes functional behavior of a cart. Article shortly summarize its discount codes, behavior of cart content when user logs in and others.

The cart is the first step of order and can be in these states:
- **empty** - nothing was added to the cart yet, the customer is informed that the cart is empty
- **contains products** - the customer can see a list of products which have been added to the cart, customer can change quantity of products.

## Add to cart
- the product can be added to cart from the product detail or from the product listing (except for the product with variants)
## Cart overview in header
- shows the amount and total price with VAT of products in the cart 
- shows the information about empty cart
## Page cart
### Price
- total price including VAT is automatically recalculated (without page refresh) if the product is removed or the amount is changed

### Discount coupon
- discount code can be created in the section pricing -> promo codes in the administration
- the input for entering the discount code is in the cart on the Front-end
- after entering the discount code in the cart, total price with VAT is recalculated using the button "Apply"
- the coupon is applied for every cart item individually so the number of items doubles
- discount code can be removed from the cart and total price with VAT will be recalculated automatically

## Order flow
- the cart is the first step of order followed by a choice of shipping and payment
## Persistence
- the cart, including its content, is stored in the database for:
- 60 days for unregistered user
- 120 days for registered user

## Notification about product changes
- if the product is added to the cart and its price is changed or is not available anymore, the user is informed of the changes

## Cart behavior when logging in, logging out and register the user
- if an unregistered customer adds the product to the cart and makes the registration (the system logs the customer in) then the contents of the cart will be retained
- if an unsigned customer adds the product to the cart and then logs in, the contents of the cart will be retained
- after the customer logs out, the contents of the cart will be removed
- if the logged in customer adds the product to the cart and logs out, the contents of the cart will be retained for next login
