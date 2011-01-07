<form action="#" method="post">
	<div class="output alert"></div>

	<fieldset>
		<legend>{t}Add User{/t}</legend>

		<div>
			<label for="user">
				<strong class="required">{t}User:{/t}</strong>
			</label>
			<input type="text" size="32" maxlength="60"
					name="user" />
		</div>
		<div>
			<label for="password">
				<strong class="required">{t}Password:{/t}</strong>
			</label>
			<input type="text" size="32" maxlength="60" name="password" />
		</div>
		<div>
			<label for="password2">
				<strong class="required">{t}Password:{/t}</strong>
			</label>
			<input type="text" size="32" maxlength="60" name="password2" />
		</div>
	</fieldset>

	<div class="buttons">
		<input type="submit" value="{t}Add User{/t}" />
		<input type="reset" value="{t}Reset{/t}" />
	</div>

	<p>{t escape=no 1="<span class=\"required\">" 2="</span>"}Fields marked like
			%1 this %2 are required.{/t}</p>

</form>

{literal}
<script type="text/javascript">
$(function()
{
	poMMo.callback.addSubscriber = function(json)
	{
		// refresh the page if no grid exists, else add new subscriber to grid
		if($('#grid').size() == 0)
		{
			history.go(0);
		}
        else
        {
        	poMMo.grid.addRow(json.key,json);
        }
	};
	
	$('input[@name="force"]').click(function()
	{
		if(this.checked)
		{
			$(this).jqvDisable();
		}
		else
		{
			$(this).jqvEnable();
		}
	});

});
</script>
{/literal}
