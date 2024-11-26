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

## Mandatory data

Packeta requires weight to be set for each product in the order.
This can be done by setting the weight in the product detail in the administration.
If the weight is not set, the order will fail to be sent to Packeta.
