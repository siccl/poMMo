<br class="clear" />
</div> <!-- end content -->
<div id="footer">
    <p>
        - <?php echo _('Page fueled by poMMo mailing management software'); ?> -
    </p>
</div> <!-- end footer -->

<?php
//Called by javascripts.
if ($dialogImageCache)
{
?>
    <div class="imgCache">
        <img src="{$url.theme.shared}images/loader.gif" />
        <img src="{$url.theme.shared}images/dialog/close.gif" />
        <img src="{$url.theme.shared}images/dialog/close_hover.gif" />
        <img src="{$url.theme.shared}images/dialog/sprite.gif" />
        <img src="{$url.theme.shared}images/dialog/bl.gif" />
        <img src="{$url.theme.shared}images/dialog/br.gif" />
        <img src="{$url.theme.shared}images/dialog/bc.gif" />
    </div>
<?php 
}; 
?>
</body>
</html>
