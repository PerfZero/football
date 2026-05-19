<?php

if (!defined('ABSPATH')) {
    fwrite(STDERR, "Run this file through WP-CLI: wp eval-file tools/import-polylang-xliff-strings.php -- /path/to/xliff-dir\n");
    exit(1);
}

$dir = $args[0] ?? '/tmp/polylang-translated';

if (!is_dir($dir)) {
    throw new RuntimeException("Directory not found: {$dir}");
}

$locale_to_slug = [
    'en-US' => 'en',
    'es-ES' => 'es',
    'pt-PT' => 'pt',
    'fr-FR' => 'fr',
    'de-DE' => 'de',
];

$files = glob(rtrim($dir, '/') . '/*.xliff');
if (!$files) {
    throw new RuntimeException("No XLIFF files found in: {$dir}");
}

foreach ($files as $file) {
    $xml = file_get_contents($file);
    if ($xml === false) {
        throw new RuntimeException("Cannot read file: {$file}");
    }

    if (!preg_match('/trgLang="([^"]+)"/', $xml, $locale_match)) {
        throw new RuntimeException("Cannot detect target language in: {$file}");
    }

    $locale = $locale_match[1];
    $slug = $locale_to_slug[$locale] ?? null;
    if (!$slug) {
        WP_CLI::warning("Skipping unsupported locale {$locale} in {$file}");
        continue;
    }

    $language = get_term_by('slug', $slug, 'language');
    if (!$language) {
        WP_CLI::warning("Language term not found: {$slug}");
        continue;
    }

    preg_match_all(
        '/<segment>\s*<source><!\[CDATA\[(.*?)\]\]><\/source>\s*<target><!\[CDATA\[(.*?)\]\]><\/target>\s*<\/segment>/s',
        $xml,
        $matches,
        PREG_SET_ORDER
    );

    $strings = [];
    foreach ($matches as $match) {
        $source = str_replace(']]]]><![CDATA[>', ']]>', $match[1]);
        $target = str_replace(']]]]><![CDATA[>', ']]>', $match[2]);

        if ($source === '' || $target === '') {
            continue;
        }

        $strings[] = [wp_slash($source), wp_slash($target)];
    }

    update_term_meta((int) $language->term_id, '_pll_strings_translations', $strings);
    WP_CLI::success(sprintf('%s: imported %d strings for %s', basename($file), count($strings), $slug));
}

foreach ([
    'pll_languages_list',
    'pll_translated_slugs',
] as $transient) {
    delete_transient($transient);
    delete_site_transient($transient);
}

wp_cache_flush();
