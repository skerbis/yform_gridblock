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
?>

<div class="<?php echo implode(' ', $class_group) ?>" id="<?php echo $this->getHTMLId() ?>">
    <label class="control-label" for="<?php echo $this->getFieldId() ?>">
        <?php echo $this->getLabel() ?>
    </label>
    
    <?php 
    // Verstecktes Feld für die JSON-Daten
    echo '<input type="hidden" name="' . $this->getFieldName() . '" id="' . $this->getFieldId() . '" value="' . htmlspecialchars($this->getValue()) . '" />';
    ?>

    <div class="yform-gridblock-wrapper">
        <?php echo $value; ?>
    </div>
    
    <?php if (!empty($notice)) : ?>
        <p class="help-block small"><?php echo implode('<br />', $notice) ?></p>
    <?php endif; ?>
</div>

<script>
// Form Submit abfangen und Daten übertragen
$(document).ready(function() {
    var form = $('#<?php echo $this->getFieldId() ?>').closest('form');
    var hiddenInput = $('#<?php echo $this->getFieldId() ?>');
    
    form.on('submit', function() {
        var gridData = {};
        
        // Template und Optionen
        $('input[name^="REX_INPUT_VALUE"]').each(function() {
            var name = $(this).attr('name').match(/\[(\d+)\]/)[1];
            gridData[name] = $(this).val();
        });
        
        // Als JSON im hidden field speichern
        hiddenInput.val(JSON.stringify(gridData));
    });
});
</script>
