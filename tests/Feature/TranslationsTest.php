<?php

test('read more translation key exists in english', function () {
    app()->setLocale('en');
    expect(__('blogr::blogr.ui.read_more'))->toBe('Read more');
});

test('read more translation key exists in french', function () {
    app()->setLocale('fr');
    expect(__('blogr::blogr.ui.read_more'))->toBe('Lire la suite');
});

test('no series translation key exists in english', function () {
    app()->setLocale('en');
    expect(__('blogr::blogr.series.no_series'))->toBe('No series published yet');
});

test('no series translation key exists in french', function () {
    app()->setLocale('fr');
    expect(__('blogr::blogr.series.no_series'))->toBe('Aucune série publiée pour le moment');
});

test('save settings translation key exists in english', function () {
    app()->setLocale('en');
    expect(__('blogr::blogr.settings.save'))->toBe('Save Settings');
});

test('save settings translation key exists in french', function () {
    app()->setLocale('fr');
    expect(__('blogr::blogr.settings.save'))->toBe('Enregistrer les paramètres');
});

