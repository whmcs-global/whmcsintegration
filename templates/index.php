<?php if($_['appPermission']!='yes'){ ?>
       <div id="emptycontent">
            <div class="icon-activity"></div>
            <h2>Access Denied</h2>
            <p>Access has not been given for your role group to access this app.</p>
        </div> 
<?php }else{ ?>

<?php
script('whmcsintegration', 'script');
style('whmcsintegration', 'style');
?>

<div id="app">
	<div id="app-navigation">
		<?php print_unescaped($this->inc('navigation/index')); ?>
		<?php print_unescaped($this->inc('settings/index')); ?>
	</div>

	<div id="app-content">
		<div id="app-content-wrapper">
			<?php print_unescaped($this->inc('content/index')); ?>
		</div>
	</div>
</div>
<?php } ?>

