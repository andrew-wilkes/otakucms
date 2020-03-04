<!DOCTYPE html>
<html>
<head>
<title>#TITLE#</title>
<?php
  HTML::meta_tags(['stylesheet' => ['https://fonts.googleapis.com/css?family=Source+Sans+Pro','normalize.css','styles.css','alignment.css']]);
?>
</head>

<body data-page-id="<?php echo $_SESSION['page']->id; ?>">

  <div class="header">
    <ul>
<?php
	new Menu('top');
	Menu::html('all', ' &#x02A33; ');
?>
		</ul>
  </div>

<div class="middle">
  <div class="page">
