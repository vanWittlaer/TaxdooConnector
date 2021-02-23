# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.0.5] - 2021-02-23
### Added
- Config parameter and logic to exclude one or more channels from being
reported to Taxdoo
### Changed
- Plugin bootstrap cache clearing logic
### Fixed
- 404/410 error in backend when installing the plugin for the first time
using the Shopware backend plugin manager

## [0.0.4] - 2021-02-09
### Added
- support for PickwareERP
### Changed
- use Taxdoo API endpoint warehouses/standard to retrieve default warehouse
### Fixed
- quantity for canceled orders/items

## [0.0.3] - 2021-01-26
### Added
- Magnalister: add channel 'tradoria' as Rakuten (RAK)
- Confiugration: add a method to supply Taxdoo defined default warehouse, if none
is defined in the plugin config
### Changed
- Use Taxdoo defined default warehouse as sender address when reporting orders,
  if none is defined in plugin config.

## [0.0.2] - 2021-01-21
Initial alpha release
- Plugin configuration
- Order reporting daily/monthly/history (bi-annual)
- Product export
- Order/product cleanup utility
- Supports Shopware (vanilla)
- Supports channel Magnalister (amazon, ebay, otto, real)