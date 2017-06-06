<?php
if(!defined('OSTSCPINC') || !$thisstaff) die('Access Denied');

/*
*Récupération des organisation
*/
$orgsC = OrganisationCollection::getInstance();

$page = isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
/*
*Récupération de la page des organisation
*/

//Si recherche
if(isset($_REQUEST['query']) && !empty($_REQUEST['query'])){
    $orgs = $orgsC->lookUpByName($_REQUEST['query'],$page);
} /*else if(empty($_REQUEST['query'])) {
    header('Location : .');
} */ else {
    $orgs = $orgsC->lookUp($page);
}

/*
*Récupération du nombre d'organisation
*/
$total = $orgsC->nbOrg(isset($_REQUEST['query']) ? $_REQUEST['query'] : "");

/*
*Création de la pagination
*/
$pages = new Pagination($total,50);
$pagination = $pages->paginate($page);

?>


<div id="basic_search">
    <div style="min-height:25px;">
        <form action="orgs.php" method="get">
            <div class="attached input">
            <input type="search" class="basic-search" id="basic-org-search" name="query" autofocus size="30" value="<?php echo Format::htmlchars($_REQUEST['query']); ?>" autocomplete="off" autocorrect="off" autocapitalize="off">
                <button type="submit" class="attached button"><i class="icon-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<table class="list" border="0" cellspacing="1" cellpadding="0" width="100%">
    <thead>
        <tr>
            <th nowrap width="4%">&nbsp;</th>
            <th width="45%"><a <?php echo $name_sort; ?> href="orgs.php?<?php echo $qstr; ?>&sort=name"><?php echo __('Name'); ?></a></th>
        </tr>
    </thead>
    <tbody>
    <?php
        foreach ($orgs as $org) {
            ?>
        <tr>
            <td nowrap align="center">
                <input type="checkbox" class="ckb mass nowarn"/>
            </td>
            <td>&nbsp;  <a href="orgs.php?id=<?php echo $org->getName(); ?>"><?php echo $org->getName(); ?></td>
        </tr>

        <?php
        }
        ?>
    </tbody>
    <tfoot>
     <tr>
        <td colspan="7">
            <?php if ($total) { ?>
            <?php echo __('Select');?>:&nbsp;
            <a id="selectAll" href="#ckb"><?php echo __('All');?></a>&nbsp;&nbsp;
            <a id="selectNone" href="#ckb"><?php echo __('None');?></a>&nbsp;&nbsp;
            <a id="selectToggle" href="#ckb"><?php echo __('Toggle');?></a>&nbsp;&nbsp;
            <?php }else{
                echo '<i>';
                echo __('Query returned 0 results.');
                echo '</i>';
            } ?>
        </td>
     </tr>
    </tfoot>
</table>
<div style="text-align:center">
<?php

echo $pagination;

?>
</div>


<!--<div id="basic_search">
    <div style="min-height:25px;">
        <form action="orgs.php" method="get">
            <?php csrf_token(); ?>
            <div class="attached input">
            <input type="hidden" name="a" value="search">
            <input type="search" class="basic-search" id="basic-org-search" name="query" autofocus size="30" value="<?php echo Format::htmlchars($_REQUEST['query']); ?>" autocomplete="off" autocorrect="off" autocapitalize="off">
                <button type="submit" class="attached button"><i class="icon-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>
<div style="margin-bottom:20px; padding-top:5px;">
    <div class="sticky bar opaque">
        <div class="content">
            <div class="pull-left flush-left">
                <h2><?php echo __('Organizations'); ?></h2>
            </div>
            <div class="pull-right">
                <?php if ($thisstaff->hasPerm(Organization::PERM_CREATE)) { ?>
                <a class="green button action-button add-org"
                   href="#">
                    <i class="icon-plus-sign"></i>
                    <?php echo __('Add Organization'); ?>
                </a>
                <?php }
            if ($thisstaff->hasPerm(Organization::PERM_DELETE)) { ?>
                <span class="action-button" data-dropdown="#action-dropdown-more"
                      style="/*DELME*/ vertical-align:top; margin-bottom:0">
                    <i class="icon-caret-down pull-right"></i>
                    <span ><i class="icon-cog"></i> <?php echo __('More');?></span>
                </span>
                <div id="action-dropdown-more" class="action-dropdown anchor-right">
                    <ul>
                        <li class="danger"><a class="orgs-action" href="#delete">
                            <i class="icon-trash icon-fixed-width"></i>
                            <?php echo __('Delete'); ?></a></li>
                    </ul>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<div class="clear"></div>-->


<script type="text/javascript">
$(function() {
    $('input#basic-org-search').typeahead({
        source: function (typeahead, query) {
            $.ajax({
                url: "ajaxs.php/orgs/typeahead/"+query,
                dataType: 'json',
                success: function (data) {
                    typeahead.process(data);
                }
            });
        },
        onselect: function (obj) {
            window.location.href = 'orgs.php?id='+obj.id;
        },
        property: "/bin/true"
    });

    $(document).on('click', 'a.add-org', function(e) {
        e.preventDefault();
        $.orgLookup('ajax.php/orgs/add', function (org) {
            var url = 'orgs.php?id=' + org.id;
            $.pjax({url: url, container: '#pjax-container'})
         });

        return false;
     });

    var goBaby = function(action) {
        var ids = [],
            $form = $('form#orgs-list');
        $(':checkbox.mass:checked', $form).each(function() {
            ids.push($(this).val());
        });
        if (ids.length) {
          var submit = function() {
            $form.find('#action').val(action);
            $.each(ids, function() { $form.append($('<input type="hidden" name="ids[]">').val(this)); });
            $form.find('#selected-count').val(ids.length);
            $form.submit();
          };
          $.confirm(__('You sure?')).then(submit);
        }
        else if (!ids.length) {
            $.sysAlert(__('Oops'),
                __('You need to select at least one item'));
        }
    };
    $(document).on('click', 'a.orgs-action', function(e) {
        e.preventDefault();
        goBaby($(this).attr('href').substr(1));
        return false;
    });
});
</script>
