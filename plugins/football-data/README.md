# Football Data

WordPress-плагин для футбольных данных проекта Football.

## Что уже есть

- Русская админка `Football Data`.
- Carbon Fields для редактируемых полей сущностей.
- Настройки API-Football.
- Мок-режим без API-ключа.
- CPT:
  - турниры;
  - команды;
  - игроки;
  - матчи;
  - букмекеры.
- Таксономии:
  - страны;
  - сезоны;
  - позиции;
  - темы новостей;
  - особенности букмекеров.
- REST endpoints:
  - `/wp-json/football-data/v1/status`;
  - `/wp-json/football-data/v1/mock/players`;
  - `/wp-json/football-data/v1/mock/fixtures`;
  - `/wp-json/football-data/v1/mock/bookmakers`.
- Shortcodes:
  - `[football_data_status]`;
  - `[football_data_players]`;
  - `[football_data_matches]`;
  - `[football_data_bookmakers]`.

## Carbon Fields

Библиотека установлена через Composer:

```bash
docker run --rm -v /Users/denis/football/plugins/football-data:/app composer:2 require htmlburger/carbon-fields:^3.6
```

Поля регистрируются в:

`includes/class-football-data-carbon-fields.php`

## Как будет подключаться API

Когда появится ключ API-Football, он вводится в админке:

`Football Data -> Настройки API`

Пока ключ не задан или включен мок-режим, сайт использует локальный файл:

`data/mock-data.json`
