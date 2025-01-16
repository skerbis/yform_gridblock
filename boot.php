<?php
if (rex::isBackend() && rex::getUser()) {
    // Template-Pfad für YFORM registrieren  
    rex_yform::addTemplatePath($this->getPath('ytemplates'));

    // Nur die Gridblock CSS Datei einbinden, wenn wir im YFORM Edit sind
    if (rex_request('page') === 'yform/manager/data_edit') {
        $addon = rex_addon::get('gridblock');
        rex_view::addCssFile($addon->getAssetsUrl('style.css'));
        if ($addon->getProperty('darkmode')) {
            rex_view::addCssFile($addon->getAssetsUrl('style-darkmode.css'));
        }
    }
}
