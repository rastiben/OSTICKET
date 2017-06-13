<?php
global $thisstaff, $cfg;
$timeFormat = null;
if ($thisstaff && !strcasecmp($thisstaff->datetime_format, 'relative')) {
    $timeFormat = function($datetime) {
        return Format::relativeTime(Misc::db2gmtime($datetime));
    };
}

$entryTypes = array('M'=>'message', 'R'=>'response', 'N'=>'note');
$user = $entry->getUser() ?: $entry->getStaff();
//$name = $user ? $user->getFirstName() : $entry->poster;
//var_dump($user);
//var_dump()
if(empty($user)){
  $name = $entry->poster;
} else {
  if(array_key_exists('staff_id',$user->ht)){
    $name = $user->getName();
  } else {
    $name = ucfirst($user->getFirstName()) . ' ' . ucfirst($user->getName());
  }
}

$avatar = '';
if ($user && $cfg->isAvatarsEnabled())
    $avatar = $user->getAvatar();

?>

<?php
$toWrite = "";
if($entry->getType() == 'M')
    $toWrite = 'message';
else if($entry->getType() == 'N')
    $toWrite = 'note';
else
    $toWrite = 'answer';
?>

<?php

    $avatar = NULL;
    if(!$entry->getUser()){
        $agent = Staff::objects();
        $agent->filter(array('firstname'=>$thisstaff->getFirstName(),
                         'lastname'=>$thisstaff->getLastName()));
        $agent->values('avatar');
        $avatar = $agent[0]['avatar'];
    }

?>

<div class="thread-entry <?php echo $toWrite ?>">

<!--<?php if ($avatar) { ?>
    <span class="<?php echo 'pull-left'; ?> avatar">
<?php echo $avatar; ?>
    </span>
<?php } ?>-->
    <div class="header">
      <?php if($toWrite == 'message'){ ?>
        <span class="avatar">
          <img class="avatar" alt="Avatar" src="../assets/default/images/avatar.png"><?php echo '<span>' .  strtoupper(substr(Format::htmlchars($name),0,1)) . '</span>'?></img>
        </span>
      <?php } else { ?>
        <img src="../assets/avatar/<?php echo $avatar ?>"/>
      <?php } ?>
        <div class="pull-right" style="display:none">
<?php   if ($entry->hasActions()) {
            $actions = $entry->getActions(); ?>
        <span class="muted-button pull-right" data-dropdown="#entry-action-more-<?php echo $entry->getId(); ?>">
            <i class="icon-caret-down"></i>
        </span>
        <div id="entry-action-more-<?php echo $entry->getId(); ?>" class="action-dropdown anchor-right">
            <ul class="title">
<?php       foreach ($actions as $group => $list) {
                foreach ($list as $id => $action) { ?>
                <li>
                    <a class="no-pjax" href="#" onclick="javascript:
                    <?php echo str_replace('"', '\\"', $action->getJsStub()); ?>; return false;">
                    <i class="<?php echo $action->getIcon(); ?>"></i> <?php
                    echo $action->getName();
                ?></a></li>
<?php           }
            } ?>
            </ul>
        </div>
<?php   } ?>
        <span class="textra light">
<?php   if ($entry->flags & ThreadEntry::FLAG_EDITED) { ?>
            <span class="label label-bare" title="<?php
            echo sprintf(__('Edited on %s by %s'), Format::datetime($entry->updated),
                ($editor = $entry->getEditor()) ? $editor->getName() : '');
                ?>"><?php echo __('Edited'); ?></span>
<?php   }
        if ($entry->flags & ThreadEntry::FLAG_RESENT) { ?>
            <span class="label label-bare"><?php echo __('Resent'); ?></span>
<?php   }
        if ($entry->flags & ThreadEntry::FLAG_COLLABORATOR) { ?>
            <span class="label label-bare"><?php echo __('Collaborator'); ?></span>
<?php   } ?>
        </span>
        </div>
<?php
        echo sprintf(__('<b class="name">%s</b>'), $name);
        echo sprintf('<div class="hour"><a name="entry-%d" href="#entry-%1$s"><time %s
            datetime="%s" data-toggle="tooltip" title="%s">%s</time></a></div>',
            $entry->id,
            $timeFormat ? 'class="relative"' : '',
            date(DateTime::W3C, Misc::db2gmtime($entry->created)),
            Format::daydatetime($entry->created),
            $timeFormat ? $timeFormat($entry->created) : Format::datetime($entry->created)
        );
        if($toWrite == 'note'){
        ?>

           <img src="<?php echo ROOT_PATH ?>assets/default/images/private.png" alt="private"/>

        <?php } ?>
    </div>
    <div class="thread-body no-pjax">
        <div><?php echo $entry->getBody()->toHtml(); ?></div>
        <div class="clear"></div>

<?php
  $historique = HistoriqueModel::objects()->filter(array('thread_entry_id'=>$entry->id));

  if($historique->count() > 0) {

    $stock = StockModel::objects()->filter(array('id'=>$historique[0]->stock_id));
  ?>
    <hr />
    <div>
      <?= $stock[0]->designation . " - " .  $stock[0]->numserie; ?>
    </div>

<?php } ?>
<?php
    // The strangeness here is because .has_attachments is an annotation from
    // Thread::getEntries(); however, this template may be used in other
    // places such as from thread entry editing
    $atts = isset($thread_attachments) ? $thread_attachments[$entry->id] : $entry->attachments;
    if (isset($atts) && $atts) {
?>
    <div class="attachments"><?php
        foreach ($atts as $A) {
            if ($A->inline)
                continue;
            $size = '';
            if ($A->file->size)
                $size = sprintf('<small class="filesize faded">%s</small>', Format::file_size($A->file->size));
?>
        <span class="attachment-info">
        <i class="icon-paperclip icon-flip-horizontal"></i>
        <a class="no-pjax truncate filename" href="<?php echo $A->file->getDownloadUrl();
            ?>" download="<?php echo Format::htmlchars($A->getFilename()); ?>"
            target="_blank"><?php echo Format::htmlchars($A->getFilename());
        ?></a><?php echo $size;?>
        </span>
<?php   }
    echo '</div>';
    }
?>
    </div>
<?php
    if (!isset($thread_attachments) && ($urls = $entry->getAttachmentUrls())) { ?>
        <script type="text/javascript">
            $('#thread-entry-<?php echo $entry->getId(); ?>')
                .data('urls', <?php
                    echo JsonDataEncoder::encode($urls); ?>)
                .data('id', <?php echo $entry->getId(); ?>);
        </script>
<?php
    } ?>
<?php if($toWrite == 'answer'){ ?>
<!--<div class="<?php echo 'pull-left'; ?> avatar"
        style="position: absolute;
            width: 50px;
            height: 50px;
            right: -76px;
            top: -6px;
            background: url(../assets/avatar/<?php echo $avatar ?>) no-repeat center;
            background-size: 120%;"
    ></div>-->
<!--<span class="pull-right avatar">
    <img class="avatar" alt="Avatar" src="../assets/avatar/<?php echo $avatar[0]['avatar'] ?>"/>
</span>-->
<?php } ?>
</div>
