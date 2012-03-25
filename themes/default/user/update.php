<?php
ob_start();

if ($this->datePicker)
{
	include $this->config['app']['path'].'themes/shared/datepicker/datepicker.php';
}
else
{
?>
	<script src="<?php echo $this->url['theme']['shared']; ?>js/jq/jquery.js"
			type="text/javascript"></script>
<?php
}

$this->capturedHead = ob_get_clean();

include $this->template_dir.'/inc/user.header.php';

?>

<h3><?php echo _('Subscriber Update'); ?></h3>

<?php

include $this->template_dir.'/inc/messages.php';

if (!$this->unsubscribe)
{
	include $this->template_dir.'/subscribe/form.update.php';
?>

<form method="post" action="">
	<input type="hidden" name="email" value="<?php echo $this->email; ?>" />
	<input type="hidden" name="code" value="<?php echo $this->code; ?>" />

	<h3><?php echo _('Unsubscribe'); ?></h3>

	<label><?php echo _('Comments'); ?>:</label>
	<textarea name="comments" rows="3" cols="33" maxlength="255"><?php
	if (isset($this->d['key']))
	{
		echo $this->d['key'];
	}
	elseif ($this->field['normally'])
	{
		echo $this->field['normally'];
	}
	?></textarea>

	<div class="buttons">
		<button type="submit" name="unsubscribe" value="true" class="warn">
			<img src="<?php echo $this->url['theme']['shared'];
					?>images/icons/nok.png" alt="not ok icon" />
			<?php
				echo _('Click to unsubscribe');
				echo $this->email;
			?>
		</button>
	</div>

</form>
<?php
}

?>

<script type="text/javascript">
$().ready(function() {
	$('.warn').click(function() {
		var str = this.innerHTML;
		return confirm("<?php echo _('Really unsubscribe?'); ?>");
	});
});
</script>

<?php

include $this->template_dir.'/inc/user.footer.php';

