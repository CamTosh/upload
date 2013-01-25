<? include_once 'Upload.php'; ?>


<form method="post" action="" enctype="multipart/form-data">
    File: <input type="file" name="file">
    <input type="submit" name="submit" value="Send">
</form>

<?
if (isset($_POST['submit'])) {
    $up = new Upload($_FILES['file']);
    if (!$up->isError()) {
        $up->load();
    } else {
        print $up->getErrors(true);
    }
    pre($up);
}
?>
