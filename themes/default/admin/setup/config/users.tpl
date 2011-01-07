{* Field Validation - see docs/template.txt documentation *}
{fv form='users'}
{fv prepend='<span class="error">' append='</span>'}
{fv validate="admin_username"}
{fv validate="admin_password2"}
{fv validate="admin_email"}

<form action="{$smarty.server.PHP_SELF}" method="post" class="json">

<div class="output alert">{if $output}{$output}{/if}</div>

<script type="text/javascript">
$().ready(function()
{ldelim}	

	var p =
	{ldelim}	
		colNames:
		[
			'{t escape=js}User{/t}'
		],
		{literal}
		colModel:
		[
			{name: 'username', width: 350}
		],
		url: 'ajax/users.list.php',
		rowList: [10,25,50]
	};

	poMMo.grid = PommoGrid.init('#grid',p);
});
</script>

<script type="text/javascript">
$(function()
{
	// Setup Modal Dialogs
	PommoDialog.init();

	$('a.modal').click(function()
	{
		var rows = poMMo.grid.getRowIDs();
		if(rows)
		{
			// check for confirmation
			if($(this).hasClass('confirm') && !poMMo.confirm())
			{
				return false;
			}
				
			// serialize the data
			var data = $.param({'users[]': rows});
			
			// rewrite the HREF of the clicked element
			var oldHREF = this.href;
			this.href += (this.href.match(/\?/) ? "&" : "?") + data
			
			// trigger the modal dialog, or visit the URL
			if($(this).hasClass('visit'))
			{
				window.location = this.href;
			}
			else
			{
				$('#dialog').jqmShow(this);
			}
			
			// restore the original HREF
			this.href = oldHREF;
			
			poMMo.grid.reset();
		}
		return false;
	});
});

poMMo.callback.deleteUser = function(p)
{
	poMMo.grid.delRow(p.users);
	$('#dialog').jqmHide();
}

</script>
{/literal}

<table id="grid" class="scroll" cellpadding="0" cellspacing="0"></table>
<div id="gridPager" class="scroll" style="text-align:center;"></div>

<ul class="inpage_menu">
	<li>
		<a href="#">
			{t}New{/t}
		</a>
	</li>
	<li>
		<a href="#">
			{t}Update{/t}
		</a>
	</li>
	<li>
		<a href="ajax/users.rpc.php?call=delete" class="modal confirm">
			<img src="{$url.theme.shared}images/icons/delete.png"/>
			{t}Delete{/t}
		</a>
	</li>
</ul>

<div>
<label for="admin_username"><strong class="required">{t}Administrator Username:{/t}</strong>{fv message="admin_username"}</label>
<input type="text" name="admin_username" value="{$admin_username|escape}" />
<span class="notes">{t}(you will use this to login){/t}</span>
</div>

<div>
<label for="admin_password">{t}Administrator Password:{/t}</label>
<input type="password" name="admin_password" value="{$admin_password|escape}" />
<span class="notes">{t}(you will use this to login){/t}</span>
</div>

<div>
<label for="admin_password2">{t}Verify Password:{/t}{fv message="admin_password"}</label>
<input type="password" name="admin_password2" value="{$admin_password2|escape}" />
<span class="notes">{t}(enter password again){/t}</span>
</div>

<div>
<label for="admin_email"><strong class="required">{t}Administrator Email:{/t}</strong>{fv message="admin_email"}</label>
<input type="text" name="admin_email" value="{$admin_email|escape}" />
<span class="notes">{t}(email address of administrator){/t}</span>
</div>

<input type="submit" value="{t}Update{/t}" />
<img src="{$url.theme.shared}images/loader.gif" alt="loading..." class="hidden" name="loading" />
</form>

