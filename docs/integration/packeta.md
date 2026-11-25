# Packeta

Packeta is a logistic service that provides delivery points for customers to pick up their orders and other delivery services.
Shopsys Platform provides integration to this service, which allows customers to choose a delivery point during the checkout process.
More information about Packeta can be found on their [website](https://www.packeta.com/global) or in their [documentation](https://docs.packetery.com).

## Installation

Packeta is currently part of the Shopsys Platform, so there is no need to install it.

## Configuration

Packeta is configurable via ENV variables, the following ENV variables should be set:

- PACKETERY_API_KEY (string)
- PACKETERY_API_PASSWORD (string)
- PACKETERY_ENABLED (bool)
- PACKETERY_SENDER (string)
- PACKETERY_REST_API_URL (string)

The `PACKETERY_API_KEY` is used by the storefront widget and by the backend to download the pickup point feeds.

## Pickup point opening hours

The `PacketeryPickupPointImportCronModule` downloads the [Packeta pickup point feeds](https://docs.packeta.com/docs/pudo-delivery/packeta-pudos) (branches and Z-BOXes of all the countries allowed for the API key) four times a day, as Packeta recommends, and stores the opening hours of every point.
The expected delivery date of a Packeta transport is then postponed to the first day the pickup point selected in the cart is open, so the customer sees the first day the parcel can actually be picked up.

!!! note

    Until the first import runs, or when the selected point is missing in the feeds, the expected delivery date does not take the opening hours into account.

## Mandatory data

Packeta requires weight to be set for each product in the order.
This can be done by setting the weight in the product detail in the administration.
If the weight is not set, the order will fail to be sent to Packeta.
