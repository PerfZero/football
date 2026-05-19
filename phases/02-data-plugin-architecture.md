# Phase 02 — Data Plugin Architecture

## Цель

Сделать отдельный WordPress-плагин `football-data`, который отвечает за API, данные, кеш, настройки и служебную логику. Тема не должна напрямую ходить в API.

## Почему отдельный плагин

- Данные не зависят от темы.
- Можно менять дизайн без потери API-логики.
- Удобнее держать cron jobs, настройки ключа, кеш и синхронизацию.
- Проще масштабировать: игроки, команды, турниры, букмекеры, матчи, odds.

## Основные модули плагина

- `Settings`: API key, host, режим моков, лимиты, языки, debug.
- `ApiClient`: единая обертка запросов к API-Football.
- `Cache`: transient/object cache/custom tables для частых данных.
- `Sync`: ручная и автоматическая синхронизация.
- `CPT`: регистрация сущностей WordPress.
- `REST`: внутренние REST endpoints для фронтенда.
- `Blocks/Shortcodes`: блоки для таблиц, матчей, игроков, букмекеров.
- `Admin UI`: экраны настроек, статусы синхронизации, ошибки API.

## Предварительные API-группы

- Reference data:
  - `/countries`
  - `/timezone`
  - `/leagues/seasons`
  - `/odds/bookmakers`
  - `/odds/bets`
  - `/odds/live/bets`
- Competitions:
  - `/leagues`
  - `/standings`
  - `/fixtures/rounds`
- Teams:
  - `/teams`
  - `/players/squads`
- Players:
  - `/players`
  - `/players/profiles`
  - `/players/topscorers`
  - `/players/topassists`
  - `/players/topyellowcards`
  - `/players/topredcards`
- Matches:
  - `/fixtures`
  - `/fixtures/events`
  - `/fixtures/lineups`
  - `/fixtures/statistics`
  - `/fixtures/players`
  - `/fixtures/headtohead`
- Betting:
  - `/odds`
  - `/odds/live`
- Extra:
  - `/injuries`
  - `/predictions`
  - `/transfers`
  - `/trophies`
  - `/sidelined`

## Результат фазы

- Скелет плагина `plugins/football-data`.
- Экран настроек API.
- Мок-режим без реального ключа.
- Базовый `ApiClient` с логированием ошибок.
