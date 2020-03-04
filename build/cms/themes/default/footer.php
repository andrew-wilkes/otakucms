		</div>
	</div>

	<div class="footer">
		<div class="menu">
<?php
	new Menu('footer');
	Menu::html('all', ' | ');
?>
		</div>
		<div class="copy">&#xa9; <?php echo date('Y'); ?></div>
	</div>
<?php
	HTML::javascripts();
?>

</body>
</html>