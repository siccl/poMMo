<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" /> 
        <title><?php echo $this->title; ?></title>

        <script src="<?php echo $this->url['theme']['shared'] ?>js/jq/jquery.js"
        type="text/javascript"></script>
        <script src="<?php echo $this->url['theme']['shared'] ?>js/pommo.js"
        type="text/javascript"></script>
        <script type="text/javascript">
            poMMo.confirmMsg = '<?php echo _('Are you sure?'); ?>';
        </script>
        <?php
        //These javascripts are only included if we set them = true in the calling form
        if ($isUiForm)
        {
            include 'ui.form.php';
        }
        if ($isUiDialog)
        {
            include 'ui.dialog.php';
        }
        if ($isUiTabs)
        {
            include 'ui.tabs.php';
        }
        if ($isUiSlider)
        {
            include 'ui.slider.php';
        }
        if ($isUiGrid)
        {
            include 'ui.grid.php';
        }
        ?>

        <link type="text/css" rel="stylesheet" 
              href="<?php echo $this->url['theme']['shared'] ?>css/default.admin.css" />

        <head>	
        </head>

        <body>

            <div id="header">

                <h1><a href="<?php echo($this->config['site_url']) ?>">
                        <img src="<?php echo($this->url['theme']['shared']); ?>images/pommo.gif" 
                             alt="pommo logo" /> <strong><?php echo($this->config['site_name']) ?></strong>
                    </a></h1>
            </div>

            <ul id="menu">
                <li><a href="<?php echo($this->url_base) ?>index.php?logout=TRUE">
                        <?php echo _('Logout') ?></a></li>
                <li class="advanced"><a href="<?php echo($this->url_base) ?>support.php">
                        <?php echo _('Support') ?></a></li>
                <li><a href="<?php echo($this->url_base) ?>admin.php">
            <?php echo _('Admin Page') ?></a></li>
            </ul>

            <?php
            if ($this->sidebar !== false)
            {
                include($this->template_dir.'/inc/admin.sidebar.php');
                echo('<div id="content">');
            } else
            {
                echo('<div id="content" class="wide">');
            }
            ?>

