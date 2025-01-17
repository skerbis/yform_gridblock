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
            $values = json_decode($value, true);
            if(is_array($values)) {
                // Werte direkt in REX_INPUT_VALUE speichern
                foreach($values as $key => $val) {
                    $_REQUEST['REX_INPUT_VALUE'][$key] = $val;
                }
            }
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

        if (isset($_POST['REX_INPUT_VALUE']) && is_array($_POST['REX_INPUT_VALUE'])) {
            $values = $_POST['REX_INPUT_VALUE'];
            
            // Als JSON speichern wenn Daten vorhanden sind
            if (!empty($values)) {
                $this->setValue(json_encode($values));
            }
        }
    }
    
    public function getGridblockOutput() 
    {
        if($this->getValue()) {
            $values = json_decode($this->getValue(), true);
            if(is_array($values)) {
                // Werte direkt in REX_INPUT_VALUE speichern
                foreach($values as $key => $val) {
                    $_REQUEST['REX_INPUT_VALUE'][$key] = $val;
                }
            }
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
