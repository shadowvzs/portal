<?php if (count(ADMIN_COMPONENTS) > 0 && $Auth['rank'] > 1) { ?>
	<?php foreach ( ADMIN_COMPONENTS as $component) { ?>
		<?php if (file_exists(COMPONENT_PATH.$component.PHP)) { ?>
			<?php  include (COMPONENT_PATH.$component.PHP); ?>
		<?php } ?>
	<?php } ?>
<?php } ?>