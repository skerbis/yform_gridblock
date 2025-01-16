<?php
class rex_yform_value_gridblock extends rex_yform_value_abstract
{
    private $grid;
    private $sliceId;
    
    function init()
    {
        // Nur Gridblock initialisieren
        $this->grid = new rex_gridblock();
    }

    function enterObject()
    {
        // ID ist hier verfügbar
        $this->sliceId = 'yform_gb_field_' . $this->getId();
        
        // Session Vars setzen
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
        
        // JSON Werte verarbeiten
        if ($value) {
            $values = json_decode($value, true);
            
            // Gridblock Values setzen
            foreach($values as $key => $val) {
                $this->grid->values[$key] = $val;  
            }
        }
        
        // Erlaubte Templates filtern
        $allowedTemplates = $this->getElement('templates');
        if($allowedTemplates) {
            // Template IDs in Array konvertieren
            $templateIds = array_map('trim', explode(',', $allowedTemplates));
            
            // Templates in Session speichern für Gridblock
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
        // Werte aus POST sammeln
        $values = [];
        foreach($_POST as $key => $value) {
            if(strpos($key, 'REX_INPUT_VALUE') === 0) {
                $values[$key] = $value;
            }
        }
        
        // Als JSON speichern
        $this->setValue(json_encode($values));
    }
    
    // Frontend Ausgabe generieren
    public function getGridblockOutput() 
    {
        if($this->getValue()) {
            $this->grid->getSliceValues($this->sliceId);
            return $this->grid->getModuleOutput();
        }
        return '';
    }

    function getDefinitions(): array 
    {
        return [
            'type' => 'value',
            'name' => 'gridblock',
            'values' => [
                'name'  => ['type' => 'name', 'label' => rex_i18n::msg('yform_values_defaults_name')],
                'label' => ['type' => 'text', 'label' => rex_i18n::msg('yform_values_defaults_label')],
                'templates' => ['type' => 'text', 'label' => rex_i18n::msg('yform_gridblock_templates'), 'notice' => 'Template IDs (comma separated)'],
            ],
            'description' => rex_i18n::msg('yform_values_gridblock_description'),
            'db_type' => ['text', 'mediumtext'],
            'multi_edit' => false,
        ];
    }

    public static function getSearchField($params)
    {
        $params['searchForm']->setValueField('text', ['name' => $params['field']->getName(), 'label' => $params['field']->getLabel()]);
    }

    public static function getSearchFilter($params)
    {
        $sql = rex_sql::factory();
        $value = $params['value'];
        $field = $params['field']->getName();

        if ($value == '(empty)') {
            return ' (' . $sql->escapeIdentifier($field) . ' = "" or ' . $sql->escapeIdentifier($field) . ' IS NULL) ';
        }
        if ($value == '!(empty)') {
            return ' (' . $sql->escapeIdentifier($field) . ' <> "" and ' . $sql->escapeIdentifier($field) . ' IS NOT NULL) ';
        }

        return $sql->escapeIdentifier($field) . ' LIKE ' . $sql->escape('%' . $value . '%');
    }
}
