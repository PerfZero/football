# Phase 03 — WordPress Content Model

## Цель

Спроектировать сущности WordPress так, чтобы страницы игроков, команд, турниров, матчей и букмекеров были полноценными SEO-страницами, а не только архивами.

## Custom Post Types

- `football_league`: турниры, чемпионаты, кубки.
- `football_team`: команды/участники.
- `football_player`: игроки.
- `football_fixture`: матчи.
- `football_bookmaker`: букмекеры.
- `post`: новости WordPress, связанные с футбольными сущностями.

## Taxonomies

- `football_country`: страна.
- `football_season`: сезон.
- `football_position`: позиция игрока.
- `football_league_type`: league/cup.
- `football_bookmaker_feature`: бонусы, live, мобильное приложение, быстрые выплаты.
- `football_news_topic`: трансферы, травмы, матч, обзор, прогноз.

## Meta Fields

### Player

- API player ID.
- Фото.
- Имя, фамилия, возраст.
- Дата рождения.
- Место рождения.
- Страна рождения.
- Гражданство.
- Рост, вес.
- Номер.
- Позиция.
- Текущая команда.
- Средняя оценка за матч.
- Статистика по сезонам.

### Team

- API team ID.
- Логотип.
- Страна.
- Стадион.
- Текущий состав.
- Матчи.
- Турниры.
- Новости.

### League

- API league ID.
- Страна.
- Сезон.
- Таблица.
- Туры.
- Матчи.
- Команды-участники.
- Лучшие игроки.

### Fixture

- API fixture ID.
- Команды.
- Турнир.
- Дата/время.
- Стадион.
- Счет.
- Статус.
- События.
- Составы.
- Статистика.
- Коэффициенты.

### Bookmaker

- API bookmaker ID, если есть.
- Логотип.
- Рейтинг.
- Бонус.
- Минимальный депозит.
- Страны.
- Особенности.
- Партнерская ссылка.
- Обзор.

## URL Structure

С учетом языковых папок:

- `/ru/news/...`
- `/ru/leagues/premier-league/`
- `/ru/leagues/premier-league/standings/`
- `/ru/teams/manchester-city/`
- `/ru/players/erling-haaland/`
- `/ru/matches/manchester-city-real-madrid-2024-05-17/`
- `/ru/bookmakers/`
- `/ru/bookmakers/winline/`

## Результат фазы

- Зарегистрированные CPT и taxonomies.
- Поля метаданных.
- Черновая структура permalink.
- Связи между сущностями.
