# Snapshots Info Lookup Table

## Snapshot Group - AUTHENTICATION

| Snapshot Id | Test Name | Snapshot Detail | File |
|-------------|-----------|-----------------|------|
| 1-0-0 | [Register B2C] should register as a B2C customer | filled registration form | registration.cy.ts |
| 1-0-1 | [Register B2C] should register as a B2C customer | customer edit page | registration.cy.ts |
| 1-0-2 | [Register B2C] should register as a B2C customer | after invalid try | registration.cy.ts |
| 1-0-3 | [Empty Form] should disallow registration with empty registration form, but then allow after filling | after invalid try | registration.cy.ts |

## Snapshot Group - CART

| Snapshot Id | Test Name | Snapshot Detail | File |
|-------------|-----------|-----------------|------|
| 2-0-0 | [Cart Header Remove] should remove products from cart using cart in header and then display empty cart message | after first remove | cartInHeader.cy.ts |
| 2-0-1 | [Cart Header Remove] should remove products from cart using cart in header and then display empty cart message | after second remove | cartInHeader.cy.ts |
| 2-1-0 | [Prefilled Cart] should log in, add product to cart to an already prefilled cart, and empty cart after log out | cart page after login | cartLogin.cy.ts |
| 2-1-1 | [Prefilled Cart] should log in, add product to cart to an already prefilled cart, and empty cart after log out | cart page after adding product to cart | cartLogin.cy.ts |
| 2-1-2 | [Prefilled Cart] should log in, add product to cart to an already prefilled cart, and empty cart after log out | cart page after adding product to cart | cartLogin.cy.ts |
| 2-1-3 | [Prefilled Cart] should log in, add product to cart to an already prefilled cart, and empty cart after log out | cart page after adding product to cart | cartLogin.cy.ts |
| 2-1-4 | [Prefilled Cart] should log in, add product to cart to an already prefilled cart, and empty cart after log out | cart page after second login | cartLogin.cy.ts |
| 2-1-5 | [Prefilled Cart] should log in, add product to cart to an already prefilled cart, and empty cart after log out | cart page after first login | cartLogin.cy.ts |
| 2-1-6 | [Prefilled Cart] should log in, add product to cart to an already prefilled cart, and empty cart after log out | third step before second login | cartLogin.cy.ts |
| 2-1-7 | [Prefilled Cart] should log in, add product to cart to an already prefilled cart, and empty cart after log out | third step after second login | cartLogin.cy.ts |
| 2-1-8 | [Empty Cart] should log in, add product to an empty cart, and empty cart after log out | cart page after adding product to cart | cartLogin.cy.ts |
| 2-1-9 | [Empty Cart] should log in, add product to an empty cart, and empty cart after log out | cart page after adding product to cart | cartLogin.cy.ts |
| 2-1-10 | [Empty Cart] should log in, add product to an empty cart, and empty cart after log out | cart page after second login | cartLogin.cy.ts |
| 2-1-11 | [Empty Cart] should log in, add product to an empty cart, and empty cart after log out | cart page after first login | cartLogin.cy.ts |
| 2-1-12 | [Empty Cart] should log in, add product to an empty cart, and empty cart after log out | third step before second login | cartLogin.cy.ts |
| 2-1-13 | [Empty Cart] should log in, add product to an empty cart, and empty cart after log out | third step after second login | cartLogin.cy.ts |
| 2-1-14 | [Merge Cart] should repeatedly merge carts when logged in (starting with an empty cart for the registered customer) | cart page after adding product to cart | cartLogin.cy.ts |
| 2-1-15 | [Merge Cart] should repeatedly merge carts when logged in (starting with an empty cart for the registered customer) | cart page after second login | cartLogin.cy.ts |
| 2-1-16 | [Merge Cart] should repeatedly merge carts when logged in (starting with an empty cart for the registered customer) | cart page after first login | cartLogin.cy.ts |
| 2-1-17 | [Merge Cart] should repeatedly merge carts when logged in (starting with an empty cart for the registered customer) | third step before second login | cartLogin.cy.ts |
| 2-1-18 | [Merge Cart] should repeatedly merge carts when logged in (starting with an empty cart for the registered customer) | third step after second login | cartLogin.cy.ts |
| 2-1-19 | [Discard Cart] should discard user's previous cart after logging in in order 3rd step | cart page after first login | cartLogin.cy.ts |
| 2-1-20 | [Discard Cart] should discard user's previous cart after logging in in order 3rd step | third step before second login | cartLogin.cy.ts |
| 2-1-21 | [Discard Cart] should discard user's previous cart after logging in in order 3rd step | third step after second login | cartLogin.cy.ts |
| 2-2-0 | [Fast Quantity Clicked] should increase and decrease product quantity using spinbox in cart (once if clicked fast) | after increase | cartPage.cy.ts |
| 2-2-1 | [Fast Quantity Clicked] should increase and decrease product quantity using spinbox in cart (once if clicked fast) | after decrease | cartPage.cy.ts |
| 2-2-2 | [Fast Quantity Clicked] should increase and decrease product quantity using spinbox in cart (once if clicked fast) | after increase | cartPage.cy.ts |
| 2-2-3 | [Fast Quantity Clicked] should increase and decrease product quantity using spinbox in cart (once if clicked fast) | after decrease | cartPage.cy.ts |
| 2-2-4 | [Fast Quantity Clicked] should increase and decrease product quantity using spinbox in cart (once if clicked fast) | after first removal | cartPage.cy.ts |
| 2-2-5 | [Fast Quantity Clicked] should increase and decrease product quantity using spinbox in cart (once if clicked fast) | empty cart after second removal | cartPage.cy.ts |
| 2-2-6 | [Fast Quantity Clicked] should increase and decrease product quantity using spinbox in cart (once if clicked fast) | cart page after applying first promocode | cartPage.cy.ts |
| 2-2-7 | [Fast Quantity Clicked] should increase and decrease product quantity using spinbox in cart (once if clicked fast) | transport and payment page after applying first promocode | cartPage.cy.ts |
| 2-2-8 | [Fast Quantity Clicked] should increase and decrease product quantity using spinbox in cart (once if clicked fast) | cart page after removing first promocode | cartPage.cy.ts |
| 2-2-9 | [Fast Quantity Clicked] should increase and decrease product quantity using spinbox in cart (once if clicked fast) | cart page after removing second promocode | cartPage.cy.ts |
| 2-2-10 | [Fast Quantity Clicked] should increase and decrease product quantity using spinbox in cart (once if clicked fast) | after applying promocode | cartPage.cy.ts |
| 2-2-11 | [Fast Quantity Clicked] should increase and decrease product quantity using spinbox in cart (once if clicked fast) | after removing product that allows promocode | cartPage.cy.ts |
| 2-2-12 | [Fast Quantity Clicked] should increase and decrease product quantity using spinbox in cart (once if clicked fast) | cart page with non-free transport after applying promocode | cartPage.cy.ts |
| 2-2-13 | [Slow Quantity Clicked] should increase and decrease product quantity using spinbox in cart (multiple times if clicked slowly) | after increase | cartPage.cy.ts |
| 2-2-14 | [Slow Quantity Clicked] should increase and decrease product quantity using spinbox in cart (multiple times if clicked slowly) | after decrease | cartPage.cy.ts |
| 2-2-15 | [Slow Quantity Clicked] should increase and decrease product quantity using spinbox in cart (multiple times if clicked slowly) | after first removal | cartPage.cy.ts |
| 2-2-16 | [Slow Quantity Clicked] should increase and decrease product quantity using spinbox in cart (multiple times if clicked slowly) | empty cart after second removal | cartPage.cy.ts |
| 2-2-17 | [Slow Quantity Clicked] should increase and decrease product quantity using spinbox in cart (multiple times if clicked slowly) | cart page after applying first promocode | cartPage.cy.ts |
| 2-2-18 | [Slow Quantity Clicked] should increase and decrease product quantity using spinbox in cart (multiple times if clicked slowly) | transport and payment page after applying first promocode | cartPage.cy.ts |
| 2-2-19 | [Slow Quantity Clicked] should increase and decrease product quantity using spinbox in cart (multiple times if clicked slowly) | cart page after removing first promocode | cartPage.cy.ts |
| 2-2-20 | [Slow Quantity Clicked] should increase and decrease product quantity using spinbox in cart (multiple times if clicked slowly) | cart page after removing second promocode | cartPage.cy.ts |
| 2-2-21 | [Slow Quantity Clicked] should increase and decrease product quantity using spinbox in cart (multiple times if clicked slowly) | after applying promocode | cartPage.cy.ts |
| 2-2-22 | [Slow Quantity Clicked] should increase and decrease product quantity using spinbox in cart (multiple times if clicked slowly) | after removing product that allows promocode | cartPage.cy.ts |
| 2-2-23 | [Slow Quantity Clicked] should increase and decrease product quantity using spinbox in cart (multiple times if clicked slowly) | cart page with non-free transport after applying promocode | cartPage.cy.ts |
| 2-2-24 | [Remove Products] should remove products from cart | after first removal | cartPage.cy.ts |
| 2-2-25 | [Remove Products] should remove products from cart | empty cart after second removal | cartPage.cy.ts |
| 2-2-26 | [Remove Products] should remove products from cart | cart page after applying first promocode | cartPage.cy.ts |
| 2-2-27 | [Remove Products] should remove products from cart | transport and payment page after applying first promocode | cartPage.cy.ts |
| 2-2-28 | [Remove Products] should remove products from cart | cart page after removing first promocode | cartPage.cy.ts |
| 2-2-29 | [Remove Products] should remove products from cart | cart page after removing second promocode | cartPage.cy.ts |
| 2-2-30 | [Remove Products] should remove products from cart | after applying promocode | cartPage.cy.ts |
| 2-2-31 | [Remove Products] should remove products from cart | after removing product that allows promocode | cartPage.cy.ts |
| 2-2-32 | [Remove Products] should remove products from cart | cart page with non-free transport after applying promocode | cartPage.cy.ts |
| 2-2-33 | [Quantity Spinbox Decrease] min spinbox button should not be clickable if it cannot be used due to min quantity | cart page after applying first promocode | cartPage.cy.ts |
| 2-2-34 | [Quantity Spinbox Decrease] min spinbox button should not be clickable if it cannot be used due to min quantity | transport and payment page after applying first promocode | cartPage.cy.ts |
| 2-2-35 | [Quantity Spinbox Decrease] min spinbox button should not be clickable if it cannot be used due to min quantity | cart page after removing first promocode | cartPage.cy.ts |
| 2-2-36 | [Quantity Spinbox Decrease] min spinbox button should not be clickable if it cannot be used due to min quantity | cart page after removing second promocode | cartPage.cy.ts |
| 2-2-37 | [Quantity Spinbox Decrease] min spinbox button should not be clickable if it cannot be used due to min quantity | after applying promocode | cartPage.cy.ts |
| 2-2-38 | [Quantity Spinbox Decrease] min spinbox button should not be clickable if it cannot be used due to min quantity | after removing product that allows promocode | cartPage.cy.ts |
| 2-2-39 | [Quantity Spinbox Decrease] min spinbox button should not be clickable if it cannot be used due to min quantity | cart page with non-free transport after applying promocode | cartPage.cy.ts |
| 2-2-40 | [Quantity Spinbox Increase] max spinbox button should be always clickable | cart page after applying first promocode | cartPage.cy.ts |
| 2-2-41 | [Quantity Spinbox Increase] max spinbox button should be always clickable | transport and payment page after applying first promocode | cartPage.cy.ts |
| 2-2-42 | [Quantity Spinbox Increase] max spinbox button should be always clickable | cart page after removing first promocode | cartPage.cy.ts |
| 2-2-43 | [Quantity Spinbox Increase] max spinbox button should be always clickable | cart page after removing second promocode | cartPage.cy.ts |
| 2-2-44 | [Quantity Spinbox Increase] max spinbox button should be always clickable | after applying promocode | cartPage.cy.ts |
| 2-2-45 | [Quantity Spinbox Increase] max spinbox button should be always clickable | after removing product that allows promocode | cartPage.cy.ts |
| 2-2-46 | [Quantity Spinbox Increase] max spinbox button should be always clickable | cart page with non-free transport after applying promocode | cartPage.cy.ts |
| 2-2-47 | [Add Remove Promo] should add promo code to cart, check it, remove promo code from cart, and then add a different one | cart page after applying first promocode | cartPage.cy.ts |
| 2-2-48 | [Add Remove Promo] should add promo code to cart, check it, remove promo code from cart, and then add a different one | transport and payment page after applying first promocode | cartPage.cy.ts |
| 2-2-49 | [Add Remove Promo] should add promo code to cart, check it, remove promo code from cart, and then add a different one | cart page after removing first promocode | cartPage.cy.ts |
| 2-2-50 | [Add Remove Promo] should add promo code to cart, check it, remove promo code from cart, and then add a different one | cart page after removing second promocode | cartPage.cy.ts |
| 2-2-51 | [Add Remove Promo] should add promo code to cart, check it, remove promo code from cart, and then add a different one | after applying promocode | cartPage.cy.ts |
| 2-2-52 | [Add Remove Promo] should add promo code to cart, check it, remove promo code from cart, and then add a different one | after removing product that allows promocode | cartPage.cy.ts |
| 2-2-53 | [Add Remove Promo] should add promo code to cart, check it, remove promo code from cart, and then add a different one | cart page with non-free transport after applying promocode | cartPage.cy.ts |
| 2-2-54 | [Add Promo Remove Product] should add promo code to cart, remove product that allows it, and see the promo code removed | after applying promocode | cartPage.cy.ts |
| 2-2-55 | [Add Promo Remove Product] should add promo code to cart, remove product that allows it, and see the promo code removed | after removing product that allows promocode | cartPage.cy.ts |
| 2-2-56 | [Add Promo Remove Product] should add promo code to cart, remove product that allows it, and see the promo code removed | cart page with non-free transport after applying promocode | cartPage.cy.ts |
| 2-2-57 | [No Free Transport] transport should not be free if price minus promo code discount is below the free transport limit | cart page with non-free transport after applying promocode | cartPage.cy.ts |
| 2-3-0 | [Brand Page Add] should add product to cart from brand page | add to cart popup | productAddToCart.cy.ts |
| 2-3-1 | [Brand Page Add] should add product to cart from brand page | add to cart popup | productAddToCart.cy.ts |
| 2-3-2 | [Brand Page Add] should add product to cart from brand page | add to cart popup | productAddToCart.cy.ts |
| 2-3-3 | [Brand Page Add] should add product to cart from brand page | add to cart popup | productAddToCart.cy.ts |
| 2-3-4 | [Brand Page Add] should add product to cart from brand page | add to cart popup | productAddToCart.cy.ts |
| 2-3-5 | [Brand Page Add] should add product to cart from brand page | add to cart popup | productAddToCart.cy.ts |
| 2-3-6 | [Product Detail Add] should add product to cart from product detail | add to cart popup | productAddToCart.cy.ts |
| 2-3-7 | [Product Detail Add] should add product to cart from product detail | add to cart popup | productAddToCart.cy.ts |
| 2-3-8 | [Product Detail Add] should add product to cart from product detail | add to cart popup | productAddToCart.cy.ts |
| 2-3-9 | [Product Detail Add] should add product to cart from product detail | add to cart popup | productAddToCart.cy.ts |
| 2-3-10 | [Product Detail Add] should add product to cart from product detail | add to cart popup | productAddToCart.cy.ts |
| 2-3-11 | [Category Page Add] should add product to cart from category page | add to cart popup | productAddToCart.cy.ts |
| 2-3-12 | [Category Page Add] should add product to cart from category page | add to cart popup | productAddToCart.cy.ts |
| 2-3-13 | [Category Page Add] should add product to cart from category page | add to cart popup | productAddToCart.cy.ts |
| 2-3-14 | [Category Page Add] should add product to cart from category page | add to cart popup | productAddToCart.cy.ts |
| 2-3-15 | [Product Variant Add] should add variant product to cart from product detail | add to cart popup | productAddToCart.cy.ts |
| 2-3-16 | [Product Variant Add] should add variant product to cart from product detail | add to cart popup | productAddToCart.cy.ts |
| 2-3-17 | [Product Variant Add] should add variant product to cart from product detail | add to cart popup | productAddToCart.cy.ts |
| 2-3-18 | [Promoted Products Add] should add product to cart from promoted products on homepage | add to cart popup | productAddToCart.cy.ts |
| 2-3-19 | [Promoted Products Add] should add product to cart from promoted products on homepage | add to cart popup | productAddToCart.cy.ts |
| 2-3-20 | [Search Page Add] should add product to cart from search results page | add to cart popup | productAddToCart.cy.ts |

