import { brand_name1, cart_total_price1, cart_total_price2, category1_name, category1_url, product1_catnum, product1_name, product1_name_prefix_suffix, product1_url_prefix_suffix, product2_catnum, product2_name, product2_url, product3_catnum, product3_name, url_brand_overview, url_cart } from "../../fixtures/demodata"
import { checkProductInCart, checkTotalPriceInCart } from "../Functions/CartPage"
import { checkProductAndGoToCartFromFloatingWindow } from "../Functions/CartPopupWindow"
import { clickOnCategoryFromMenu, searchProductByNameTypeEnterAndCheckResult } from "../Functions/HeaderPage"
import { addProductToCartFromPromotedProductsOnHomepage, productClickFromPromotedProductsOnHomepage } from "../Functions/HomepagePage"
import { addProductToCartFromProductDetail, addProductVariantToCartFromProductDetail } from "../Functions/ProductDetailPage"
import { addProductToCartFromProductList } from "../Functions/ProductListPage"

describe('Tests for adding products to cart', () => {
    beforeEach(() => {
    cy.intercept('POST' , '/graphql/').as('preview')
		cy.visit('/')
	})
    it('Product detail - Adding product to cart from product detail and check product in cart', () => {
        productClickFromPromotedProductsOnHomepage(product1_catnum,product1_name)
        cy.url().should('contain', product1_url_prefix_suffix)
        addProductToCartFromProductDetail()
        checkProductAndGoToCartFromFloatingWindow(product1_name_prefix_suffix)
        checkProductInCart(product1_catnum,product1_name_prefix_suffix)
        checkTotalPriceInCart(cart_total_price1)
        cy.url().should('contain', url_cart)
    })

    it('Homepage promoted products - Adding product to cart from promoted products on homepage and check product in cart', () => {
       addProductToCartFromPromotedProductsOnHomepage(product1_catnum)
       checkProductAndGoToCartFromFloatingWindow(product1_name_prefix_suffix)
       checkProductInCart(product1_catnum,product1_name_prefix_suffix)
       checkTotalPriceInCart(cart_total_price1)
       cy.url().should('contain', url_cart)
    });

    it('Product list - Adding product to cart from product list and check product in cart', () => {
      clickOnCategoryFromMenu(category1_name)
      cy.url().should('contain', category1_url)
      addProductToCartFromProductList(product1_catnum)
      checkProductAndGoToCartFromFloatingWindow(product1_name_prefix_suffix)
      checkProductInCart(product1_catnum,product1_name_prefix_suffix)
      checkTotalPriceInCart(cart_total_price1)
      cy.url().should('contain', url_cart)
    });

    it('Search results - Adding product to cart from search results list and check product in cart', () => {
      searchProductByNameTypeEnterAndCheckResult(product1_name,product1_catnum)
      addProductToCartFromProductList(product1_catnum)
      checkProductAndGoToCartFromFloatingWindow(product1_name_prefix_suffix)
      checkProductInCart(product1_catnum,product1_name_prefix_suffix)
      checkTotalPriceInCart(cart_total_price1)
      cy.url().should('contain', url_cart)
    });

    it('Brand list - Adding product to cart from brand list and check product in cart', () => {
      cy.visit(url_brand_overview)
      cy.wait('@preview')
      cy.get('[data-testid="blocks-simplenavigation-22"]').contains(brand_name1).click()
      addProductToCartFromProductList(product1_catnum)
      checkProductAndGoToCartFromFloatingWindow(product1_name_prefix_suffix)
      checkProductInCart(product1_catnum,product1_name_prefix_suffix)
      checkTotalPriceInCart(cart_total_price1)
      cy.url().should('contain', url_cart)
    });

    it('Product variant - Adding variant product to cart from product detail and check product in cart', () => {
      productClickFromPromotedProductsOnHomepage(product2_catnum,product2_name)
      cy.url().should('contain', product2_url)
      addProductVariantToCartFromProductDetail(product3_catnum)
      checkProductAndGoToCartFromFloatingWindow(product3_name)
      checkProductInCart(product3_catnum,product3_name)
      checkTotalPriceInCart(cart_total_price2)
      cy.url().should('contain', url_cart)
    });
})