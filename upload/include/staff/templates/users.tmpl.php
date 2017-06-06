<?php
$qs = array();
$select = 'SELECT user.*, email.address as email, cdata.firsname as firsname ';

$from = 'FROM '.USER_TABLE.' user '
      . 'LEFT JOIN '.USER_EMAIL_TABLE.' email ON (user.id = email.user_id) '
      . 'LEFT JOIN ost_user__cdata cdata ON (user.id = cdata.user_id) ';

$where = ' WHERE user.org_name='.db_input($org->getName());

$sortOptions = array('name' => 'user.name',
                     'email' => 'email.address',
                     'create' => 'user.created',
                     'update' => 'user.updated');
$orderWays = array('DESC'=>'DESC','ASC'=>'ASC');
$sort= ($_REQUEST['sort'] && $sortOptions[strtolower($_REQUEST['sort'])]) ? strtolower($_REQUEST['sort']) : 'name';
//Sorting options...
if ($sort && $sortOptions[$sort])
    $order_column =$sortOptions[$sort];

$order_column = $order_column ?: 'user.name';

if ($_REQUEST['order'] && $orderWays[strtoupper($_REQUEST['order'])])
    $order = $orderWays[strtoupper($_REQUEST['order'])];

$order=$order ?: 'ASC';
if ($order_column && strpos($order_column,','))
    $order_column = str_replace(','," $order,",$order_column);

$x=$sort.'_sort';
$$x=' class="'.strtolower($order).'" ';
$order_by="$order_column $order ";

$total=db_count('SELECT count(DISTINCT user.id) '.$from.' '.$where);
$page=($_GET['p'] && is_numeric($_GET['p']))?$_GET['p']:1;
$pageNav=new Pagenate($total,$page,PAGE_LIMIT);
$qstr = '&amp;'. Http::build_query($qs);
$qs += array('sort' => $_REQUEST['sort'], 'order' => $_REQUEST['order']);

$pageNav->setURL('users.php', $qs);
//Ok..lets roll...create the actual query
$qstr .= '&amp;order='.($order=='DESC' ? 'ASC' : 'DESC');

$select .= ', count(DISTINCT ticket.ticket_id) as tickets ';

$from .= ' LEFT JOIN '.TICKET_TABLE.' ticket ON (ticket.user_id = user.id) ';


$query="$select $from $where GROUP BY user.id ORDER BY $order_by LIMIT ".$pageNav->getStart().",".$pageNav->getLimit();
//echo $query;

$showing = $search ? __('Search Results').': ' : '';
$res = db_query($query);
if($res && ($num=db_num_rows($res)))
    $showing .= $pageNav->showing();
else
    $showing .= __("This organization doesn't have any users yet");

?>


<div>
    <div>
        <p class="numberUsers">Utilisateurs (<?= $num ?>)</p>
    </div>
</div>

<div class="listOrg">
<form action="orgs.php?id=<?php /*echo $org->getName();*/ ?>" method="POST" name="users" >

<div class="clear"></div>

 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="mass_process" >
 <input type="hidden" id="id" name="id" value="<?php /*echo $org->getName();*/ ?>" >
 <input type="hidden" id="action" name="a" value="" >

<?php
$month = ['Jan','Fev','Mar','Avr','Mai','Jui','Jui','Aou','Sep','Oct','Nov','Dec'];
if($res && db_num_rows($res)){
    $ids=($errors && is_array($_POST['ids']))?$_POST['ids']:null;
    while ($row = db_fetch_array($res)) {
        $name = new UsersName(ucfirst($row['firsname']) . ' ' . ucfirst($row['name']));
        $date = DateTime::createFromFormat('Y-m-d H:i:s',$row['created']);
      ?>

 <div class="userDiv">
   <div class="userCreated"><?= $date->format('d'); ?><br /><?= $month[$date->format('n')] . ' ' . $date->format('Y'); ?></div>
   <h6 id="userName"><b><?= Format::htmlchars($name); ?></b></h6>
   <p id="userEmail"><?= Format::htmlchars($row['email']); ?></p>
   <p id="userStatus">Actif</p>
 </div>

 <?php
  }
}
 ?>

 <!--<table class="table table-striped" border="0" style="margin:0px;" cellspacing="1" cellpadding="0" width="100%">
    <thead>
        <tr>
            <th width="38%"><?php echo __('Name'); ?></th>
            <th width="35%"><?php echo __('Email'); ?></th>
            <th width="8%"><?php echo __('Status'); ?></th>
            <th width="15%"><?php echo __('Created'); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php
        if($res && db_num_rows($res)):
            $ids=($errors && is_array($_POST['ids']))?$_POST['ids']:null;
            while ($row = db_fetch_array($res)) {

                $name = new UsersName($row['name']);
                $status = 'Active';
                $sel=false;
                if($ids && in_array($row['id'], $ids))
                    $sel=true;
                ?>
               <tr id="<?php echo $row['id']; ?>">
                <td>&nbsp;
                    <a class="preview"
                        href="users.php?id=<?php echo $row['id']; ?>"
                        data-preview="#users/<?php
                        echo $row['id']; ?>/preview" ><?php
                        echo Format::htmlchars($name); ?></a>
                    &nbsp;
                    <?php
                    if ($row['tickets'])
                         echo sprintf('<i class="icon-fixed-width icon-file-text-alt"></i>
                             <small>(%d)</small>', $row['tickets']);
                    ?>
                </td>
                <td><?php echo Format::htmlchars($row['email']); ?></td>
                <td><?php echo $status; ?></td>
                <td><?php echo Format::date($row['created']); ?></td>
               </tr>
            <?php
            } //end of while.
        endif; ?>
    </tbody>
</table>-->
</form>

<div style="display:none;" class="dialog" id="confirm-action">
    <h3 class="drag-handle"><?php echo __('Please Confirm'); ?></h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <p class="confirm-action" style="display:none;" id="remove-users-confirm">
        <?php echo sprintf(__(
        'Are you sure you want to <b>REMOVE</b> %1$s from <strong>%2$s</strong>?'),
        _N('selected user', 'selected users', 2),
        $org->getName()); ?>
    </p>
    <div><?php echo __('Please confirm to continue.'); ?></div>
    <hr style="margin-top:1em"/>
    <p class="full-width">
        <span class="buttons pull-left">
            <input type="button" value="<?php echo __('No, Cancel'); ?>" class="close">
        </span>
        <span class="buttons pull-right">
            <input type="button" value="<?php echo __('Yes, Do it!'); ?>" class="confirm">
        </span>
     </p>
    <div class="clear"></div>
</div>

</div>

<script type="text/javascript">
/*$(function() {
    $(document).on('click', 'a.add-user', function(e) {
        e.preventDefault();
        $.userLookup('ajax.php/' + $(this).attr('href').substr(1), function (user) {
            if (user && user.id)
                window.location.href = 'orgs.php?id=<?php echo $org->getName(); ?>'
            else
              $.pjax({url: window.location.href, container: '#pjax-container'})
        });
        return false;
     });
});*/
</script>