## Snapshot Group - MATRIX

| Snapshot Id | Test Name | Snapshot Detail | File |
|-------------|-----------|-----------------|------|
| 0-0-0 | [Matrix] should visit matrix page with screenshot | matrix page | matrixTest.cy.ts |

## Snapshot Group - ORDER

| Snapshot Id | Test Name | Snapshot Detail | File |
|-------------|-----------|-----------------|------|
| 3-0-0 | [Anon Empty Cart] should redirect to cart page and not display contact information form if cart is empty and user is not logged in | transport and payment page | contactInformation.cy.ts |
| 3-0-1 | [Anon Empty Cart] should redirect to cart page and not display contact information form if cart is empty and user is not logged in | transport and payment page | contactInformation.cy.ts |
| 3-0-2 | [Anon Empty Cart] should redirect to cart page and not display contact information form if cart is empty and user is not logged in | contact information page after reload | contactInformation.cy.ts |
| 3-0-3 | [Anon Empty Cart] should redirect to cart page and not display contact information form if cart is empty and user is not logged in | contact information page after reload | contactInformation.cy.ts |
| 3-0-4 | [Anon Transport & Payment] should redirect to transport and payment select page and not display contact information form if transport and payment are not selected and user is not logged in | transport and payment page | contactInformation.cy.ts |
| 3-0-5 | [Anon Transport & Payment] should redirect to transport and payment select page and not display contact information form if transport and payment are not selected and user is not logged in | transport and payment page | contactInformation.cy.ts |
| 3-0-6 | [Anon Transport & Payment] should redirect to transport and payment select page and not display contact information form if transport and payment are not selected and user is not logged in | contact information page after reload | contactInformation.cy.ts |
| 3-0-7 | [Anon Transport & Payment] should redirect to transport and payment select page and not display contact information form if transport and payment are not selected and user is not logged in | contact information page after reload | contactInformation.cy.ts |
| 3-0-8 | [Preserve Contact Form] should keep filled contact information after page refresh | contact information page after reload | contactInformation.cy.ts |
| 3-0-9 | [Preserve Contact Form] should keep filled contact information after page refresh | contact information page after reload | contactInformation.cy.ts |
| 3-1-0 | [Anon Registered Home Cash] should create order as unlogged user with a registered email (transport to home, cash on delivery) and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-1 | [Anon Registered Home Cash] should create order as unlogged user with a registered email (transport to home, cash on delivery) and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-2 | [Anon Registered Home Cash] should create order as unlogged user with a registered email (transport to home, cash on delivery) and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-3 | [Anon Registered Home Cash] should create order as unlogged user with a registered email (transport to home, cash on delivery) and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-4 | [Anon Registered Home Cash] should create order as unlogged user with a registered email (transport to home, cash on delivery) and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-5 | [Anon Registered Home Cash] should create order as unlogged user with a registered email (transport to home, cash on delivery) and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-6 | [Anon Registered Home Cash] should create order as unlogged user with a registered email (transport to home, cash on delivery) and check it in order detail | customer edit page | createOrder.cy.ts |
| 3-1-7 | [Anon Registered Home Cash] should create order as unlogged user with a registered email (transport to home, cash on delivery) and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-8 | [Anon Home Cash] should create order as unlogged user (transport to home, cash on delivery) and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-9 | [Anon Home Cash] should create order as unlogged user (transport to home, cash on delivery) and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-10 | [Anon Home Cash] should create order as unlogged user (transport to home, cash on delivery) and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-11 | [Anon Home Cash] should create order as unlogged user (transport to home, cash on delivery) and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-12 | [Anon Home Cash] should create order as unlogged user (transport to home, cash on delivery) and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-13 | [Anon Home Cash] should create order as unlogged user (transport to home, cash on delivery) and check it in order detail | customer edit page | createOrder.cy.ts |
| 3-1-14 | [Anon Home Cash] should create order as unlogged user (transport to home, cash on delivery) and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-15 | [Anon Collect Cash] should create order as unlogged user (personal collection, cash) and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-16 | [Anon Collect Cash] should create order as unlogged user (personal collection, cash) and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-17 | [Anon Collect Cash] should create order as unlogged user (personal collection, cash) and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-18 | [Anon Collect Cash] should create order as unlogged user (personal collection, cash) and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-19 | [Anon Collect Cash] should create order as unlogged user (personal collection, cash) and check it in order detail | customer edit page | createOrder.cy.ts |
| 3-1-20 | [Anon Collect Cash] should create order as unlogged user (personal collection, cash) and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-21 | [Anon PPL Card] should create order as unlogged user (PPL, credit card) and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-22 | [Anon PPL Card] should create order as unlogged user (PPL, credit card) and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-23 | [Anon PPL Card] should create order as unlogged user (PPL, credit card) and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-24 | [Anon PPL Card] should create order as unlogged user (PPL, credit card) and check it in order detail | customer edit page | createOrder.cy.ts |
| 3-1-25 | [Anon PPL Card] should create order as unlogged user (PPL, credit card) and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-26 | [Anon Promo Code] should create order with promo code and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-27 | [Anon Promo Code] should create order with promo code and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-28 | [Anon Promo Code] should create order with promo code and check it in order detail | customer edit page | createOrder.cy.ts |
| 3-1-29 | [Anon Promo Code] should create order with promo code and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-2-0 | [Preserve Form On Refresh] should keep filled delivery address after page refresh | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-1 | [Preserve Form On Refresh] should keep filled delivery address after page refresh | contact information form after refresh | createOrderWithDeliveryAddress.cy.ts |
| 3-2-2 | [Preserve Form On Refresh] should keep filled delivery address after page refresh | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-3 | [Preserve Form On Refresh] should keep filled delivery address after page refresh | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-4 | [Preserve Form On Refresh] should keep filled delivery address after page refresh | contact information form after refresh | createOrderWithDeliveryAddress.cy.ts |
| 3-2-5 | [Preserve Form On Refresh] should keep filled delivery address after page refresh | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-6 | [Preserve Form On Refresh] should keep filled delivery address after page refresh | with default address | createOrderWithDeliveryAddress.cy.ts |
| 3-2-7 | [Preserve Form On Refresh] should keep filled delivery address after page refresh | changed contact information after refresh | createOrderWithDeliveryAddress.cy.ts |
| 3-2-8 | [Preserve Form On Refresh] should keep filled delivery address after page refresh | with default address | createOrderWithDeliveryAddress.cy.ts |
| 3-2-9 | [Preserve Form On Refresh] should keep filled delivery address after page refresh | with changed delivery address | createOrderWithDeliveryAddress.cy.ts |
| 3-2-10 | [Preserve Form On Refresh] should keep filled delivery address after page refresh | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-11 | [Preserve Form On Refresh] should keep filled delivery address after page refresh | contact information form after refresh | createOrderWithDeliveryAddress.cy.ts |
| 3-2-12 | [Preserve Form On Refresh] should keep filled delivery address after page refresh | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-13 | [Preserve Form On Refresh] should keep filled delivery address after page refresh | after checking again | createOrderWithDeliveryAddress.cy.ts |
| 3-2-14 | [Preserve Form On Refresh] should keep filled delivery address after page refresh | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-15 | [Preserve Form On Refresh] should keep filled delivery address after page refresh | contact information form after refresh | createOrderWithDeliveryAddress.cy.ts |
| 3-2-16 | [Preserve Form On Refresh] should keep filled delivery address after page refresh | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-17 | [Preserve Form On Refresh] should keep filled delivery address after page refresh | after checking again | createOrderWithDeliveryAddress.cy.ts |
| 3-2-18 | [Preserve Form On Checkbox Change] should keep filled delivery address after unchecking the checkbox for different delivery address and then checking it again | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-19 | [Preserve Form On Checkbox Change] should keep filled delivery address after unchecking the checkbox for different delivery address and then checking it again | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-20 | [Preserve Form On Checkbox Change] should keep filled delivery address after unchecking the checkbox for different delivery address and then checking it again | contact information form after refresh | createOrderWithDeliveryAddress.cy.ts |
| 3-2-21 | [Preserve Form On Checkbox Change] should keep filled delivery address after unchecking the checkbox for different delivery address and then checking it again | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-22 | [Preserve Form On Checkbox Change] should keep filled delivery address after unchecking the checkbox for different delivery address and then checking it again | with default address | createOrderWithDeliveryAddress.cy.ts |
| 3-2-23 | [Preserve Form On Checkbox Change] should keep filled delivery address after unchecking the checkbox for different delivery address and then checking it again | changed contact information after refresh | createOrderWithDeliveryAddress.cy.ts |
| 3-2-24 | [Preserve Form On Checkbox Change] should keep filled delivery address after unchecking the checkbox for different delivery address and then checking it again | with default address | createOrderWithDeliveryAddress.cy.ts |
| 3-2-25 | [Preserve Form On Checkbox Change] should keep filled delivery address after unchecking the checkbox for different delivery address and then checking it again | with changed delivery address | createOrderWithDeliveryAddress.cy.ts |
| 3-2-26 | [Preserve Form On Checkbox Change] should keep filled delivery address after unchecking the checkbox for different delivery address and then checking it again | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-27 | [Preserve Form On Checkbox Change] should keep filled delivery address after unchecking the checkbox for different delivery address and then checking it again | contact information form after refresh | createOrderWithDeliveryAddress.cy.ts |
| 3-2-28 | [Preserve Form On Checkbox Change] should keep filled delivery address after unchecking the checkbox for different delivery address and then checking it again | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-29 | [Preserve Form On Checkbox Change] should keep filled delivery address after unchecking the checkbox for different delivery address and then checking it again | after checking again | createOrderWithDeliveryAddress.cy.ts |
| 3-2-30 | [Preserve Form On Checkbox Change] should keep filled delivery address after unchecking the checkbox for different delivery address and then checking it again | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-31 | [Preserve Form On Checkbox Change] should keep filled delivery address after unchecking the checkbox for different delivery address and then checking it again | contact information form after refresh | createOrderWithDeliveryAddress.cy.ts |
| 3-2-32 | [Preserve Form On Checkbox Change] should keep filled delivery address after unchecking the checkbox for different delivery address and then checking it again | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-33 | [Preserve Form On Checkbox Change] should keep filled delivery address after unchecking the checkbox for different delivery address and then checking it again | after checking again | createOrderWithDeliveryAddress.cy.ts |
| 3-2-34 | [Logged Preserve Form On Refresh] should keep filled delivery address for logged-in user after page refresh | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-35 | [Logged Preserve Form On Refresh] should keep filled delivery address for logged-in user after page refresh | contact information form after refresh | createOrderWithDeliveryAddress.cy.ts |
| 3-2-36 | [Logged Preserve Form On Refresh] should keep filled delivery address for logged-in user after page refresh | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-37 | [Logged Preserve Form On Refresh] should keep filled delivery address for logged-in user after page refresh | with default address | createOrderWithDeliveryAddress.cy.ts |
| 3-2-38 | [Logged Preserve Form On Refresh] should keep filled delivery address for logged-in user after page refresh | changed contact information after refresh | createOrderWithDeliveryAddress.cy.ts |
| 3-2-39 | [Logged Preserve Form On Refresh] should keep filled delivery address for logged-in user after page refresh | with default address | createOrderWithDeliveryAddress.cy.ts |
| 3-2-40 | [Logged Preserve Form On Refresh] should keep filled delivery address for logged-in user after page refresh | with changed delivery address | createOrderWithDeliveryAddress.cy.ts |
| 3-2-41 | [Logged Preserve Form On Refresh] should keep filled delivery address for logged-in user after page refresh | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-42 | [Logged Preserve Form On Refresh] should keep filled delivery address for logged-in user after page refresh | contact information form after refresh | createOrderWithDeliveryAddress.cy.ts |
| 3-2-43 | [Logged Preserve Form On Refresh] should keep filled delivery address for logged-in user after page refresh | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-44 | [Logged Preserve Form On Refresh] should keep filled delivery address for logged-in user after page refresh | after checking again | createOrderWithDeliveryAddress.cy.ts |
| 3-2-45 | [Logged Preserve Form On Refresh] should keep filled delivery address for logged-in user after page refresh | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-46 | [Logged Preserve Form On Refresh] should keep filled delivery address for logged-in user after page refresh | contact information form after refresh | createOrderWithDeliveryAddress.cy.ts |
| 3-2-47 | [Logged Preserve Form On Refresh] should keep filled delivery address for logged-in user after page refresh | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-48 | [Logged Preserve Form On Refresh] should keep filled delivery address for logged-in user after page refresh | after checking again | createOrderWithDeliveryAddress.cy.ts |
| 3-2-49 | [Logged Preserve Form On Checkbox Change] should keep filled delivery address for logged-in user after unchecking the checkbox for different delivery address and then checking it again | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-50 | [Logged Preserve Form On Checkbox Change] should keep filled delivery address for logged-in user after unchecking the checkbox for different delivery address and then checking it again | with default address | createOrderWithDeliveryAddress.cy.ts |
| 3-2-51 | [Logged Preserve Form On Checkbox Change] should keep filled delivery address for logged-in user after unchecking the checkbox for different delivery address and then checking it again | changed contact information after refresh | createOrderWithDeliveryAddress.cy.ts |
| 3-2-52 | [Logged Preserve Form On Checkbox Change] should keep filled delivery address for logged-in user after unchecking the checkbox for different delivery address and then checking it again | with default address | createOrderWithDeliveryAddress.cy.ts |
| 3-2-53 | [Logged Preserve Form On Checkbox Change] should keep filled delivery address for logged-in user after unchecking the checkbox for different delivery address and then checking it again | with changed delivery address | createOrderWithDeliveryAddress.cy.ts |
| 3-2-54 | [Logged Preserve Form On Checkbox Change] should keep filled delivery address for logged-in user after unchecking the checkbox for different delivery address and then checking it again | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-55 | [Logged Preserve Form On Checkbox Change] should keep filled delivery address for logged-in user after unchecking the checkbox for different delivery address and then checking it again | contact information form after refresh | createOrderWithDeliveryAddress.cy.ts |
| 3-2-56 | [Logged Preserve Form On Checkbox Change] should keep filled delivery address for logged-in user after unchecking the checkbox for different delivery address and then checking it again | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-57 | [Logged Preserve Form On Checkbox Change] should keep filled delivery address for logged-in user after unchecking the checkbox for different delivery address and then checking it again | after checking again | createOrderWithDeliveryAddress.cy.ts |
| 3-2-58 | [Logged Preserve Form On Checkbox Change] should keep filled delivery address for logged-in user after unchecking the checkbox for different delivery address and then checking it again | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-59 | [Logged Preserve Form On Checkbox Change] should keep filled delivery address for logged-in user after unchecking the checkbox for different delivery address and then checking it again | contact information form after refresh | createOrderWithDeliveryAddress.cy.ts |
| 3-2-60 | [Logged Preserve Form On Checkbox Change] should keep filled delivery address for logged-in user after unchecking the checkbox for different delivery address and then checking it again | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-61 | [Logged Preserve Form On Checkbox Change] should keep filled delivery address for logged-in user after unchecking the checkbox for different delivery address and then checking it again | after checking again | createOrderWithDeliveryAddress.cy.ts |
| 3-2-62 | [Logged Default Fill New] should first select saved default delivery address for logged-in user, but then fill and keep new delivery address after refresh | with default address | createOrderWithDeliveryAddress.cy.ts |
| 3-2-63 | [Logged Default Fill New] should first select saved default delivery address for logged-in user, but then fill and keep new delivery address after refresh | changed contact information after refresh | createOrderWithDeliveryAddress.cy.ts |
| 3-2-64 | [Logged Default Fill New] should first select saved default delivery address for logged-in user, but then fill and keep new delivery address after refresh | with default address | createOrderWithDeliveryAddress.cy.ts |
| 3-2-65 | [Logged Default Fill New] should first select saved default delivery address for logged-in user, but then fill and keep new delivery address after refresh | with changed delivery address | createOrderWithDeliveryAddress.cy.ts |
| 3-2-66 | [Logged Default Fill New] should first select saved default delivery address for logged-in user, but then fill and keep new delivery address after refresh | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-67 | [Logged Default Fill New] should first select saved default delivery address for logged-in user, but then fill and keep new delivery address after refresh | contact information form after refresh | createOrderWithDeliveryAddress.cy.ts |
| 3-2-68 | [Logged Default Fill New] should first select saved default delivery address for logged-in user, but then fill and keep new delivery address after refresh | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-69 | [Logged Default Fill New] should first select saved default delivery address for logged-in user, but then fill and keep new delivery address after refresh | after checking again | createOrderWithDeliveryAddress.cy.ts |
| 3-2-70 | [Logged Default Fill New] should first select saved default delivery address for logged-in user, but then fill and keep new delivery address after refresh | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-71 | [Logged Default Fill New] should first select saved default delivery address for logged-in user, but then fill and keep new delivery address after refresh | contact information form after refresh | createOrderWithDeliveryAddress.cy.ts |
| 3-2-72 | [Logged Default Fill New] should first select saved default delivery address for logged-in user, but then fill and keep new delivery address after refresh | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-73 | [Logged Default Fill New] should first select saved default delivery address for logged-in user, but then fill and keep new delivery address after refresh | after checking again | createOrderWithDeliveryAddress.cy.ts |
| 3-2-74 | [Logged Default Fill New Default] should first select saved default delivery address for logged-in user, then fill new delivery address, then change it to a saved one and back to the new address which should stay filled | with default address | createOrderWithDeliveryAddress.cy.ts |
| 3-2-75 | [Logged Default Fill New Default] should first select saved default delivery address for logged-in user, then fill new delivery address, then change it to a saved one and back to the new address which should stay filled | with changed delivery address | createOrderWithDeliveryAddress.cy.ts |
| 3-2-76 | [Logged Default Fill New Default] should first select saved default delivery address for logged-in user, then fill new delivery address, then change it to a saved one and back to the new address which should stay filled | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-77 | [Logged Default Fill New Default] should first select saved default delivery address for logged-in user, then fill new delivery address, then change it to a saved one and back to the new address which should stay filled | contact information form after refresh | createOrderWithDeliveryAddress.cy.ts |
| 3-2-78 | [Logged Default Fill New Default] should first select saved default delivery address for logged-in user, then fill new delivery address, then change it to a saved one and back to the new address which should stay filled | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-79 | [Logged Default Fill New Default] should first select saved default delivery address for logged-in user, then fill new delivery address, then change it to a saved one and back to the new address which should stay filled | after checking again | createOrderWithDeliveryAddress.cy.ts |
| 3-2-80 | [Logged Default Fill New Default] should first select saved default delivery address for logged-in user, then fill new delivery address, then change it to a saved one and back to the new address which should stay filled | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-81 | [Logged Default Fill New Default] should first select saved default delivery address for logged-in user, then fill new delivery address, then change it to a saved one and back to the new address which should stay filled | contact information form after refresh | createOrderWithDeliveryAddress.cy.ts |
| 3-2-82 | [Logged Default Fill New Default] should first select saved default delivery address for logged-in user, then fill new delivery address, then change it to a saved one and back to the new address which should stay filled | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-83 | [Logged Default Fill New Default] should first select saved default delivery address for logged-in user, then fill new delivery address, then change it to a saved one and back to the new address which should stay filled | after checking again | createOrderWithDeliveryAddress.cy.ts |
| 3-2-84 | [Preserve Pickup On Refresh] should prefill delivery address from selected pickup point and keep delivery contact after refresh | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-85 | [Preserve Pickup On Refresh] should prefill delivery address from selected pickup point and keep delivery contact after refresh | contact information form after refresh | createOrderWithDeliveryAddress.cy.ts |
| 3-2-86 | [Preserve Pickup On Refresh] should prefill delivery address from selected pickup point and keep delivery contact after refresh | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-87 | [Preserve Pickup On Refresh] should prefill delivery address from selected pickup point and keep delivery contact after refresh | after checking again | createOrderWithDeliveryAddress.cy.ts |
| 3-2-88 | [Preserve Pickup On Refresh] should prefill delivery address from selected pickup point and keep delivery contact after refresh | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-89 | [Preserve Pickup On Refresh] should prefill delivery address from selected pickup point and keep delivery contact after refresh | contact information form after refresh | createOrderWithDeliveryAddress.cy.ts |
| 3-2-90 | [Preserve Pickup On Refresh] should prefill delivery address from selected pickup point and keep delivery contact after refresh | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-91 | [Preserve Pickup On Refresh] should prefill delivery address from selected pickup point and keep delivery contact after refresh | after checking again | createOrderWithDeliveryAddress.cy.ts |
| 3-2-92 | [Preserve Pickup On Checkbox Change] should prefill delivery address from selected pickup point and keep delivery contact after unchecking the checkbox for different delivery contact and then checking it again | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-93 | [Preserve Pickup On Checkbox Change] should prefill delivery address from selected pickup point and keep delivery contact after unchecking the checkbox for different delivery contact and then checking it again | after checking again | createOrderWithDeliveryAddress.cy.ts |
| 3-2-94 | [Preserve Pickup On Checkbox Change] should prefill delivery address from selected pickup point and keep delivery contact after unchecking the checkbox for different delivery contact and then checking it again | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-95 | [Preserve Pickup On Checkbox Change] should prefill delivery address from selected pickup point and keep delivery contact after unchecking the checkbox for different delivery contact and then checking it again | contact information form after refresh | createOrderWithDeliveryAddress.cy.ts |
| 3-2-96 | [Preserve Pickup On Checkbox Change] should prefill delivery address from selected pickup point and keep delivery contact after unchecking the checkbox for different delivery contact and then checking it again | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-97 | [Preserve Pickup On Checkbox Change] should prefill delivery address from selected pickup point and keep delivery contact after unchecking the checkbox for different delivery contact and then checking it again | after checking again | createOrderWithDeliveryAddress.cy.ts |
| 3-2-98 | [Logged No Prefill On Pickup Preserve On Refresh] should not prefill delivery contact for logged-in user with saved address and with selected pickup point, and then keep the filled delivery information after refresh | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-99 | [Logged No Prefill On Pickup Preserve On Refresh] should not prefill delivery contact for logged-in user with saved address and with selected pickup point, and then keep the filled delivery information after refresh | contact information form after refresh | createOrderWithDeliveryAddress.cy.ts |
| 3-2-100 | [Logged No Prefill On Pickup Preserve On Refresh] should not prefill delivery contact for logged-in user with saved address and with selected pickup point, and then keep the filled delivery information after refresh | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-101 | [Logged No Prefill On Pickup Preserve On Refresh] should not prefill delivery contact for logged-in user with saved address and with selected pickup point, and then keep the filled delivery information after refresh | after checking again | createOrderWithDeliveryAddress.cy.ts |
| 3-2-102 | [Logged No Prefill On Pickup Preserve On Checkbox Change] should not prefill delivery contact for logged-in user with saved address and pickup point, but keep filled delivery information after unchecking and checking checkbox for different delivery address | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-103 | [Logged No Prefill On Pickup Preserve On Checkbox Change] should not prefill delivery contact for logged-in user with saved address and pickup point, but keep filled delivery information after unchecking and checking checkbox for different delivery address | after checking again | createOrderWithDeliveryAddress.cy.ts |
| 3-3-0 | [Logged Repeat With Empty] should repeat order (pre-fill cart) for logged-in user with initially empty cart | after repeat | orderRepeat.cy.ts |
| 3-3-1 | [Logged Repeat With Empty] should repeat order (pre-fill cart) for logged-in user with initially empty cart | after repeat | orderRepeat.cy.ts |
| 3-3-2 | [Logged Repeat With Empty] should repeat order (pre-fill cart) for logged-in user with initially empty cart | after repeat | orderRepeat.cy.ts |
| 3-3-3 | [Logged Repeat With Empty] should repeat order (pre-fill cart) for logged-in user with initially empty cart | after repeat | orderRepeat.cy.ts |
| 3-3-4 | [Logged Repeat With Empty] should repeat order (pre-fill cart) for logged-in user with initially empty cart | after repeat | orderRepeat.cy.ts |
| 3-3-5 | [Logged Repeat With Empty] should repeat order (pre-fill cart) for logged-in user with initially empty cart | after repeat | orderRepeat.cy.ts |
| 3-3-6 | [Logged Repeat With Prefilled And Merge] should repeat order (pre-fill cart) for logged-in user with initially filled cart and allowed merging | after repeat | orderRepeat.cy.ts |
| 3-3-7 | [Logged Repeat With Prefilled And Merge] should repeat order (pre-fill cart) for logged-in user with initially filled cart and allowed merging | after repeat | orderRepeat.cy.ts |
| 3-3-8 | [Logged Repeat With Prefilled And Merge] should repeat order (pre-fill cart) for logged-in user with initially filled cart and allowed merging | after repeat | orderRepeat.cy.ts |
| 3-3-9 | [Logged Repeat With Prefilled And Merge] should repeat order (pre-fill cart) for logged-in user with initially filled cart and allowed merging | after repeat | orderRepeat.cy.ts |
| 3-3-10 | [Logged Repeat With Prefilled And Merge] should repeat order (pre-fill cart) for logged-in user with initially filled cart and allowed merging | after repeat | orderRepeat.cy.ts |
| 3-3-11 | [Logged Repeat With Prefilled And No Merge] should repeat order (pre-fill cart) for logged-in user with initially filled cart and disallowed merging | after repeat | orderRepeat.cy.ts |
| 3-3-12 | [Logged Repeat With Prefilled And No Merge] should repeat order (pre-fill cart) for logged-in user with initially filled cart and disallowed merging | after repeat | orderRepeat.cy.ts |
| 3-3-13 | [Logged Repeat With Prefilled And No Merge] should repeat order (pre-fill cart) for logged-in user with initially filled cart and disallowed merging | after repeat | orderRepeat.cy.ts |
| 3-3-14 | [Logged Repeat With Prefilled And No Merge] should repeat order (pre-fill cart) for logged-in user with initially filled cart and disallowed merging | after repeat | orderRepeat.cy.ts |
| 3-3-15 | [Anon Repeat With Empty] should repeat order (pre-fill cart) for unlogged user with initially empty cart | after repeat | orderRepeat.cy.ts |
| 3-3-16 | [Anon Repeat With Empty] should repeat order (pre-fill cart) for unlogged user with initially empty cart | after repeat | orderRepeat.cy.ts |
| 3-3-17 | [Anon Repeat With Empty] should repeat order (pre-fill cart) for unlogged user with initially empty cart | after repeat | orderRepeat.cy.ts |
| 3-3-18 | [Anon Repeat With Prefilled Merge] should repeat order (pre-fill cart) for unlogged user with initially filled cart and allowed merging | after repeat | orderRepeat.cy.ts |
| 3-3-19 | [Anon Repeat With Prefilled Merge] should repeat order (pre-fill cart) for unlogged user with initially filled cart and allowed merging | after repeat | orderRepeat.cy.ts |
| 3-3-20 | [Anon Repeat With Prefilled No Merge] should repeat order (pre-fill cart) for unlogged user with initially filled cart and disallowed merging | after repeat | orderRepeat.cy.ts |

