<?php
include '../inc/messages.php';

if ($this->callbackFunction)
    {
    echo ('<script type="text/javascript">');
    echo ('     if ($.isFunction(poMMo.callback.'.$this->callbackFunction.'));');
    echo ('     poMMo.callback.'.$this->callbackFunction.'('.$this->callbackParams.');');
    echo ('</script>');
    }            
?>