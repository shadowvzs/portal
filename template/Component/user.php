<div class="userComponent">
	<div class="userTable">
		<table>
		</table>
	</div>
	<br><br>
	<div class="userEdit" data-id="0">
		<h2>Name</h2>
		<select>
			<option value="0">Inactive</option>
			<option value="1">Active</option>
			<option value="2">Ban</option>
			<option value="3">Delete</option>
			<option value="4">Real delete</option>
		</select>
		<button id="toggle_rank">Toggle Rank</button>
		<br><br>
		<button id="close_panel" onclick="UserComponent.toggle();">Close Panel</button>
	</div>

	<p class="tooltip"> move moue over the name for information or click for edit the user! </p>
</div>