## Snapshot Group - TRANSPORT_AND_PAYMENT

| Snapshot Id | Test Name | Snapshot Detail | File |
|-------------|-----------|-----------------|------|
| 4-0-0 | [Preselect T&P] should preselect transport and payment from last order for logged-in user | preselected transport and payment | lastOrderTransportAndPayment.cy.ts |
| 4-0-1 | [Preselect T&P] should preselect transport and payment from last order for logged-in user | after first change and refresh | lastOrderTransportAndPayment.cy.ts |
| 4-0-2 | [Preselect T&P] should preselect transport and payment from last order for logged-in user | after second change and refresh | lastOrderTransportAndPayment.cy.ts |
| 4-0-3 | [Change T&P And Preserve On Refresh] should change preselected transport and payment from last order for logged-in user and keep the new selection after refresh | after first change and refresh | lastOrderTransportAndPayment.cy.ts |
| 4-0-4 | [Change T&P And Preserve On Refresh] should change preselected transport and payment from last order for logged-in user and keep the new selection after refresh | after second change and refresh | lastOrderTransportAndPayment.cy.ts |
| 4-1-0 | [Select Payment] should select payment on delivery | after payment selection | paymentSelect.cy.ts |
| 4-1-1 | [Select Payment] should select payment on delivery | after changing payment selection | paymentSelect.cy.ts |
| 4-1-2 | [Select Payment] should select payment on delivery | after selecting | paymentSelect.cy.ts |
| 4-1-3 | [Select Payment] should select payment on delivery | after removing | paymentSelect.cy.ts |
| 4-1-4 | [Select Payment] should select payment on delivery | after selecting | paymentSelect.cy.ts |
| 4-1-5 | [Select Payment] should select payment on delivery | after removing | paymentSelect.cy.ts |
| 4-1-6 | [Select Payment] should select payment on delivery | after selecting | paymentSelect.cy.ts |
| 4-1-7 | [Select Payment] should select payment on delivery | after removing transport | paymentSelect.cy.ts |
| 4-1-8 | [Select And Change Payment] should select a payment, deselect it, and then change the payment option | after changing payment selection | paymentSelect.cy.ts |
| 4-1-9 | [Select And Change Payment] should select a payment, deselect it, and then change the payment option | after selecting | paymentSelect.cy.ts |
| 4-1-10 | [Select And Change Payment] should select a payment, deselect it, and then change the payment option | after removing | paymentSelect.cy.ts |
| 4-1-11 | [Select And Change Payment] should select a payment, deselect it, and then change the payment option | after selecting | paymentSelect.cy.ts |
| 4-1-12 | [Select And Change Payment] should select a payment, deselect it, and then change the payment option | after removing | paymentSelect.cy.ts |
| 4-1-13 | [Select And Change Payment] should select a payment, deselect it, and then change the payment option | after selecting | paymentSelect.cy.ts |
| 4-1-14 | [Select And Change Payment] should select a payment, deselect it, and then change the payment option | after removing transport | paymentSelect.cy.ts |
| 4-1-15 | [Remove Payment Repeated Click] should remove payment using repeated clicks | after selecting | paymentSelect.cy.ts |
| 4-1-16 | [Remove Payment Repeated Click] should remove payment using repeated clicks | after removing | paymentSelect.cy.ts |
| 4-1-17 | [Remove Payment Repeated Click] should remove payment using repeated clicks | after selecting | paymentSelect.cy.ts |
| 4-1-18 | [Remove Payment Repeated Click] should remove payment using repeated clicks | after removing | paymentSelect.cy.ts |
| 4-1-19 | [Remove Payment Repeated Click] should remove payment using repeated clicks | after selecting | paymentSelect.cy.ts |
| 4-1-20 | [Remove Payment Repeated Click] should remove payment using repeated clicks | after removing transport | paymentSelect.cy.ts |
| 4-1-21 | [Remove Payment Button Click] should remove payment using reset button | after selecting | paymentSelect.cy.ts |
| 4-1-22 | [Remove Payment Button Click] should remove payment using reset button | after removing | paymentSelect.cy.ts |
| 4-1-23 | [Remove Payment Button Click] should remove payment using reset button | after selecting | paymentSelect.cy.ts |
| 4-1-24 | [Remove Payment Button Click] should remove payment using reset button | after removing transport | paymentSelect.cy.ts |
| 4-1-25 | [Remove & Select New T&P] should remove transport to remove payment as well, and then allow to select transport incompatible with previous payment | after selecting | paymentSelect.cy.ts |
| 4-1-26 | [Remove & Select New T&P] should remove transport to remove payment as well, and then allow to select transport incompatible with previous payment | after removing transport | paymentSelect.cy.ts |
| 4-2-0 | [Transport Home] should select transport to home | after selecting | transportSelect.cy.ts |
| 4-2-1 | [Transport Home] should select transport to home | after selecting | transportSelect.cy.ts |
| 4-2-2 | [Transport Home] should select transport to home | after selecting | transportSelect.cy.ts |
| 4-2-3 | [Transport Home] should select transport to home | after removing | transportSelect.cy.ts |
| 4-2-4 | [Transport Home] should select transport to home | after selecting | transportSelect.cy.ts |
| 4-2-5 | [Transport Home] should select transport to home | after removing | transportSelect.cy.ts |
| 4-2-6 | [Transport Home] should select transport to home | cart page with enough products | transportSelect.cy.ts |
| 4-2-7 | [Personal Collection] should select personal pickup transport | after selecting | transportSelect.cy.ts |
| 4-2-8 | [Personal Collection] should select personal pickup transport | after selecting | transportSelect.cy.ts |
| 4-2-9 | [Personal Collection] should select personal pickup transport | after removing | transportSelect.cy.ts |
| 4-2-10 | [Personal Collection] should select personal pickup transport | after selecting | transportSelect.cy.ts |
| 4-2-11 | [Personal Collection] should select personal pickup transport | after removing | transportSelect.cy.ts |
| 4-2-12 | [Personal Collection] should select personal pickup transport | cart page with enough products | transportSelect.cy.ts |
| 4-2-13 | [Change Transport] should select a transport, deselect it, and then change the transport option | after selecting | transportSelect.cy.ts |
| 4-2-14 | [Change Transport] should select a transport, deselect it, and then change the transport option | after removing | transportSelect.cy.ts |
| 4-2-15 | [Change Transport] should select a transport, deselect it, and then change the transport option | after selecting | transportSelect.cy.ts |
| 4-2-16 | [Change Transport] should select a transport, deselect it, and then change the transport option | after removing | transportSelect.cy.ts |
| 4-2-17 | [Change Transport] should select a transport, deselect it, and then change the transport option | cart page with enough products | transportSelect.cy.ts |
| 4-2-18 | [Remove Transport Repeated Click] should be able to remove transport using repeated clicks | after selecting | transportSelect.cy.ts |
| 4-2-19 | [Remove Transport Repeated Click] should be able to remove transport using repeated clicks | after removing | transportSelect.cy.ts |
| 4-2-20 | [Remove Transport Repeated Click] should be able to remove transport using repeated clicks | after selecting | transportSelect.cy.ts |
| 4-2-21 | [Remove Transport Repeated Click] should be able to remove transport using repeated clicks | after removing | transportSelect.cy.ts |
| 4-2-22 | [Remove Transport Repeated Click] should be able to remove transport using repeated clicks | cart page with enough products | transportSelect.cy.ts |
| 4-2-23 | [Remove Transport Button Click] should remove transport using reset button | after selecting | transportSelect.cy.ts |
| 4-2-24 | [Remove Transport Button Click] should remove transport using reset button | after removing | transportSelect.cy.ts |
| 4-2-25 | [Remove Transport Button Click] should remove transport using reset button | cart page with enough products | transportSelect.cy.ts |
| 4-2-26 | [Anon No Transport Empty Cart] should redirect to cart page and not display transport options if cart is empty and user is not logged in | cart page with enough products | transportSelect.cy.ts |
| 4-2-27 | [Transport Fee] should change price for transport when cart is large enough for transport to be free | cart page with enough products | transportSelect.cy.ts |

