<?php
class rex_yform_value_gridblock extends rex_yform_value_abstract
{
    private $grid;
    private $sliceId;
    public $values;
    
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

        // Existierende Werte aus dem versteckten Feld laden
        $value = $this->getValue();
        if ($value) {
            $values = json_decode($value, true);
            if (is_array($values)) {
                $this->grid->values = $values;
            }
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
        // Werte aus dem POST Array sammeln
        $values = [];
        $post = rex_post('REX_INPUT_VALUE', 'array', []);

        if (!empty($post)) {
            // Template und Optionen
            foreach ([17, 18, 19, 20] as $key) {
                if (isset($post[$key])) {
                    $values[$key] = $post[$key];
                }
            }

            // Spalteninhalte 1-16  
            for ($i = 1; $i <= 16; $i++) {
                if (isset($post[$i])) {
                    $values[$i] = $post[$i];
                }
            }
        }

        // Als JSON im versteckten Feld speichern
        $this->setValue(!empty($values) ? json_encode($values) : '');
    }
    
    public function getGridblockOutput() 
    {
        if($this->getValue()) {
            $this->grid->values = json_decode($this->getValue(), true);
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
