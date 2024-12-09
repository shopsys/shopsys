# Time-Limited Price Lists

This guide explains how administrators can use the time-limited price lists to manage special product prices effectively.

[TOC]

## Overview

Time-limited price lists allow administrators to set specific prices for products that are valid for a defined time range.
This feature is ideal for managing promotions, sales campaigns, or other time-sensitive pricing strategies.

## Managing the time-limited price lists

Time-limited price lists can be managed in the `Pricing -> Price lists` section.

Each price list has its own validity period, so it's possible to seamlessly create follow-up price promotions.
If a product is assigned to the price list, the price is automatically used as a new selling price for the specified time period (no manual action is required).

The special price for the product is used only if the entered price is actually lower than the basic price.
That allows to create a promotion only for a specific customer group, or deal with a complex pricing strategy.

## Priority of the multiple active price lists

Active price lists are prioritized by the last update date.
This means that the most recently updated price list has the highest priority.
The default sorting on the price list page is by the last update date, so the most recently updated price list is at the top.

Currently used price for a current product is highlighted in the price list overview table on the product detail page in the administration.

!!! warning

    When a new product is added or removed from a price list, the price list is effectively updated, and the priority is changed.
    Keep that in mind to avoid unexpected behavior.

### Behavior of product variants

Only a specific variant of the product can be assigned to the price list due to the fact that the main variant is not sellable and does not have any price set.

Each variant price is managed and determined separately.
That means that each variant can be placed on a different price list, and the price of a specific variant is determined by the price list containing the variant with the highest priority.

The price "from" displayed on the product list page is then calculated as the lowest determined price of all variants of the product.
