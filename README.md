# Mygento_IndexNow module

## Features
This module integrates the IndexNow protocol into Magento, allowing for faster indexing of your website by search engines that support IndexNow.
This module will automatically notify search engines about new or updated content (CMS pages, categories and products) on your website.

## Dependencies
- Magento_Config
- Magento_Backend

## Events
- `Mygento\IndexNow\Observer\ProductSaveAfter` - listens to product save events and sends data to IndexNow services.
- `Mygento\IndexNow\Observer\CategorySaveAfter` - listens to category save events and sends data to IndexNow services.
- `Mygento\IndexNow\Observer\CmsPageSaveAfter` - listens to CMS page save events and sends data to IndexNow services.

## Configuration
Stores -> Configuration -> Mygento -> IndexNow
