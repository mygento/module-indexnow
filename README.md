# Mygento_IndexNow module

## Функционал
Модуль интегрирует протокол IndexNow в Magento, что позволяет ускорить индексацию вашего сайта поисковыми системами, поддерживающими IndexNow.
Этот модуль будет автоматически уведомлять поисковые системы о новом или обновленном контенте (страницах CMS, категориях и товарах) на вашем сайте.

## Зависимости
- Magento_Config
- Magento_Backend

## Backend

## Плагины

## События
- `Mygento\IndexNow\Observer\ProductSaveAfter` - отслеживает событие сохранения товара и отправляет данные в сервисы IndexNow.
- `Mygento\IndexNow\Observer\CategorySaveAfter` - отслеживает событие сохранения категории и отправляет данные в сервисы IndexNow.
- `Mygento\IndexNow\Observer\CmsPageSaveAfter` - отслеживает событие сохранения CMS-страницы и отправляет данные в сервисы IndexNow.

## Cron-процессы

## Консольные команды

## Конфигурация
Stores > Configuration Mygento -> IndexNow
