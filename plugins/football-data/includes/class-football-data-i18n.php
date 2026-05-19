<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Football_Data_I18n
{
    public function register_strings(): void
    {
        if (!function_exists('pll_register_string')) {
            return;
        }

        foreach ($this->strings() as $key => $value) {
            pll_register_string('football_data_' . $key, $value, 'Football Data');
        }
    }

    public function translate(string $key): string
    {
        $strings = $this->strings();
        $fallback = $strings[$key] ?? $key;

        if (function_exists('pll__')) {
            return pll__($fallback);
        }

        return $fallback;
    }

    private function strings(): array
    {
        return [
            'plugin_name' => 'Football Data',
            'mock_mode_status' => 'мок-режим включен, данные берутся из локального JSON.',
            'carbon_missing' => 'Carbon Fields не найден. Запустите установку Composer в папке плагина.',
            'settings_api_title' => 'Настройки API-Football',
            'settings_save' => 'Сохранить настройки',
            'entity_leagues' => 'Турниры',
            'entity_teams' => 'Команды',
            'entity_players' => 'Игроки',
            'entity_fixtures' => 'Матчи',
            'entity_bookmakers' => 'Букмекеры',
        ];
    }
}
