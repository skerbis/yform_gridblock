<?php
if (rex::isBackend() && rex::getUser()) {
    // Template-Pfad für YFORM registrieren
    rex_yform::addTemplatePath($this->getPath('ytemplates'));

    // Assets von Gridblock bei Bedarf einbinden
    if (rex_request('page') === 'yform/manager/data_edit') {
        $addon = rex_addon::get('gridblock');
        rex_view::addCssFile($addon->getAssetsUrl('style.css'));
        if ($addon->getProperty('darkmode')) {
            rex_view::addCssFile($addon->getAssetsUrl('style-darkmode.css'));
        }
        rex_view::addJsFile($addon->getAssetsUrl('sortable.min.js'));
        rex_view::addJsFile($addon->getAssetsUrl('script.js'));
    }
}

// Klasse für YFORM registrieren
rex_yform::addValueField('gridblock', 'YForm_Gridblock_Value');
