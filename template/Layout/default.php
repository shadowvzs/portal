<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title> Blog Homepage </title>
		<script src="<?= script('common') ?>"></script>
        <link rel="stylesheet" href="<?= css($layout.'_layout') ?>" type="text/css"/>     
        <link rel="stylesheet" href="<?= css('common') ?>" type="text/css"/>  
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css" integrity="sha384-hWVjflwFxL6sNzntih27bfxkr27PmbbK/iSvJ+a4+0owXq79v+lsFkW54bOGbiDQ" crossorigin="anonymous">
	</head>
    <body>
		<div class="default">
			<?php if (file_exists($HEADER)) { ?>
				<header> <?php  include ($HEADER); ?> </header>
				<div class="bottom-shadow"></div>
			<?php } ?>
			
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
			
			<?php if (file_exists($FOOTER)) { ?>
				<footer> <?php  include ($FOOTER); ?> </footer>
			<?php } ?>
		</div>
    </body>
</html>