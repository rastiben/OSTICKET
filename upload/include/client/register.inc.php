<?php
$info = $_POST;
if (!isset($info['timezone']))
    $info += array(
        'backend' => null,
    );
if (isset($user) && $user instanceof ClientCreateRequest) {
    $bk = $user->getBackend();
    $info = array_merge($info, array(
        'backend' => $bk::$id,
        'username' => $user->getUsername(),
    ));
}
$info = Format::htmlchars(($errors && $_POST)?$_POST:$info);

?>
<form action="account.php" id="createAccount" method="post">
  <?php csrf_token(); ?>
  <input type="hidden" name="do" value="<?php echo Format::htmlchars($_REQUEST['do']
    ?: ($info['backend'] ? 'import' :'create')); ?>" />
<table width="800" class="padded">
<tbody>
<?php
    $cf = $user_form ?: UserForm::getInstance();
    $cf->render(false, false, array('mode' => 'create'));
?>
<tr>
    <td colspan="2">
        <div><hr><h3><?php echo __('Preferences'); ?></h3>
        </div>
    </td>
</tr>
   <td colspan="2" style="padding-top:10px;">
       <label for="18e2494227ed32b3"><span class="required">
                <?php echo __('Time Zone');?>:<br>
                <?php
            $TZ_NAME = 'timezone';
            $TZ_TIMEZONE = $info['timezone'];
            include INCLUDE_DIR.'staff/templates/timezone.tmpl.php'; ?>
            <div class="error"><?php echo $errors['timezone']; ?></div>
        </label>
    </td>
<tr>
    <td colspan="2">
        <div><hr><h3><?php echo __('Access Credentials'); ?></h3></div>
    </td>
</tr>
<?php if ($info['backend']) { ?>
<tr>
    <td width="180">
        <?php echo __('Login With'); ?>:
    </td>
    <td>
        <input type="hidden" name="backend" value="<?php echo $info['backend']; ?>"/>
        <input type="hidden" name="username" value="<?php echo $info['username']; ?>"/>
<?php foreach (UserAuthenticationBackend::allRegistered() as $bk) {
    if ($bk::$id == $info['backend']) {
        echo $bk->getName();
        break;
    }
} ?>
    </td>
</tr>
<?php } else { ?>
<tr>
         <td colspan="2" style="padding-top:10px;">
       <label for="18e2494227ed32b3"><span class="required">
           <?php echo __('Create a Password'); ?>:<br>
                       <input type="hidden" name="backend" value="<?php echo $info['backend']; ?>"/>
        <input type="password" size="18" name="passwd1" value="<?php echo $info['passwd1']; ?>">
        &nbsp;<span class="error">&nbsp;<?php echo $errors['passwd1']; ?></span>
        </label>
    </td>
</tr>
<tr>
            <td colspan="2" style="padding-top:10px;">
       <label for="18e2494227ed32b3"><span class="required">
           <?php echo __('Confirm New Password'); ?>:<br>
        <input type="password" size="18" name="passwd2" value="<?php echo $info['passwd2']; ?>">
        &nbsp;<span class="error">&nbsp;<?php echo $errors['passwd2']; ?></span>
        </label>
    </td>
</tr>
<?php } ?>
</tbody>
</table>
<hr>
<p style="text-align: center;margin:0;margin-bottom:30px;padding-bottom:20px;">
    <input type="submit" value="Register"/>
    <input type="button" value="Cancel" onclick="javascript:
        window.location.href='index.php';"/>
</p>
</form>

<?php if (!isset($info['timezone'])) { ?>
<!-- Auto detect client's timezone where possible -->
<script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/jstz.min.js?901e5ea"></script>
<script type="text/javascript">
$(function() {
    var zone = jstz.determine();
    $('#timezone-dropdown').val(zone.name()).trigger('change');
});
</script>
<?php }
