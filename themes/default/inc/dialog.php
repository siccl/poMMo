<?php
/*
Valid Variables
-------
id: (str) - DOM ID; "dialog" by default
wide: (bool) - Wide Dialog; false
tall: (bool) - Tall Dialog; false
short: (bool) - Short Dialog; false
content: (str) - Initial Contents of dialog; null
title: (str) window title ; "poMMo",
wait: (bool) - wait dialog [no close button] ; false
*/
?>

<div id="<?php if ($id==null) {echo('Dialog');} else {echo($id);}; ?>"
     class="jqmDialog <?php if ($wide) {echo('jqmdWide');};?>">
      	<div class="jqmdTL">
		<div class="jqmdTR">
			<div class="jqmdTC">
				<?php if ($title==null) {echo('poMMo');} else {echo($title);};?>
			</div>
		</div>
	</div>
	<div class="jqmdBL">
		<div class="jqmdBR">
			<div class="jqmdBC <?php 
                                                if ($tall) {echo('jqmdTall');}
                                                if ($short) {echo('jqmdShort');};?>">
				<div class="jqmdMSG<?php if ($dialogMsgClass) {echo('dialogMsgClass');};?>">
					<?php
                                        if ($content!=null) 
                                        {
                                            echo($content);
                                        } else
                                        {
                                            echo('<img src="'.$this->url['theme']['shared'].'images/loader.gif"'.
							' alt="Loading Icon" title="Please Wait" border="0" />');
                                            echo _('Please Wait...');
					}
                                        ?>
				</div>
			</div>
		</div>
	</div>
        <?php
        if (!$wait) 
        {
            echo('<input type="image" src="'.$this->url ['theme']['shared'].
                    'images/dialog/close.gif" class="jqmdX jqmClose" />');
        }
        ?>
</div>
