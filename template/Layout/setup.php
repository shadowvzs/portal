<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title> Portal: Setup </title>
        <link rel="stylesheet" href="/public/css/index.css?<?= time() ?>" type="text/css"/>     
		<script src="<?= script('common') ?>"></script>
        <link rel="stylesheet" href="<?= css('setup_layout') ?>" type="text/css"/>     
        <link rel="stylesheet" href="<?= css('common') ?>" type="text/css"/>     
    </head>
	
    <body>
		<div class="setup">
			<header> <p> Welcome! This is our setup page! </p> </header>
			<div class="bottom-shadow"></div>
			<main>
				<?php if (!empty($messages)) { ?>
				<div class="messages">
					<?php foreach($messages as $msg) { ?>
						<div class="<?= $msg[1] ? 'success' : 'error' ?>"> 
							<?= $msg[0] ?>
						</div>
					<?php } ?>
				</div>
				<?php } ?>		
				<?php  require ($view_path); ?>
			</main>
		</div>
    </body>
</html>