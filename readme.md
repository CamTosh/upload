```html
<form method="post" action="" enctype="multipart/form-data">
	File: <input type="file" name="file">
	<input type="submit" name="submit" value="Send">
</form>
```

```php
if (isset($_POST['submit'])) {
	// pre($_FILES);
	$fu = new Upload($_FILES['file']);
	// pre($fu);
	if (!$fu->isError()) {
		$fu->load();
	} else {
		print $fu->getErrors(true);
	}
	pre($fu);
}
```
