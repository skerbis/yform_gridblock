<?php
class rex_yform_value_gridblock extends rex_yform_value_abstract
{
    private $grid;
    private $sliceId;
    
    function init()
    {
        $this->grid = new rex_gridblock();
    }

    function enterObject()
    {
        $this->sliceId = 'yform_gb_field_' . $this->getId();
        
        $_SESSION['gridRexVars'] = [
            'sliceID' => $this->sliceId,
            'artID' => rex_article::getCurrentId(),
            'tmplID' => rex_template::getDefaultId(),
            'clangID' => rex_clang::getCurrentId(),
            'ctypeID' => 1,
            'moduleID' => 0
        ];

        // Existierende Werte laden
        $value = $this->getValue();
        if ($value) {
            $this->grid->getSliceValues($this->sliceId);
        }

        // Template-Filter
        $allowedTemplates = $this->getElement('templates');
        if($allowedTemplates) {
            $templateIds = array_map('trim', explode(',', $allowedTemplates));
            $_SESSION['gridTemplates'] = $templateIds;
        }

        // Gridblock Formular generieren
        $input = $this->grid->getModuleInput();
        
        // In YFORM integrieren
        $this->params['form_output'][$this->getId()] = $this->parse(
            ['value.gridblock.tpl.php', 'value.defaultform.tpl.php'],
            [
                'type' => $this->getElement('type'),
                'name' => $this->getElement('name'), 
                'value' => $input,
                'error' => $this->params['warning_messages'][$this->getId()]
            ]
        );
    }
    
    function saveValue()
    {
        // Alle REX_INPUT_VALUE Felder aus dem POST sammeln
        $values = [];

        foreach($_POST as $key => $value) {
            // Filtere die REX_INPUT_VALUE Felder
            if (preg_match('/^REX_INPUT_VALUE/', $key)) {
                // Extrahiere die ID
                preg_match('/\[(\d+)\]/', $key, $matches);
                if (isset($matches[1])) {
                    $id = $matches[1];
                    $values[$id] = $value;
                }
            }
        }

        // Als JSON speichern wenn Daten vorhanden sind
        if (!empty($values)) {
            $this->setValue(json_encode($values));
        }
    }

    // ... Rest der Klasse bleibt gleich
}
