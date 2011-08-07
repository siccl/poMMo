<script type="text/javascript" src="<?php echo($this->url['theme']['shared']); ?>js/jq/jqModal.js"></script>
<link type="text/css" rel="stylesheet" href="<?php echo($this->url['theme']['shared']); ?>css/ui.dialog.css" />
<script type="text/javascript">

PommoDialog = {
	init: function(dialogs,params,overloadParams) {
		dialogs = dialogs || 'div.jqmDialog[id!=wait]';
		params = params || {};
		if(!overloadParams)
			params = $.extend(this.params,params);
		
		$(dialogs).jqm(this.params);
	},
	params: {
		modal: false,
		ajax: '@href',
		target: '.jqmdMSG',
		trigger: false,
		onLoad: function(hash){
			// Automatically prepare forms in ajax loaded content
			if(poMMo.form && $.isFunction(poMMo.form.assign))
				poMMo.form.assign(hash.w);
		}
	}
};

$().ready(function() {
	// Close Button Highlighting. IE doesn't support :hover. Surprise?
	$('input.jqmdX')
	.hover(
		function(){ $(this).addClass('jqmdXFocus'); }, 
		function(){ $(this).removeClass('jqmdXFocus'); })
	.focus( 
		function(){ this.hideFocus=true; $(this).addClass('jqmdXFocus'); })
	.blur( 
		function(){ $(this).removeClass('jqmdXFocus'); });
		
	// Work around for IE's lack of :focus CSS selector
	if($.browser.msie)
		$('div.jqmDialog :input:visible')
			.focus(function(){$(this).addClass('iefocus');})
			.blur(function(){$(this).removeClass('iefocus');});

	// Initialize default wait dialog
	$('#wait').jqm({modal: true});

});
</script>

<?php
//Switch on caching of images in the footer
$dialogImageCache = true;

//Call the dialog
$wait=true;
$id="wait";
include $this->template_dir.'/inc/dialog.php';
?>