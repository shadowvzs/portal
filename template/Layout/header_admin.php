<div class="nav-bar">
	<div class="welcome"> Onboard, <span><?= $Auth['name'] ?></span></div>
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
			<?php if ($Auth && $Auth['rank'] > 1) { ?>			
				<a href="?controller=user" title="Dashboard">Dashboard</a>
				<a href="?controller=user&action=welcome" title="User interface">User Interface</a>
			<?php } ?>
			<a href="#" title="Menu 5">Menu 5</a>
			<a href="#" title="Menu 6">Menu 6</a>
			<a href="#" title="Menu 7">Menu 7</a>
			<a href="#" title="Menu 8">Menu 8</a>
		</nav>
	</div>
</div>
<h2>Banner</h2>