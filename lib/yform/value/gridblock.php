
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

        $value = $this->getValue();
        if ($value) {
            $this->grid->values = json_decode($value, true);
        }

        $allowedTemplates = $this->getElement('templates');
        if($allowedTemplates) {
            $templateIds = array_map('trim', explode(',', $allowedTemplates));
            $_SESSION['gridTemplates'] = $templateIds;
        }

        $input = $this->grid->getModuleInput();
        
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
        echo '<pre>';
        var_dump($_POST);
        echo '</pre>';
       exit;
        // error_log(print_r($_POST, true), 3, rex_path::log('gridblock.log'));

        //Sicherstellen, dass $_POST['REX_INPUT_VALUE'] existiert und ein Array ist
        if (!isset($_POST['REX_INPUT_VALUE']) || !is_array($_POST['REX_INPUT_VALUE'])) {
            $this->setValue('');
            return;
        }

        $values = [];
        
        // Gehe alle geposteten Werte durch und filtere relevante
        foreach ($_POST['REX_INPUT_VALUE'] as $key => $value) {
            // Stelle sicher, dass der Schlüssel ein Integer ist
            if (is_int($key)) {
                 // Verarbeite jeden Wert einzeln
                $values[$key] = $this->sanitizeValue($value); // Sichere Behandlung der Werte
            }
        }

        // Speichern nur wenn Werte vorhanden sind
        if (!empty($values)) {
           $this->setValue(json_encode($values));
        } else {
            $this->setValue('');
        }
    }
    
    private function sanitizeValue($value) {
        if (is_array($value)) {
           return array_map([$this, 'sanitizeValue'], $value);
        }
        
        return htmlspecialchars(trim($value)); // Standard htmlspecialchars und trim
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
