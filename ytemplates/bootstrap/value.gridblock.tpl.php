<?php
// ytemplates/bootstrap/value.gridblock.tpl.php

$notice = [];
if ($this->getElement('notice') != "") {
    $notice[] = rex_i18n::translate($this->getElement('notice'), false);
}
if (isset($this->params['warning_messages'][$this->getId()]) && !empty($this->params['warning_messages'][$this->getId()])) {
    $notice[] = '<span class="text-warning">' . rex_i18n::translate($this->params['warning_messages'][$this->getId()], false) . '</span>';
}

$class = $this->getElement('required') ? 'form-is-required ' : '';
$class_group = [];
$class_group['form-group'] = 'form-group';
if (!empty($this->getWarningClass())) {
    $class_group[] = $this->getWarningClass();
}

$formId = $this->getId();
?>

<div class="<?php echo implode(' ', $class_group) ?>" id="<?php echo $this->getHTMLId() ?>">
    <label class="control-label" for="<?php echo $this->getFieldId() ?>">
        <?php echo $this->getLabel() ?>
    </label>
    
    <?php 
    // Verstecktes Feld für die JSON-Daten
    echo '<input type="hidden" name="' . $this->getFieldName() . '" id="' . $this->getFieldId() . '" value="' . htmlspecialchars($this->getValue()) . '" />';
    ?>

    <div class="yform-gridblock-wrapper" id="yform-gridblock-<?php echo $formId; ?>">
        <?php echo $value; ?>
    </div>
    
    <?php if (!empty($notice)) : ?>
        <p class="help-block small"><?php echo implode('<br />', $notice) ?></p>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    var $form = $('#<?php echo $this->getFieldId(); ?>').closest('form');
    var $hiddenInput = $('#<?php echo $this->getFieldId(); ?>');
    var $wrapper = $('#yform-gridblock-<?php echo $formId; ?>');

    $form.on('submit', function(e) {
        // Alle REX_INPUT_VALUE Felder sammeln
        var gridData = {};
        
        // Template und Optionen (17-20)
        $wrapper.find('input[name^="REX_INPUT_VALUE"]').each(function() {
            var match = $(this).attr('name').match(/REX_INPUT_VALUE\[(\d+)\]/);
            if (match) {
                gridData[match[1]] = $(this).val();
            }
        });

        // Value Felder in den Spalten (1-16)
        for (var i = 1; i <= 16; i++) {
            var values = {};
            $wrapper.find('input[name^="REX_INPUT_VALUE['+i+']"]').each(function() {
                var name = $(this).attr('name');
                values[name] = $(this).val();
            });
            if (!$.isEmptyObject(values)) {
                gridData[i] = values;
            }
        }

        // Als JSON speichern
        $hiddenInput.val(JSON.stringify(gridData));
    });
});
</script>
