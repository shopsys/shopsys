## Helping queries
Visible product details
```sql
SELECT '/' || fu.slug
FROM "friendly_urls" fu
JOIN products p ON p.id = fu.entity_id AND p.variant_type != 'main'
JOIN product_visibilities pv ON pv.product_id = p.id AND pv.domain_id = fu.domain_id and pv.pricing_group_id = 1
WHERE fu."route_name" = 'front_product_detail' AND fu."main" = '1' AND fu."domain_id" = 1
LIMIT 500
```

Visible categories
```sql
SELECT '/' || fu.slug
FROM "friendly_urls" fu
JOIN categories c ON c.id = fu.entity_id AND c.visible = TRUE
WHERE fu."route_name" = 'front_product_list' AND fu."main" = '1' AND fu."domain_id" = '1'
LIMIT 500
```
