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
    // Prüfen ob Daten vorhanden sind
    if (!isset($_POST['REX_INPUT_VALUE']) || !is_array($_POST['REX_INPUT_VALUE'])) {
        $this->setValue('');
        return;
    }

    // Alle wichtigen Gridblock-Werte sammeln
    $values = [];
        
    // Template und Optionen
    if (isset($_POST['REX_INPUT_VALUE'][17])) {
        $values[17] = $_POST['REX_INPUT_VALUE'][17];
    }
    if (isset($_POST['REX_INPUT_VALUE'][18])) {
        $values[18] = $_POST['REX_INPUT_VALUE'][18];
    }
    if (isset($_POST['REX_INPUT_VALUE'][19])) {
        $values[19] = $_POST['REX_INPUT_VALUE'][19];
    }
    if (isset($_POST['REX_INPUT_VALUE'][20])) {
        $values[20] = $_POST['REX_INPUT_VALUE'][20];
    }

    // Spalteninhalte 1-16
    for ($i = 1; $i <= 16; $i++) {
        if (isset($_POST['REX_INPUT_VALUE'][$i])) {
            $values[$i] = $_POST['REX_INPUT_VALUE'][$i];
        }
    }
        
    // Nur speichern wenn Werte vorhanden sind
    if (!empty($values)) {
        $this->setValue(json_encode($values));
    } else {
        $this->setValue('');
    }
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
