# Stoneholl Previous / Next Order Navigation

Free MIT module `Stoneholl_OrderNavigation`, package `stoneholl/module-order-navigation`.

## Features

Adds Previous order / Next order links beside native Magento Admin order actions and a navigation strip in the
Information tab. Both placements share one request-local result. Links use native action styling, escaped target
labels and tooltips; sequence ends are noninteractive.

Neighbors are the nearest existing order entity IDs in the exact same store view, across all statuses. Gaps and
custom order-number formats work. Grid filters, sorting, browser history and creation dates do not affect this
record sequence. There is no wraparound. Each request resolves current neighbors; deleted or newly denied targets
follow Magento's native controller behavior without retrying another order.

## Requirements and tested compatibility

Core `^1.0` is required and fully offline. Connector is optional. No account, pairing or paid entitlement is required.
The feature defaults disabled and supports Default Config scope only.

| Magento Open Source | PHP | Database |
| --- | --- | --- |
| 2.3.7-p4 | 7.4.33 | MySQL 5.7.44 |
| 2.4.9 | 8.5.9 | MariaDB 12.3.2 |

These exact pairs passed native installation, DI and targeted runtime/Admin checks. The runtime candidate was
Composer-installed with immutable Core 1.0.0 in disposable databases using existing locked Magento dependencies.
PHP 7.4/8.0/8.5 parsing and static CI do not certify other runtime pairs. Intermediate versions, Adobe Commerce and
third-party Admin themes remain untested. Declared dependency ranges are not a complete compatibility matrix.

## Installation and configuration

Install with your normal approved Magento deployment procedure:

```sh
composer require stoneholl/module-order-navigation:^1.0
bin/magento module:enable Stoneholl_Core Stoneholl_OrderNavigation
bin/magento setup:upgrade --safe-mode=1
```

Keep existing module flags intact during upgrade; follow your backup/deployment policy. Compile DI, deploy static
content and clean relevant caches as appropriate. In this project's Docker environment, run Magento and Composer
inside the existing PHP service.

At Default Config scope, open Stores > Configuration > Stoneholl > Previous / Next Order Navigation > General and
enable Order Navigation. Website/store overrides are unsupported. Configuration requires
`Stoneholl_OrderNavigation::config`; rendering requires both `Magento_Sales::actions_view` and
`Stoneholl_OrderNavigation::view`. Refresh Admin sessions after role changes. Configuration permission alone does
not grant order visibility. Native persisted-role HTTP checks cover these independent permissions on both pairs.

## Security, privacy and limits

Native Open Source action ACL does not provide per-order isolation. For roles restricted by store, ownership or
third-party order rules, withhold navigation view permission or keep the feature disabled until a verified
integration applies the same visibility predicates before neighbor selection and authorizes the target controller.
No third-party restriction adapter is included. Same-store selection is not row-level authorization.

No networking, telemetry, operational persistence, schema, cron or order mutation. Only the enable setting is saved.
The module reuses the current native order block and performs two bound scalar neighbor queries. Table/index names
come from ResourceConnection and are quoted. Queries use Magento's existing store index and its implicit InnoDB
primary-key suffix to avoid a MySQL 5.7 backward scan. Standard native InnoDB schema is required; a renamed/missing
index gives a safe unavailable state without an unbounded fallback. No index, full order load, count or join is added.

Disabled/denied rendering performs no feature reads. Cold store-repository work may add metadata queries. Results
are memoized only within the request. Fixed diagnostic codes omit exception text, order/customer data and SQL binds.
Store 0, deleted/null stores and failures show unavailable states instead of false sequence ends. Native secret keys
protect backend URLs. Narrow Admin pages may scroll horizontally; synthetic long labels wrap inside native actions.
The layout tests do not supply a bundled translation. Keyboard shortcuts and filter-aware navigation are not included.

## Removal and support

Disable the feature/module, remove the Composer package and rebuild deployment artifacts through the normal workflow.
Remove only `stoneholl_order_navigation/general/enabled` configuration rows and any deliberate override for that
exact path through an approved maintenance procedure. There is no operational schema/data to remove. Do not delete
orders or shared Core while another package needs it.

Report issues at https://github.com/Stoneholl/module-order-navigation/issues with exact Magento/PHP versions and
reproduction steps. Do not include credentials, secret-key URLs or customer/order data. Licensed under MIT.