## Snapshot Group - VISITS

| Snapshot Id | Test Name | Snapshot Detail | File |
|-------------|-----------|-----------------|------|
| 5-0-0 | [Homepage] should visit homepage with screenshot | homepage | simpleVisitsWithScreenshots.cy.ts |
| 5-0-1 | [Homepage] should visit homepage with screenshot | product detail | simpleVisitsWithScreenshots.cy.ts |
| 5-0-2 | [Homepage] should visit homepage with screenshot | category detail | simpleVisitsWithScreenshots.cy.ts |
| 5-0-3 | [Homepage] should visit homepage with screenshot | stores page | simpleVisitsWithScreenshots.cy.ts |
| 5-0-4 | [Homepage] should visit homepage with screenshot | blog article detail | simpleVisitsWithScreenshots.cy.ts |
| 5-0-5 | [Product Detail] should visit product detail with screenshot | product detail | simpleVisitsWithScreenshots.cy.ts |
| 5-0-6 | [Product Detail] should visit product detail with screenshot | category detail | simpleVisitsWithScreenshots.cy.ts |
| 5-0-7 | [Product Detail] should visit product detail with screenshot | stores page | simpleVisitsWithScreenshots.cy.ts |
| 5-0-8 | [Product Detail] should visit product detail with screenshot | blog article detail | simpleVisitsWithScreenshots.cy.ts |
| 5-0-9 | [Category Detail] should visit category detail with screenshot | category detail | simpleVisitsWithScreenshots.cy.ts |
| 5-0-10 | [Category Detail] should visit category detail with screenshot | stores page | simpleVisitsWithScreenshots.cy.ts |
| 5-0-11 | [Category Detail] should visit category detail with screenshot | blog article detail | simpleVisitsWithScreenshots.cy.ts |
| 5-0-12 | [Stores] should visit stores page with screenshot | stores page | simpleVisitsWithScreenshots.cy.ts |
| 5-0-13 | [Stores] should visit stores page with screenshot | blog article detail | simpleVisitsWithScreenshots.cy.ts |
| 5-0-14 | [Blog Detail] should visit blog article detail with screenshot | blog article detail | simpleVisitsWithScreenshots.cy.ts |
