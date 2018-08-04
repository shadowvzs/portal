<style>
</style>
<div class="nav-bar">
	<?php if ($Auth) { ?>
		<div class="welcome"> Welcome, <span><?= $Auth['name'] ?></span> </div>
	<?php } ?>
	<div class="burger">
		<label for="burger_menu_check">
			<span class="line"></span>
			<span class="line"></span>
			<span class="line"></span>
		</label>
		<input type="checkbox" id="burger_menu_check">
		<nav>
			<?php if ($Auth) { ?>
				<a href="?controller=user&action=logout" title="Logout">Logout</a>			
			<?php } else { ?>
				<a href="?controller=user&action=login" title="Login">Login</a>
				<a href="?controller=user&action=signup" title="Sign Up">Sign Up</a>
			<?php } ?>
			<a href="#" title="Menu 3">Menu 3</a>
			<a href="#" title="Menu 4">Menu 4</a>
			<a href="#" title="Menu 5">Menu 5</a>
			<a href="#" title="Menu 6">Menu 6</a>
			<a href="#" title="Menu 7">Menu 7</a>
			<a href="#" title="Menu 8">Menu 8</a>
		</nav>
	</div>
</div>
<h2>Banner</h2>