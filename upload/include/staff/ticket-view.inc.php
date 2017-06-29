<?php
//Note that ticket obj is initiated in tickets.php.
if(!defined('OSTSCPINC') || !$thisstaff || !is_object($ticket) || !$ticket->getId()) die('Invalid path');

//Make sure the staff is allowed to access the page.
if(!@$thisstaff->isStaff() || !$ticket->checkStaffPerm($thisstaff)) die('Access Denied');

//Re-use the post info on error...savekeyboards.org (Why keyboard? -> some people care about objects than users!!)
$info=($_POST && $errors)?Format::input($_POST):array();

/*require_once(SCP_DIR . 'Request/Tickets.php');*/
require_once(SCP_DIR . 'Request/Rapport.php');
require_once(SCP_DIR . 'Request/Atelier.php');
require_once(INCLUDE_DIR . 'class.contrats.php');
require_once(INCLUDE_DIR . 'class.stocks.php');
require_once(INCLUDE_DIR . 'class.rapport.php');

//Get the goodies.
$dept  = $ticket->getDept();  //Dept
$role  = $thisstaff->getRole($dept);
$staff = $ticket->getStaff(); //Assigned or closed by..
$user  = $ticket->getOwner(); //Ticket User (EndUser)
$team  = $ticket->getTeam();  //Assigned team.
$sla   = $ticket->getSLA();
$lock  = $ticket->getLock();  //Ticket lock obj
if (!$lock && $cfg->getTicketLockMode() == Lock::MODE_ON_VIEW)
    $lock = $ticket->acquireLock($thisstaff->getId());
$mylock = ($lock && $lock->getStaffId() == $thisstaff->getId()) ? $lock : null;
$id    = $ticket->getId();    //Ticket ID.

//Useful warnings and errors the user might want to know!
if ($ticket->isClosed() && !$ticket->isReopenable())
    $warn = sprintf(
            __('Current ticket status (%s) does not allow the end user to reply.'),
            $ticket->getStatus());
elseif ($ticket->isAssigned()
        && (($staff && $staff->getId()!=$thisstaff->getId())
            || ($team && !$team->hasMember($thisstaff))
        ))
    $warn.= sprintf('&nbsp;&nbsp;<span class="Icon assignedTicket">%s</span>',
            sprintf(__('Ticket is assigned to %s'),
                implode('/', $ticket->getAssignees())
                ));

if (!$errors['err']) {

    if ($lock && $lock->getStaffId()!=$thisstaff->getId())
        $errors['err'] = sprintf(__('This ticket is currently locked by %s'),
                $lock->getStaffName());
    elseif (($emailBanned=Banlist::isBanned($ticket->getEmail())))
        $errors['err'] = __('Email is in banlist! Must be removed before any reply/response');
    elseif (!Validator::is_valid_email($ticket->getEmail()))
        $errors['err'] = __('EndUser email address is not valid! Consider updating it before responding');
}

$unbannable=($emailBanned) ? BanList::includes($ticket->getEmail()) : false;

if($ticket->isOverdue())
    $warn.='&nbsp;&nbsp;<span class="Icon overdueTicket">'.__('Marked overdue!').'</span>';

?>
<link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet">
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">

<div>
    <div class="sticky bar col-md-12" data_ticket_id="<?php echo $ticket->getId(); ?>"
      data_agent_id="<?php echo $thisstaff->getId(); ?>">
       <div class="content col-md-12">
        <div class="ticketName">
             <h2><a href="tickets.php?id=<?php echo $ticket->getId(); ?>"
             title="<?php echo __('Reload'); ?>"><i class="icon-refresh"></i>
             <?php echo sprintf(__('Ticket #%s'), $ticket->getNumber()); ?></a>
            </h2>
        </div>
        <div class="outils">
            <?php
            if ($thisstaff->hasPerm(Email::PERM_BANLIST)
                    || $role->hasPerm(TicketModel::PERM_EDIT)
                    || ($dept && $dept->isManager($thisstaff))) { ?>
            <span class="action-button" data-placement="bottom" data-dropdown="#action-dropdown-more" data-toggle="tooltip" title="<?php echo __('More');?>">
                <i class="icon-caret-down pull-right"></i>
                <span ><i class="icon-cog"></i></span>
            </span>
            <?php
            }

            if ($role->hasPerm(TicketModel::PERM_EDIT)) { ?>
                <span class="action-button"><a data-placement="bottom" class="no-pjax" data-toggle="tooltip" title="<?php echo __('Edit'); ?>" href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=edit"><i class="icon-edit"></i></a></span>
            <?php
            } ?>
            <span class="action-button" data-placement="bottom" data-dropdown="#action-dropdown-print" data-toggle="tooltip" title="<?php echo __('Print'); ?>">
                <i class="icon-caret-down pull-right"></i>
                <a id="ticket-print" href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=print"><i class="icon-print"></i></a>
            </span>
            <div id="action-dropdown-print" class="action-dropdown anchor-right">
              <ul>
                 <li><a class="no-pjax" target="_blank" href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=print&notes=0"><i
                 class="icon-file-alt"></i> <?php echo __('Ticket Thread'); ?></a>
                 <li><a class="no-pjax" target="_blank" href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=print&notes=1"><i
                 class="icon-file-text-alt"></i> <?php echo __('Thread + Internal Notes'); ?></a>
              </ul>
            </div>
            <?php
            // Transfer
            if ($role->hasPerm(TicketModel::PERM_TRANSFER)) {?>
            <!--<span class="action-button">
            <a class="ticket-action" id="ticket-transfer" data-placement="bottom" data-toggle="tooltip" title="<?php echo __('Transfer'); ?>"
                data-redirect="tickets.php"
                href="#tickets/<?php echo $ticket->getId(); ?>/transfer"><i class="icon-share"></i></a>
            </span>-->
            <?php
            } ?>

            <?php
            // Assign
            if ($ticket->isOpen() && $role->hasPerm(TicketModel::PERM_ASSIGN)) {?>
            <span class="action-button"
                data-dropdown="#action-dropdown-assign"
                data-placement="bottom"
                data-toggle="tooltip"
                title=" <?php echo $ticket->isAssigned() ? __('Assign') : __('Reassign'); ?>"
                >
                <i class="icon-caret-down pull-right"></i>
                <a class="ticket-action" id="ticket-assign"
                    data-redirect="tickets.php"
                    href="#tickets/<?php echo $ticket->getId(); ?>/assign"><i class="icon-user"></i></a>
            </span>
            <div id="action-dropdown-assign" class="action-dropdown anchor-right">
              <ul>
                <?php
                // Agent can claim team assigned ticket
                if (!$ticket->getStaff()
                        && (!$dept->assignMembersOnly()
                            || $dept->isMember($thisstaff))
                        ) { ?>
                 <li><a class="no-pjax ticket-action"
                    data-redirect="tickets.php"
                    href="#tickets/<?php echo $ticket->getId(); ?>/claim"><i
                    class="icon-chevron-sign-down"></i> <?php echo __('Claim'); ?></a>
                <?php
                } ?>
                 <li><a class="no-pjax ticket-action"
                    data-redirect="tickets.php"
                    href="#tickets/<?php echo $ticket->getId(); ?>/assign/agents"><i
                    class="icon-user"></i> <?php echo __('Agent'); ?></a>
                 <li><a class="no-pjax ticket-action"
                    data-redirect="tickets.php"
                    href="#tickets/<?php echo $ticket->getId(); ?>/assign/teams"><i
                    class="icon-group"></i> <?php echo __('Team'); ?></a>
              </ul>
            </div>
            <?php
            } ?>
            <div id="action-dropdown-more" class="action-dropdown anchor-right">
              <ul>
                <?php
                 if ($role->hasPerm(TicketModel::PERM_EDIT)) { ?>
                    <li><a class="change-user" href="#tickets/<?php
                    echo $ticket->getId(); ?>/change-user"><i class="icon-user"></i> <?php
                    echo __('Change Owner'); ?></a></li>
                <?php
                 }

                 if($ticket->isOpen() && ($dept && $dept->isManager($thisstaff))) {

                    if($ticket->isAssigned()) { ?>
                        <li><a  class="confirm-action" id="ticket-release" href="#release"><i class="icon-user"></i> <?php
                            echo __('Release (unassign) Ticket'); ?></a></li>
                    <?php
                    }

                    if(!$ticket->isOverdue()) { ?>
                        <li><a class="confirm-action" id="ticket-overdue" href="#overdue"><i class="icon-bell"></i> <?php
                            echo __('Mark as Overdue'); ?></a></li>
                    <?php
                    }

                    if($ticket->isAnswered()) { ?>
                    <li><a class="confirm-action" id="ticket-unanswered" href="#unanswered"><i class="icon-circle-arrow-left"></i> <?php
                            echo __('Mark as Unanswered'); ?></a></li>
                    <?php
                    } else { ?>
                    <li><a class="confirm-action" id="ticket-answered" href="#answered"><i class="icon-circle-arrow-right"></i> <?php
                            echo __('Mark as Answered'); ?></a></li>
                    <?php
                    }
                } ?>
                <?php
                if ($role->hasPerm(Ticket::PERM_EDIT)) { ?>
                <li><a href="#ajax.php/tickets/<?php echo $ticket->getId();
                    ?>/forms/manage" onclick="javascript:
                    $.dialog($(this).attr('href').substr(1), 201);
                    return false"
                    ><i class="icon-paste"></i> <?php echo __('Manage Forms'); ?></a></li>
                <?php
                } ?>

<?php           if ($thisstaff->hasPerm(Email::PERM_BANLIST)) {
                     if(!$emailBanned) {?>
                        <li><a class="confirm-action" id="ticket-banemail"
                            href="#banemail"><i class="icon-ban-circle"></i> <?php echo sprintf(
                                Format::htmlchars(__('Ban Email <%s>')),
                                $ticket->getEmail()); ?></a></li>
                <?php
                     } elseif($unbannable) { ?>
                        <li><a  class="confirm-action" id="ticket-banemail"
                            href="#unbanemail"><i class="icon-undo"></i> <?php echo sprintf(
                                Format::htmlchars(__('Unban Email <%s>')),
                                $ticket->getEmail()); ?></a></li>
                    <?php
                     }
                  }
                  if ($role->hasPerm(TicketModel::PERM_DELETE)) {
                     ?>
                    <li class="danger"><a class="ticket-action" href="#tickets/<?php
                    echo $ticket->getId(); ?>/status/delete"
                    data-redirect="tickets.php"><i class="icon-trash"></i> <?php
                    echo __('Delete Ticket'); ?></a></li>
                <?php
                 }
                ?>
              </ul>
            </div>
                <?php
                if ($role->hasPerm(TicketModel::PERM_REPLY)) { ?>
                <a href="#post-reply" class="post-response action-button"
                data-placement="bottom" data-toggle="tooltip"
                title="<?php echo __('Post Reply'); ?>"><i class="icon-mail-reply"></i></a>
                <?php
                } ?>
                <a href="#post-note" id="post-note" class="post-response action-button"
                data-placement="bottom" data-toggle="tooltip"
                title="<?php echo __('Post Internal Note'); ?>"><i class="icon-file-text"></i></a>
                <?php // Status change options
                //echo TicketStatus::status_options();
                ?>
           </div>
    </div>
  </div>
</div>

<div style="min-height: 680px;background-color: #f8f8f8;">

<div class="ticket_left col-md-9">


<?php

  $nbRapports = RapportModel::objects()->filter(array('id_ticket'=>$ticket->getId()))->count();

?>
<ul  class="tabs clean threads" id="ticket_tabs" >
    <li class="active"><a id="ticket-thread-tab" href="#ticket_thread"><?php
        echo sprintf(__('Ticket Thread (%d)'), $tcount); ?></a></li>
    <li><a id="ticket-tasks-tab" href="#tasks"
            data-url="<?php
        echo sprintf('#tickets/%d/tasks', $ticket->getId()); ?>"><?php
        echo 'Tâches';
        if ($ticket->getNumTasks())
            echo sprintf('&nbsp;(<span id="ticket-tasks-count">%d</span>)', $ticket->getNumTasks());
        ?></a></li>
    <li><a id="ticket-rapport-tab" href="#ticket_rapport">Rapports (<?= $nbRapports; ?>)</a></li>
    <li><a id="atelier-tab" href="#atelier">Atelier</a></li>
</ul>

<div id="ticket_tabs_container">


<?php

    //$prepas = Atelier::getInstance()->get_prepa($ticket->getId());
    //print_r($prepas);
?>

<?php

    $agents = Staff::objects()
            ->annotate(array(
                'teams_count'=>SqlAggregate::COUNT('teams', true),
            ))
            ->select_related('dept', 'group');

    $array = [];
    foreach ($agents as $agent) {
        array_push($array,['id'=>$agent->getId(),'name'=>$agent->getFirstName().' '.$agent->getLastName()]);
    }

    $threads = $ticket->getThreadEntries(array('M','R','N'));

    /*
    *Récupération du prénom de l'utilisateur
    */
    $userInfo = $ticket->getOwner();
    $orgName = $userInfo->getOrgName();
    foreach($userInfo->getForms()[0]->_fields as $field){
        if($field->answer->_field->answer->_field->ht['label'] == "Prénom"){
            $prenom = $field->answer->_field->answer->ht['value'];
        }
    }

    /*
    *Récupération du premier message ( dans notre cas la problématique pour une repa ).
    */
    //$orgsC = OrganisationCollection::getInstance();
    //die();

    //TODO : Asynchronously load org data

    /*$org = $orgsC->lookUpByName($ticket->getOwner()->getOrgId())[0]
    $org_name = $userInfo->getOrgName();
    $org = $orgsC->searchByName($org_name)[0];*/

    /*if(!empty($org)){
        $address = addslashes($org->getAddress() . " " . $org->getComplement() . "&#013;&#010;" . $org->getCP() . " " . $org->getCity());
        $phone = $org->getPhone();
        $orgName = $org->getName();
        $orgId = '411VDOC';
    }*/


?>

<div id="atelier" style="display:none" ng-init="init(<?php echo $ticket->getId() ?>,'<?php echo $thisstaff->getName() ?>',<?php echo htmlspecialchars(json_encode($array)) ?>)" ng-controller="atelierCtrl">

    <div id="ifNoAtelier" class="col-md-12" ng-show="!showRepa && !showPrepa">
        <div id="ticketIsRepa" ng-click="setTicketAtelierType('repa')" class="col-md-6">
            <div class="col-md-12">
                <img src="../assets/atelier/computerRepair.png">
                <h4>Ticket de réparation</h4>
            </div>
        </div>
        <div id="ticketIsPrepa" ng-click="setTicketAtelierType('prepa')" class="col-md-6">
            <div class="col-md-12">
                <img src="../assets/atelier/computerPrepair.png">
                <h4>Ticket de préparation</h4>
            </div>
        </div>
    </div>

    <div class="modal fade" id="signatureFs" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Impression de la fiche de suivi</h4>
          </div>
          <div class="modal-body">
                <canvas id="signature-pad2"></canvas>
          </div>
          <div class="modal-footer">
            <button type="button" ng-click="removePDFView($event)" class="btn btn-default">Annuler</button>
            <button type="button" ng-click="displaySignature($event)" class="btn btn-primary">Signer</button>
          </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div>

    <div id="ifRepa" class="col-md-12" ng-show="showRepa">

        <div class="bubble bubble-repa col-md-12">
           <div class="rectangle rectangle-repa"><h2>REPA - {{etat}}</h2></div>
           <div class="triangle-l triangle-l-repa"></div>
           <div class="triangle-r triangle-r-repa"></div>
        </div>

        <div id="newFicheSuivi" class="">
            <h4>{{ficheSuiviText}}</h4>
            <div class="col-md-12" style="background: white;padding-top:15px;padding-bottom:15px;">
                <div class="col-md-12 text-left">
                    <div class="inputField readOnly col-md-6">
                        <input type="text" id="org" ng-init="org = '<?php echo $orgName; ?>'" value="<?php echo $orgName; ?>" readonly>
                        <label for="org">Organisation</label>
                    </div>
                    <div class="inputField readOnly col-md-6">
                        <input type="text" id="dateOuverture" ng-init="dateOuverture = '<?php echo $ticket->getCreateDate(); ?>'" value="<?php echo $ticket->getCreateDate(); ?>" readonly>
                        <label for="dateOuverture">Date d'ouverture</label>
                    </div>
                </div>
                <div class="col-md-12 text-left">
                    <div class="inputField readOnly col-md-12">
                        <textarea id="contact" ng-init="contact = {tel: '<?php echo $phone; ?>',address: '<?php echo $address ?>'}" readonly><?php echo stripslashes($address) ?>&#013;&#010;<?php echo $phone; ?></textarea>
                        <label for="contact">Adresse - Téléphone</label>
                    </div>
                </div>
                <div class="col-md-12 text-left">
                    <div class="inputField readOnly col-md-12">
                        <textarea id="description" ng-init="description = '<?php echo $threads[0]->getBody(); ?>'" readonly><?php echo $threads[0]->getBody(); ?></textarea>
                        <label for="description">Description du souci</label>
                    </div>
                </div>
                <div class="col-md-12 text-left">
                    <div class="inputField readOnly col-md-6">
                        <input type="text" id="names" ng-init="names = '<?php echo ucwords($prenom . ' ' . $userInfo->getName()); ?>'" value="<?php echo ucwords($prenom . ' ' . $userInfo->getName()); ?>" readonly>
                        <label for="names">Nom de la personne</label>
                    </div>
                    <div class="inputField col-md-6">
                        <input type="text" ng-model="type" id="type" required>
                        <label for="type">Type de matériel</label>
                    </div>
                </div>
                <div class="col-md-12 text-left">
                    <div class="inputField col-md-6">
                        <p for="tech">Technicien</p>
                        <select id="tech" ng-model="tech" ng-options="agent.name for agent in agents" required>
                        </select>
                    </div>
                </div>
                <div class="col-md-12 text-left">
                    <div class="inputField col-md-12">
                        <textarea id="accessoire" ng-model="accessoire" required></textarea>
                        <label for="accessoire">Accessoires</label>
                    </div>
                </div>
            </div>
        </div>
            <div id="ficheDuPoste">
                <h4>Fiche du poste</h4>
               <div class="col-md-12" style="background: white;padding-top:15px;padding-bottom:15px;">
                    <div class="col-md-12 text-left">
                        <div class="inputField col-md-6 ">
                            <input id="marque" ng-model="marque" required>
                            <label for="marque">Marque</label>
                        </div>
                        <div class="inputField col-md-6">
                            <input id="model" ng-model="model" required>
                            <label for="model">Modèle</label>
                        </div>
                    </div>
                    <div class="col-md-12 text-left">
                        <div class="inputField col-md-6">
                            <input id="sn" ng-model="sn" required>
                            <label for="sn">Numéro de série</label>
                        </div>
                        <div class="inputField col-md-6">
                            <input id="vd" ng-model="vd" required>
                            <label for="vd">Numéro de VD</label>
                        </div>
                    </div>
                    <div class="col-md-12 text-left">
                        <div class="inputField col-md-6">
                            <input id="os" ng-model="os" required>
                            <label for="os">Système d'exploitation</label>
                        </div>
                        <div class="inputField col-md-6">
                            <input id="motDePasse" ng-model="motDePasse" required>
                            <label for="motDePasse">Mot de passe</label>
                        </div>
                    </div>
                    <div class="col-md-12 text-left">
                        <div class="inputField col-md-6">
                            <input id="login" ng-model="login" required>
                            <label for="login">Login</label>
                        </div>
                        <div class="inputField col-md-6">
                            <input id="office"  ng-model="office" required>
                            <label for="office">Office</label>
                        </div>
                    </div>
                    <div class="col-md-12 text-left">
                        <div class="inputField col-md-12">
                            <textarea id="autreSoft" ng-model="autreSoft" required></textarea>
                            <label for="autreSoft">Autres Soft</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 text-right" style="padding: 0px;">
                <div class="col-md-12" style="padding: 0px;">
                    <button ng-click="addFicheSuivi('repa')" class="btn btn-success">{{buttonFicheSuivi}}</button>
                    <button ng-click="printFicheSuivi()" class="btn btn-info"><span class="glyphicon glyphicon-print"></span></a></button>
                </div>
            </div>
    </div>

    <div id="ifPrepa" ng-show="showPrepa">
        <div class="form-group form-inline">
            <input id="nbPrepa" class="form-control" placeholder="Nombre de Prepa" ng-model="nbPrepa">
            <button class="btn btn-success" ng-click="addContenu('prepa')">Créer</button>
        </div>
        <div ng-repeat="contenu in prepas" class="col-md-4 contenu" id="{{contenu.numContenue}}">
            <div class="prepa">
                <div class="bubble bubble-prepa col-md-12">
                   <div class="rectangle rectangle-prepa"><h4>{{contenu.etat}}</h4></div>
                   <div class="triangle-l triangle-l-prepa"></div>
                   <div class="triangle-r triangle-r-prepa"></div>
                </div>
                <img class="computer" src="../assets/default/images/computer.png">
                <h2>{{contenu.contenuType == "prepa"  ? "VD"+contenu.id_VD : "REPA"}}</h2>
            </div>
        </div>
        <!--<div class="bubbleRepa col-md-12">
           <div class="rectangleRepa"><h2>REPA - {{etat}}</h2></div>
           <div class="triangle-lRepa"></div>
           <div class="triangle-rRepa"></div>
        </div>-->
    </div>

</div>

<div id="ticket_rapport" style="display:none" ng-init="init(<?php echo $ticket->getId() ?>,<?php echo $thisstaff->getId(); ?>,<?php echo $ticket->getTopicID(); ?>)" ng-controller="rapportCtrl">
    <div name="rapport" style="display:none" id="rapport" class="col-md-12">
      <div class="borderShadow">
       <div>
            <div><span></span>
                <label class="required" for="_b22c0215f6129f93">
                          Date d'intervention :
                                        <span class="error">*</span>
                                      </label>

                <input type="text" ng-model="date_new_inter" name="date_new_inter"></div>
                <!--<script type="text/javascript">

                        $('input[name="date_new_inter"]').datepicker({
                            startView: 1,
                            defaultDate: debut,
                            format: 'dd/mm/yyyy',
                            autoclose: true
                        });

                </script>-->
            <div><label class="required" for="b22c0215f6129f93:time">
                          Heure d'arrivé :
                                        <span class="error">*</span>
                                      </label>
            <select ng-model="arrive_new_inter" name="arrive_inter" id="b22c0215f6129f93:time" style="display:inline-block;width:auto"><option value="" selected="">Temps</option>
<option value="00:00" selected="selected">00:00</option><option value="00:15">00:15</option><option value="00:30">00:30</option><option value="00:45">00:45</option><option value="01:00">01:00</option><option value="01:15">01:15</option><option value="01:30">01:30</option><option value="01:45">01:45</option><option value="02:00">02:00</option><option value="02:15">02:15</option><option value="02:30">02:30</option><option value="02:45">02:45</option><option value="03:00">03:00</option><option value="03:15">03:15</option><option value="03:30">03:30</option><option value="03:45">03:45</option><option value="04:00">04:00</option><option value="04:15">04:15</option><option value="04:30">04:30</option><option value="04:45">04:45</option><option value="05:00">05:00</option><option value="05:15">05:15</option><option value="05:30">05:30</option><option value="05:45">05:45</option><option value="06:00">06:00</option><option value="06:15">06:15</option><option value="06:30">06:30</option><option value="06:45">06:45</option><option value="07:00">07:00</option><option value="07:15">07:15</option><option value="07:30">07:30</option><option value="07:45">07:45</option><option value="08:00">08:00</option><option value="08:15">08:15</option><option value="08:30">08:30</option><option value="08:45">08:45</option><option value="09:00">09:00</option><option value="09:15">09:15</option><option value="09:30">09:30</option><option value="09:45">09:45</option><option value="10:00">10:00</option><option value="10:15">10:15</option><option value="10:30">10:30</option><option value="10:45">10:45</option><option value="11:00">11:00</option><option value="11:15">11:15</option><option value="11:30">11:30</option><option value="11:45">11:45</option><option value="12:00">12:00</option><option value="12:15">12:15</option><option value="12:30">12:30</option><option value="12:45">12:45</option><option value="13:00">13:00</option><option value="13:15">13:15</option><option value="13:30">13:30</option><option value="13:45">13:45</option><option value="14:00">14:00</option><option value="14:15">14:15</option><option value="14:30">14:30</option><option value="14:45">14:45</option><option value="15:00">15:00</option><option value="15:15">15:15</option><option value="15:30">15:30</option><option value="15:45">15:45</option><option value="16:00">16:00</option><option value="16:15">16:15</option><option value="16:30">16:30</option><option value="16:45">16:45</option><option value="17:00">17:00</option><option value="17:15">17:15</option><option value="17:30">17:30</option><option value="17:45">17:45</option><option value="18:00">18:00</option><option value="18:15">18:15</option><option value="18:30">18:30</option><option value="18:45">18:45</option><option value="19:00">19:00</option><option value="19:15">19:15</option><option value="19:30">19:30</option><option value="19:45">19:45</option><option value="20:00">20:00</option><option value="20:15">20:15</option><option value="20:30">20:30</option><option value="20:45">20:45</option><option value="21:00">21:00</option><option value="21:15">21:15</option><option value="21:30">21:30</option><option value="21:45">21:45</option><option value="22:00">22:00</option><option value="22:15">22:15</option><option value="22:30">22:30</option><option value="22:45">22:45</option><option value="23:00">23:00</option><option value="23:15">23:15</option><option value="23:30">23:30</option><option value="23:45">23:45</option></select></div>
            <div><label class="required" for="b22c0215f6129f94:time">
                          Heure de départ :
                                        <span class="error">*</span>
                                      </label>
            <select ng-model="depart_new_inter" name="depart_inter" id="b22c0215f6129f94:time" style="display:inline-block;width:auto"><option value="" selected="">Temps</option>
<option value="00:00" selected="selected">00:00</option><option value="00:15">00:15</option><option value="00:30">00:30</option><option value="00:45">00:45</option><option value="01:00">01:00</option><option value="01:15">01:15</option><option value="01:30">01:30</option><option value="01:45">01:45</option><option value="02:00">02:00</option><option value="02:15">02:15</option><option value="02:30">02:30</option><option value="02:45">02:45</option><option value="03:00">03:00</option><option value="03:15">03:15</option><option value="03:30">03:30</option><option value="03:45">03:45</option><option value="04:00">04:00</option><option value="04:15">04:15</option><option value="04:30">04:30</option><option value="04:45">04:45</option><option value="05:00">05:00</option><option value="05:15">05:15</option><option value="05:30">05:30</option><option value="05:45">05:45</option><option value="06:00">06:00</option><option value="06:15">06:15</option><option value="06:30">06:30</option><option value="06:45">06:45</option><option value="07:00">07:00</option><option value="07:15">07:15</option><option value="07:30">07:30</option><option value="07:45">07:45</option><option value="08:00">08:00</option><option value="08:15">08:15</option><option value="08:30">08:30</option><option value="08:45">08:45</option><option value="09:00">09:00</option><option value="09:15">09:15</option><option value="09:30">09:30</option><option value="09:45">09:45</option><option value="10:00">10:00</option><option value="10:15">10:15</option><option value="10:30">10:30</option><option value="10:45">10:45</option><option value="11:00">11:00</option><option value="11:15">11:15</option><option value="11:30">11:30</option><option value="11:45">11:45</option><option value="12:00">12:00</option><option value="12:15">12:15</option><option value="12:30">12:30</option><option value="12:45">12:45</option><option value="13:00">13:00</option><option value="13:15">13:15</option><option value="13:30">13:30</option><option value="13:45">13:45</option><option value="14:00">14:00</option><option value="14:15">14:15</option><option value="14:30">14:30</option><option value="14:45">14:45</option><option value="15:00">15:00</option><option value="15:15">15:15</option><option value="15:30">15:30</option><option value="15:45">15:45</option><option value="16:00">16:00</option><option value="16:15">16:15</option><option value="16:30">16:30</option><option value="16:45">16:45</option><option value="17:00">17:00</option><option value="17:15">17:15</option><option value="17:30">17:30</option><option value="17:45">17:45</option><option value="18:00">18:00</option><option value="18:15">18:15</option><option value="18:30">18:30</option><option value="18:45">18:45</option><option value="19:00">19:00</option><option value="19:15">19:15</option><option value="19:30">19:30</option><option value="19:45">19:45</option><option value="20:00">20:00</option><option value="20:15">20:15</option><option value="20:30">20:30</option><option value="20:45">20:45</option><option value="21:00">21:00</option><option value="21:15">21:15</option><option value="21:30">21:30</option><option value="21:45">21:45</option><option value="22:00">22:00</option><option value="22:15">22:15</option><option value="22:30">22:30</option><option value="22:45">22:45</option><option value="23:00">23:00</option><option value="23:15">23:15</option><option value="23:30">23:30</option><option value="23:45">23:45</option></select></div>
       </div>
       <br>
                   <!--Gestion des type de rapport-->

            <label><input type="radio" name="type" value="Contrat" checked>Contrat</label>
            <select name="selectContrat" id="selectContrat">
              <?php
                $contrats = ContratModel::objects()->filter(array('org'=>'411ADSEA')); //$orgName
                //TODO : change 411ADSEA TO $orgname
                foreach($contrats as $contrat){
              ?>
                <option><?= $contrat->ht['type']; ?></option>

              <?php } ?>
              <option>OFFERT</option>
            </select>
            <label><input type="radio" name="type" value="Instal" checked>Instal</label>
            <select name="selectInstal" id="selectInstal">
              <option>Hotline</option>
              <option>Atelier/Sur site</option>
              <option>Régie</option>option
              <option>Téléphonie</option>
            </select>
            <label><input style="margin-bottom:15px;" type="radio" name="type" value="Formation" checked>Formation</label>

       <label class="required" for="symptomesObservations">
                          Symptômes et observations :
                                        <span class="error">*</span>
        </label>
       <textarea name="new_symptomesObservations" id="new_symptomesObservations" cols="50"
                        data-signature-field="signature" data-dept-id="<?php echo $dept->getId(); ?>"
                        data-signature="<?php
                            echo Format::htmlchars(Format::viewableImages($signature)); ?>"
                        placeholder="<?php echo __(
                        'Start writing your response here. Use canned responses from the drop-down above'
                        ); ?>"
                        rows="9" wrap="soft"
                        class="<?php if ($cfg->isRichTextEnabled()) echo 'richtext';
                            ?> draft draft-delete" <?php
    list($draft, $attrs) = Draft::getDraftAndDataAttrs('ticket.response', $ticket->getId(), $info['response']);
    echo $attrs; ?>><?php echo $_POST ? $info['response'] : $draft;
                    ?></textarea>
        <br>

        <div>
            <h2>Sortie de stock :</h2>
            <div class="articleSortie" ng-repeat="article in stockOut">{{article.reference}} ({{article.quantite}})</div>
        </div>

        <button ng-click="getStock('<?php echo $thisstaff->getStock() ?>')" class="pending getStock">Récupérer le stock</button>
        <button class="cancel pending newRapport" type="cancel" style="float:right">Annuler</button>
        <input ng-click="addRapport()" class="horaire add save pending pull-right" type="submit" name="addRapport">
        </div>
   </div>
   <div class="addRapport col-md-12">
        <h4>Nouveau rapport</h4>
    </div>

    <!--RAPPORTS-->
    <div class="modal fade" id="signature" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Impression du rapport</h4>
          </div>
          <div class="modal-body">
                <canvas id="signature-pad"></canvas>
          </div>
          <div class="modal-footer">
            <button type="button" ng-click="removePDFView($event)" class="btn btn-default">Annuler</button>
            <button type="button" ng-click="displaySignature($event)" class="btn btn-primary">Signer</button>
          </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div>
    <div class="col-md-12 rapports">
      <div class="title">
         <div class="titling"></div>
          <img class="imgRapport" src="../assets/default/images/report.png"/>
          <h4>Liste des rapports</h4>
          <!--<a class="no-pjax printRapport" target="_blank" ng-href="./tickets.php?id={{ticketID}}&a=printR&idR={{rapportID}}"><i class="fa fa-print fa-2x" id="{{rapport.id}}" aria-hidden="true" style="color:black"></i></a>-->
          <a class="no-pjax printRapport" ng-click="printRapport()" target="_blank"><i class="fa fa-print fa-2x" aria-hidden="true" style="color:black"></i></a>
      </div>
       <table width="100%" style="table-layout:fixed">
           <thead>
               <th>Numero du rapport</th>
               <th>Date création rapport</th>
               <th>Intervenant</th>
               <th>Situation</th>
               <th>Contrat</th>
               <th>Instal</th>
           </thead>
           <tbody>
            <tr ng_click="setRapportID($event,$index,rapport.id)" ng-repeat="rapport in rapports" ng-class="$first ? 'active' : ''" id="{{$index}}">
                <td>{{rapport.id}}  <span ng-show="rapport.stock.length" class="glyphicon glyphicon-shopping-cart"></span></td>
                <td style="background:{{rapport.couleur}};color:white">{{rapport.date_rapport | date:'dd/MM/yyyy'}}</td>
                <td>{{rapport.firstname}} {{rapport.lastname}}</td>
                <td>{{rapport.topic}}</td>
                <td>{{rapport.contrat == 0 ? "" : rapport.contrat}}</td>
                <td>{{rapport.instal == 0 ? "" : rapport.instal}}</td>
            </tr>
        </tbody>
        </table>
        <div id="addTimeDiv" style="display:none;margin-top:30px">
            <span></span>
            <label class="required" for="_b22c0215f6129f93">
                              Date d'intervention :
                                            <span class="error">*</span>
                                          </label>
            <input ng-model="date_inter" type="text" name="date_inter">

            <label class="required" for="b22c0215f6129f93:time">
                              Heure d'arrivé :
                                            <span class="error">*</span>
                                          </label>
            <select ng-model="arrive_inter" name="arrive_inter" id="b22c0215f6129f93:time" style="display:inline-block;width:auto"><option value="" selected="">Temps</option>
<option value="00:00" selected="selected">00:00</option><option value="00:15">00:15</option><option value="00:30">00:30</option><option value="00:45">00:45</option><option value="01:00">01:00</option><option value="01:15">01:15</option><option value="01:30">01:30</option><option value="01:45">01:45</option><option value="02:00">02:00</option><option value="02:15">02:15</option><option value="02:30">02:30</option><option value="02:45">02:45</option><option value="03:00">03:00</option><option value="03:15">03:15</option><option value="03:30">03:30</option><option value="03:45">03:45</option><option value="04:00">04:00</option><option value="04:15">04:15</option><option value="04:30">04:30</option><option value="04:45">04:45</option><option value="05:00">05:00</option><option value="05:15">05:15</option><option value="05:30">05:30</option><option value="05:45">05:45</option><option value="06:00">06:00</option><option value="06:15">06:15</option><option value="06:30">06:30</option><option value="06:45">06:45</option><option value="07:00">07:00</option><option value="07:15">07:15</option><option value="07:30">07:30</option><option value="07:45">07:45</option><option value="08:00">08:00</option><option value="08:15">08:15</option><option value="08:30">08:30</option><option value="08:45">08:45</option><option value="09:00">09:00</option><option value="09:15">09:15</option><option value="09:30">09:30</option><option value="09:45">09:45</option><option value="10:00">10:00</option><option value="10:15">10:15</option><option value="10:30">10:30</option><option value="10:45">10:45</option><option value="11:00">11:00</option><option value="11:15">11:15</option><option value="11:30">11:30</option><option value="11:45">11:45</option><option value="12:00">12:00</option><option value="12:15">12:15</option><option value="12:30">12:30</option><option value="12:45">12:45</option><option value="13:00">13:00</option><option value="13:15">13:15</option><option value="13:30">13:30</option><option value="13:45">13:45</option><option value="14:00">14:00</option><option value="14:15">14:15</option><option value="14:30">14:30</option><option value="14:45">14:45</option><option value="15:00">15:00</option><option value="15:15">15:15</option><option value="15:30">15:30</option><option value="15:45">15:45</option><option value="16:00">16:00</option><option value="16:15">16:15</option><option value="16:30">16:30</option><option value="16:45">16:45</option><option value="17:00">17:00</option><option value="17:15">17:15</option><option value="17:30">17:30</option><option value="17:45">17:45</option><option value="18:00">18:00</option><option value="18:15">18:15</option><option value="18:30">18:30</option><option value="18:45">18:45</option><option value="19:00">19:00</option><option value="19:15">19:15</option><option value="19:30">19:30</option><option value="19:45">19:45</option><option value="20:00">20:00</option><option value="20:15">20:15</option><option value="20:30">20:30</option><option value="20:45">20:45</option><option value="21:00">21:00</option><option value="21:15">21:15</option><option value="21:30">21:30</option><option value="21:45">21:45</option><option value="22:00">22:00</option><option value="22:15">22:15</option><option value="22:30">22:30</option><option value="22:45">22:45</option><option value="23:00">23:00</option><option value="23:15">23:15</option><option value="23:30">23:30</option><option value="23:45">23:45</option></select>
            <label class="required" for="b22c0215f6129f94:time">
                              Heure de départ :
                                            <span class="error">*</span>
                                          </label>
            <select ng-model="depart_inter" name="depart_inter" id="b22c0215f6129f94:time" style="display:inline-block;width:auto"><option value="" selected="">Temps</option>
<option value="00:00" selected="selected">00:00</option><option value="00:15">00:15</option><option value="00:30">00:30</option><option value="00:45">00:45</option><option value="01:00">01:00</option><option value="01:15">01:15</option><option value="01:30">01:30</option><option value="01:45">01:45</option><option value="02:00">02:00</option><option value="02:15">02:15</option><option value="02:30">02:30</option><option value="02:45">02:45</option><option value="03:00">03:00</option><option value="03:15">03:15</option><option value="03:30">03:30</option><option value="03:45">03:45</option><option value="04:00">04:00</option><option value="04:15">04:15</option><option value="04:30">04:30</option><option value="04:45">04:45</option><option value="05:00">05:00</option><option value="05:15">05:15</option><option value="05:30">05:30</option><option value="05:45">05:45</option><option value="06:00">06:00</option><option value="06:15">06:15</option><option value="06:30">06:30</option><option value="06:45">06:45</option><option value="07:00">07:00</option><option value="07:15">07:15</option><option value="07:30">07:30</option><option value="07:45">07:45</option><option value="08:00">08:00</option><option value="08:15">08:15</option><option value="08:30">08:30</option><option value="08:45">08:45</option><option value="09:00">09:00</option><option value="09:15">09:15</option><option value="09:30">09:30</option><option value="09:45">09:45</option><option value="10:00">10:00</option><option value="10:15">10:15</option><option value="10:30">10:30</option><option value="10:45">10:45</option><option value="11:00">11:00</option><option value="11:15">11:15</option><option value="11:30">11:30</option><option value="11:45">11:45</option><option value="12:00">12:00</option><option value="12:15">12:15</option><option value="12:30">12:30</option><option value="12:45">12:45</option><option value="13:00">13:00</option><option value="13:15">13:15</option><option value="13:30">13:30</option><option value="13:45">13:45</option><option value="14:00">14:00</option><option value="14:15">14:15</option><option value="14:30">14:30</option><option value="14:45">14:45</option><option value="15:00">15:00</option><option value="15:15">15:15</option><option value="15:30">15:30</option><option value="15:45">15:45</option><option value="16:00">16:00</option><option value="16:15">16:15</option><option value="16:30">16:30</option><option value="16:45">16:45</option><option value="17:00">17:00</option><option value="17:15">17:15</option><option value="17:30">17:30</option><option value="17:45">17:45</option><option value="18:00">18:00</option><option value="18:15">18:15</option><option value="18:30">18:30</option><option value="18:45">18:45</option><option value="19:00">19:00</option><option value="19:15">19:15</option><option value="19:30">19:30</option><option value="19:45">19:45</option><option value="20:00">20:00</option><option value="20:15">20:15</option><option value="20:30">20:30</option><option value="20:45">20:45</option><option value="21:00">21:00</option><option value="21:15">21:15</option><option value="21:30">21:30</option><option value="21:45">21:45</option><option value="22:00">22:00</option><option value="22:15">22:15</option><option value="22:30">22:30</option><option value="22:45">22:45</option><option value="23:00">23:00</option><option value="23:15">23:15</option><option value="23:30">23:30</option><option value="23:45">23:45</option></select>
                <br><br>

            <label class="required" for="symptomesObservations">
                              Symptômes et observations :
                                            <span class="error">*</span>
                                          </label>
            <textarea name="symptomesObservations" id="symptomesObservations" cols="50"
                                data-signature-field="signature" data-dept-id="<?php echo $dept->getId(); ?>"
                                data-signature="<?php
                                    echo Format::htmlchars(Format::viewableImages($signature)); ?>"
                                placeholder="<?php echo __(
                                'Start writing your response here. Use canned responses from the drop-down above'
                                ); ?>"
                                rows="9" wrap="soft"
                                class="<?php if ($cfg->isRichTextEnabled()) echo 'richtext';
                                    ?> draft draft-delete" <?php
            list($draft, $attrs) = Draft::getDraftAndDataAttrs('ticket.response', $ticket->getId(), $info['response']);
            echo $attrs; ?>></textarea>
            <br>
            <button ng-click="unShowUpdate($event)" class="cancel pending" type="cancel" style="float:right">Annuler</button>
            <input ng-click="insertOrUpdateHoraire()" class="horaire add save pending" type="submit" name="addRapport" style="float:right">
        </div>
        <div ng-repeat="rapport in rapports" style="float:none" class="eachRapport col-md-12 col-lg-12 col-xs-12" ng-class="$first ? 'active' : ''" id="{{$index}}">

            <div class="col-md-12 col-xs-12 rapport">
               <div class="col-lg-4 col-md-12 col-xs-12" id="borderIdentity">
                <div class="identity" id="{{rapport.id}}">
                    <div class="title">
                        <div class="titling"></div>
                        <img ng-click="showUpdate()" class="addTime" src="../assets/default/images/timeplus.png" id="<?php echo $rapport['id'] ?>"/>
                        <div class="commentTitle horaires">
                            <p>Rapports n° {{rapport.id}} :</p>
                        </div>
                    </div>
                    <div class="content">
                        <span id="date_inter">Intervenant : {{rapport.firstname}} {{rapport.lastname}}</span>

                        <div ng-repeat="horaire in rapport.horaires" class="horaire">
                            <div>
                                <span ng-style="">Intervention du {{horaire.arrive_inter | mFormat:'dddd DD MMMM YYYY' | capitalize}}</span>
                            </div>
                            <div>
                               <div class="floatSDL">
                                <p id="startDate">{{horaire.arrive_inter | mFormat:'HH:mm' }}</p>
                                </div>
                                <div class="floatEDR">
                                <p id="endDate">{{horaire.depart_inter | mFormat:'HH:mm' }}</p>
                                </div>
                                <p class="greenLine">{{horaire.nbHours}}</p>
                            </div>
                        </div>

                    </div>
                    <div class="totalHour">
                        <div id="totalTitle">
                            <span>Total</span>
                        </div>
                        <div id="total" ng-bind-html="rapport.totalHours | pastTimes:this">
                        </div>
                    </div>
                </div>
                </div>
                <div class="col-lg-8 col-md-12 col-xs-12" id="borderProperty">
                <div class="property" id="<?php echo $rapport['id'] ?>">
                  <div class="title">
                     <div class="titling"></div>
                      <h4>Description : </h4>
                  </div>
                  <div class="content">
                    <div ng-repeat="horaire in rapport.horaires">
                         <div class="comment">
                             <div class="titleComment">
                                <div class="green"></div>
                                <img class="modifyInter" src="../assets/default/images/edit.png" ng_click="showUpdate($parent.$index,$index,horaire.id)" id="{{horaire.id}}"/>
                                 <div class="commentTitle"><p>Intervention du {{horaire.arrive_inter | mFormat:'dddd DD MMMM YYYY' | capitalize}}</p></div>
                             </div>
                             <div class="commentContent">
                                 <span ng-bind-html="horaire.comment"></span>
                                 <hr style="margin-top: 10px;margin-bottom: 10px;">
                                 <!--SORTIE DE STOCK-->
                                 <div class="articleSortie" style="margin-bottom:0px" ng-repeat="article in rapport.stock">{{article.reference}} ({{article.quantite}})</div>
                             </div>
                          </div>
                     </div>
                  </div>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="ticket_thread" class="tab_content">

<?php
    // Render ticket thread
    $ticket->getThread()->render(
            array('M','R', 'N'),
            array(
                'html-id'   => 'ticketThread',
                'mode'      => Thread::MODE_STAFF,
                'sort'      => $thisstaff->thread_view_order
                )
            );

?>
<div class="clear"></div>
<?php
if ($errors['err'] && isset($_POST['a'])) {
    // Reflect errors back to the tab.
    $errors[$_POST['a']] = $errors['err'];
} elseif($msg) { ?>
    <div id="msg_notice"><?php echo $msg; ?></div>
<?php
} elseif($warn) { ?>
    <div id="msg_warning"><?php echo $warn; ?></div>
<?php
} ?>

<div class="sticky bar stop actions" id="response_options">
    <ul class="tabs" id="response-tabs">
        <?php
        if ($role->hasPerm(TicketModel::PERM_REPLY)) { ?>
        <li class="active <?php
            echo isset($errors['reply']) ? 'error' : ''; ?>"><a
            href="#reply" id="post-reply-tab"><?php echo __('Post Reply');?></a></li>
        <?php
        } ?>
        <li><a href="#note" <?php
            echo isset($errors['postnote']) ?  'class="error"' : ''; ?>
            id="post-note-tab"><?php echo __('Post Internal Note');?></a></li>
    </ul>
    <?php
    if ($role->hasPerm(TicketModel::PERM_REPLY)) { ?>
    <form id="reply" class="tab_content spellcheck exclusive"
        data-lock-object-id="ticket/<?php echo $ticket->getId(); ?>"
        data-lock-id="<?php echo $mylock ? $mylock->getId() : ''; ?>"
        action="tickets.php?id=<?php
        echo $ticket->getId(); ?>#reply" name="reply" method="post" enctype="multipart/form-data">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="msgId" value="<?php echo $msgId; ?>">
        <input type="hidden" name="a" value="reply">
        <input type="hidden" name="lockCode" value="<?php echo $mylock ? $mylock->getCode() : ''; ?>">

            <?php
            if ($errors['reply']) {?>
            <span><?php echo $errors['reply']; ?>&nbsp;</span>
            <?php
            }?>


                <label><strong><?php echo __('To'); ?>:</strong></label>
                    <?php
                    # XXX: Add user-to-name and user-to-email HTML ID#s
                    $to =sprintf('%s &lt;%s&gt;',
                            Format::htmlchars($ticket->getName()),
                            $ticket->getReplyToEmail());
                    $emailReply = (!isset($info['emailreply']) || $info['emailreply']);
                    ?>
                    <select id="emailreply" name="emailreply">
                        <option value="1" <?php echo $emailReply ?  'selected="selected"' : ''; ?>><?php echo $to; ?></option>
                        <option value="0" <?php echo !$emailReply ? 'selected="selected"' : ''; ?>
                        >&mdash; <?php echo __('Do Not Email Reply'); ?> &mdash;</option>
                    </select>

            <?php
            if(1) { //Make CC optional feature? NO, for now.
                ?>
            <tbody id="cc_sec"
                style="display:<?php echo $emailReply?  'table-row-group':'none'; ?>;">
             <tr>
                <td width="120">
                    <label><strong><?php echo __('Collaborators'); ?>:</strong></label>
                </td>
                <td>
                    <input type='checkbox' value='1' name="emailcollab"
                    id="t<?php echo $ticket->getThreadId(); ?>-emailcollab"
                        <?php echo ((!$info['emailcollab'] && !$errors) || isset($info['emailcollab']))?'checked="checked"':''; ?>
                        style="display:<?php echo $ticket->getThread()->getNumCollaborators() ? 'inline-block': 'none'; ?>;"
                        >
                    <?php
                    $recipients = __('Add Recipients');
                    if ($ticket->getThread()->getNumCollaborators())
                        $recipients = sprintf(__('Recipients (%d of %d)'),
                                $ticket->getThread()->getNumActiveCollaborators(),
                                $ticket->getThread()->getNumCollaborators());

                    echo sprintf('<span><a class="collaborators preview"
                            href="#thread/%d/collaborators"><span id="t%d-recipients">%s</span></a></span>',
                            $ticket->getThreadId(),
                            $ticket->getThreadId(),
                            $recipients);
                   ?>
                </td>
             </tr>
            </tbody>
            <?php
            } ?>
            <tbody id="resp_sec">
            <?php
            if($errors['response']) {?>
            <tr><td width="120">&nbsp;</td><td class="error"><?php echo $errors['response']; ?>&nbsp;</td></tr>
            <?php
            }?>
            <tr>
                <td width="120" style="vertical-align:top">
                    <label><strong><?php echo __('Response');?>:</strong></label>
                </td>
                <td>
<?php if ($cfg->isCannedResponseEnabled()) { ?>
                    <select id="cannedResp" name="cannedResp">
                        <option value="0" selected="selected"><?php echo __('Select a canned response');?></option>
                        <option value='original'><?php echo __('Original Message'); ?></option>
                        <option value='lastmessage'><?php echo __('Last Message'); ?></option>
                        <?php
                        if(($cannedResponses=Canned::responsesByDeptId($ticket->getDeptId()))) {
                            echo '<option value="0" disabled="disabled">
                                ------------- '.__('Premade Replies').' ------------- </option>';
                            foreach($cannedResponses as $id =>$title)
                                echo sprintf('<option value="%d">%s</option>',$id,$title);
                        }
                        ?>
                    </select>
                    <br>
<?php } # endif (canned-resonse-enabled)
                    $signature = '';
                    switch ($thisstaff->getDefaultSignatureType()) {
                    case 'dept':
                        if ($dept && $dept->canAppendSignature())
                           $signature = $dept->getSignature();
                       break;
                    case 'mine':
                        $signature = $thisstaff->getSignature();
                        break;
                    } ?>
                    <input type="hidden" name="draft_id" value=""/>
                    <textarea name="response" id="response" cols="50"
                        data-signature-field="signature" data-dept-id="<?php echo $dept->getId(); ?>"
                        data-signature="<?php
                            echo Format::htmlchars(Format::viewableImages($signature)); ?>"
                        placeholder="<?php echo __(
                        'Start writing your response here. Use canned responses from the drop-down above'
                        ); ?>"
                        rows="9" wrap="soft"
                        class="<?php if ($cfg->isRichTextEnabled()) echo 'richtext';
                            ?> draft draft-delete" <?php
    list($draft, $attrs) = Draft::getDraftAndDataAttrs('ticket.response', $ticket->getId(), $info['response']);
    echo $attrs; ?>><?php echo $_POST ? $info['response'] : $draft;
                    ?></textarea>
                <div id="reply_form_attachments" class="attachments">
                <?php
                    print $response_form->getField('attachments')->render();
                ?>
                </div>
                </td>
            </tr>
            <tr>
                <td width="120">
                    <label for="signature" class="left"><?php echo __('Signature');?>:</label>
                </td>
                <td>
                    <?php
                    $info['signature']=$info['signature']?$info['signature']:$thisstaff->getDefaultSignatureType();
                    ?>
                    <label><input type="radio" name="signature" value="none" checked="checked"> <?php echo __('None');?></label>
                    <?php
                    if($thisstaff->getSignature()) {?>
                    <label><input type="radio" name="signature" value="mine"
                        <?php echo ($info['signature']=='mine')?'checked="checked"':''; ?>> <?php echo __('My Signature');?></label>
                    <?php
                    } ?>
                    <?php
                    if($dept && $dept->canAppendSignature()) { ?>
                    <label><input type="radio" name="signature" value="dept"
                        <?php echo ($info['signature']=='dept')?'checked="checked"':''; ?>>
                        <?php echo sprintf(__('Department Signature (%s)'), Format::htmlchars($dept->getName())); ?></label>
                    <?php
                    } ?>
                </td>
            </tr>
            <tr>
                <td width="120" style="vertical-align:top">
                    <label><strong><?php echo __('Ticket Status');?>:</strong></label>
                </td>
                <td>
                    <?php
                    $outstanding = false;
                    if ($role->hasPerm(TicketModel::PERM_CLOSE)
                            && is_string($warning=$ticket->isCloseable())) {
                        $outstanding =  true;
                        echo sprintf('<div class="warning-banner">%s</div>', $warning);
                    } ?>
                    <select name="reply_status_id">
                    <?php
                    $statusId = $info['reply_status_id'] ?: $ticket->getStatusId();
                    $states = array('open');
                    if ($role->hasPerm(TicketModel::PERM_CLOSE) && !$outstanding)
                        $states = array_merge($states, array('closed'));

                    foreach (TicketStatusList::getStatuses(
                                array('states' => $states)) as $s) {
                        if (!$s->isEnabled()) continue;
                        $selected = ($statusId == $s->getId());
                        echo sprintf('<option value="%d" %s>%s%s</option>',
                                $s->getId(),
                                $selected
                                 ? 'selected="selected"' : '',
                                __($s->getName()),
                                $selected
                                ? (' ('.__('current').')') : ''
                                );
                    }
                    ?>
                    </select>
                </td>
            </tr>
         </tbody>
        </table>
        <div>
          <?php

            $stocks = StockModel::objects()->filter(array('thread_entry_id'=>null,'dispo'=>1));
          ?>
          <label>Mettre un matériel en prêt : <select name="pret">
            <option selected="selected">Vueillez choisir un pret</option>
            <?php foreach($stocks as $stock){ ?>
            <option><?= $stock->designation . " - " . $stock->numserie; ?></option>
            <?php } ?>
          </select></label>
        </div>
        <p  style="text-align:center;">
            <input class="save pending" type="submit" value="<?php echo __('Post Reply');?>">
            <input class="" type="reset" value="<?php echo __('Reset');?>">
        </p>
    </form>
    <?php
    } ?>
    <form id="note" class="tab_content spellcheck exclusive"
       style="display:none"
        data-lock-object-id="ticket/<?php echo $ticket->getId(); ?>"
        data-lock-id="<?php echo $mylock ? $mylock->getId() : ''; ?>"
        action="tickets.php?id=<?php echo $ticket->getId(); ?>#note"
        name="note" method="post" enctype="multipart/form-data">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="locktime" value="<?php echo $cfg->getLockTime() * 60; ?>">
        <input type="hidden" name="a" value="postnote">
        <input type="hidden" name="lockCode" value="<?php echo $mylock ? $mylock->getCode() : ''; ?>">
        <table width="100%" border="0" cellspacing="0" cellpadding="3">
            <?php
            if($errors['postnote']) {?>
            <tr>
                <td width="120">&nbsp;</td>
                <td class="error"><?php echo $errors['postnote']; ?></td>
            </tr>
            <?php
            } ?>
            <tr>
                <td width="120" style="vertical-align:top">
                    <label><strong><?php echo __('Internal Note'); ?>:</strong><span class='error'>&nbsp;*</span></label>
                </td>
                <td>
                    <div>
                        <div class="faded" style="padding-left:0.15em"><?php
                        echo __('Note title - summary of the note (optional)'); ?></div>
                        <input type="text" name="title" id="title" size="60" value="<?php echo $info['title']; ?>" >
                        <br/>
                        <span class="error">&nbsp;<?php echo $errors['title']; ?></span>
                    </div>
                    <br/>
                    <div class="error"><?php echo $errors['note']; ?></div>
                    <textarea name="note" id="internal_note" cols="80"
                        placeholder="<?php echo __('Note details'); ?>"
                        rows="9" wrap="soft"
                        class="<?php if ($cfg->isRichTextEnabled()) echo 'richtext';
                            ?> draft draft-delete" <?php
    list($draft, $attrs) = Draft::getDraftAndDataAttrs('ticket.note', $ticket->getId(), $info['note']);
    echo $attrs; ?>><?php echo $_POST ? $info['note'] : $draft;
                        ?></textarea>
                <div class="attachments">
                <?php
                    print $note_form->getField('attachments')->render();
                ?>
                </div>
                </td>
            </tr>
            <tr><td colspan="2">&nbsp;</td></tr>
            <!--<tr>
                <td colspan="2">
                    <fieldset class="field " id="field_b22c0215f6129f93" data-field-id="42">
                      <label class="required" for="_b22c0215f6129f93">
                          Date de début d'intervention :
                                        <span class="error">*</span>
                                      </label>
                <input type="text" name="b22c0215f6129f93" id="_b22c0215f6129f93" style="display:inline-block;width:auto" value="" size="12" autocomplete="off" class="dp hasDatepicker"><button type="button" class="ui-datepicker-trigger"><img src="./images/cal.png" alt="..." title="..."></button>
                <script type="text/javascript">

                        $('input[name="b22c0215f6129f93"]').datepicker({
                            numberOfMonths: 2,
                            showButtonPanel: true,
                            startView: 1,
                            showOn:'both',
                            dateFormat: 'dd/mm/yyyy',
                            autoclose: true
                        });

                </script>
                &nbsp;<select name="b22c0215f6129f93:time" id="b22c0215f6129f93:time" style="display:inline-block;width:auto"><option value="" selected="">Temps</option><option value="23:45">23:45</option><option value="23:30">23:30</option><option value="23:15">23:15</option><option value="23:00">23:00</option><option value="22:45">22:45</option><option value="22:30">22:30</option><option value="22:15">22:15</option><option value="22:00">22:00</option><option value="21:45">21:45</option><option value="21:30">21:30</option><option value="21:15">21:15</option><option value="21:00">21:00</option><option value="20:45">20:45</option><option value="20:30">20:30</option><option value="20:15">20:15</option><option value="20:00">20:00</option><option value="19:45">19:45</option><option value="19:30">19:30</option><option value="19:15">19:15</option><option value="19:00">19:00</option><option value="18:45">18:45</option><option value="18:30">18:30</option><option value="18:15">18:15</option><option value="18:00">18:00</option><option value="17:45">17:45</option><option value="17:30">17:30</option><option value="17:15">17:15</option><option value="17:00">17:00</option><option value="16:45">16:45</option><option value="16:30">16:30</option><option value="16:15">16:15</option><option value="16:00">16:00</option><option value="15:45">15:45</option><option value="15:30">15:30</option><option value="15:15">15:15</option><option value="15:00">15:00</option><option value="14:45">14:45</option><option value="14:30">14:30</option><option value="14:15">14:15</option><option value="14:00">14:00</option><option value="13:45">13:45</option><option value="13:30">13:30</option><option value="13:15">13:15</option><option value="13:00">13:00</option><option value="12:45">12:45</option><option value="12:30">12:30</option><option value="12:15">12:15</option><option value="12:00">12:00</option><option value="11:45">11:45</option><option value="11:30">11:30</option><option value="11:15">11:15</option><option value="11:00">11:00</option><option value="10:45">10:45</option><option value="10:30">10:30</option><option value="10:15">10:15</option><option value="10:00">10:00</option><option value="09:45">09:45</option><option value="09:30">09:30</option><option value="09:15">09:15</option><option value="09:00">09:00</option><option value="08:45">08:45</option><option value="08:30">08:30</option><option value="08:15">08:15</option><option value="08:00">08:00</option><option value="07:45">07:45</option><option value="07:30">07:30</option><option value="07:15">07:15</option><option value="07:00">07:00</option><option value="06:45">06:45</option><option value="06:30">06:30</option><option value="06:15">06:15</option><option value="06:00">06:00</option><option value="05:45">05:45</option><option value="05:30">05:30</option><option value="05:15">05:15</option><option value="05:00">05:00</option><option value="04:45">04:45</option><option value="04:30">04:30</option><option value="04:15">04:15</option><option value="04:00">04:00</option><option value="03:45">03:45</option><option value="03:30">03:30</option><option value="03:15">03:15</option><option value="03:00">03:00</option><option value="02:45">02:45</option><option value="02:30">02:30</option><option value="02:15">02:15</option><option value="02:00">02:00</option><option value="01:45">01:45</option><option value="01:30">01:30</option><option value="01:15">01:15</option><option value="01:00">01:00</option><option value="00:45">00:45</option><option value="00:30">00:30</option><option value="00:15">00:15</option><option value="00:00" selected="selected">00:00</option></select>     </fieldset>
               </td>
            </tr>-->
            <tr>
                <td width="120">
                    <label><?php echo __('Ticket Status');?>:</label>
                </td>
                <td>
                    <div class="faded"></div>
                    <select name="note_status_id">
                        <?php
                        $statusId = $info['note_status_id'] ?: $ticket->getStatusId();
                        $states = array('open');
                        if ($ticket->isCloseable() === true
                                && $role->hasPerm(TicketModel::PERM_CLOSE))
                            $states = array_merge($states, array('closed'));
                        foreach (TicketStatusList::getStatuses(
                                    array('states' => $states)) as $s) {
                            if (!$s->isEnabled()) continue;
                            $selected = $statusId == $s->getId();
                            echo sprintf('<option value="%d" %s>%s%s</option>',
                                    $s->getId(),
                                    $selected ? 'selected="selected"' : '',
                                    __($s->getName()),
                                    $selected ? (' ('.__('current').')') : ''
                                    );
                        }
                        ?>
                    </select>
                    &nbsp;<span class='error'>*&nbsp;<?php echo $errors['note_status_id']; ?></span>
                </td>
            </tr>

        </table>

       <p style="text-align:center;">
           <input class="save pending" type="submit" value="<?php echo __('Post Note');?>">
           <input class="" type="reset" value="<?php echo __('Reset');?>">
       </p>
   </form>
 </div>
 </div>
</div>
</div>
<div class="ticket_right col-md-3">
<div class="balls" style="display:none">
    <div class="ball"></div>
    <div class="ball1"></div>
</div>

<div class="stock" ng-controller="stockCtrl" style="display:none">
    <!--{{rapports}}-->
    <div class="form-group text-center">
      <label>Type de sortie : </label>
      <select ng-model="typeSortie">
        <option value="F" selected="selected">Facturable</option>
        <option value="O">Offert</option>
        <option value="P">Prêt</option>
      </select>
    </div>
    <table style="margin: 0 auto;">
        <thead>
            <th>Référence</th>
            <th>Quantité</th>
            <th>Sortie</th>
        </thead>
        <tbody>
            <tr ng-repeat="article in displayStock">
                <td>{{article.reference}}</td>
                <td style="text-align:center">{{article.quantite | number : 0}}</td>
                <td>
                    <div class="row">
                        <div class="">
                            <div class="input-group number-spinner">
                                <span class="input-group-btn data-dwn">
                                    <button class="btn btn-default btn-info" style="width:5px" ng-click="manageStock($index,article.reference,'dwn',$event)" data-dir="dwn"><span class="glyphicon glyphicon-minus" style="margin-left: -6px;font-size: 10px;"></span></button>
                                </span>
                                <input type="text" class="form-control text-center" value="0" min="0" max="{{::article.quantite}}">
                                <span class="input-group-btn data-up">
                                    <button class="btn btn-default btn-info" style="width:5px" ng-click="manageStock($index,article.reference,'up',$event)" data-dir="up"><span class="glyphicon glyphicon-plus" style="margin-left: -6px;font-size: 10px;"></span></button>
                                </span>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right;padding-top: 10px;background: white;">
                    <button ng-click="createDocument('<?php echo $orgName; ?>')">Valider</button>
                </td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="col-md-2 fixed-right">
<!--<div class="clear tixTitle has_bottom_border">
    <h3>
    <?php $subject_field = TicketForm::getInstance()->getField('subject');
        echo $subject_field->display($ticket->getSubject()); ?>
    </h3>
</div>-->

<div class="opening_info">
    <h2><?php echo $ticket->getStatus() ?></h2>
    <?php
        //Récupération de l'etat d'atelier
        if($ticket->getHelpTopic() == "Atelier"){

        }
    ?>
    <span><?php echo Format::htmlchars($ticket->getHelpTopic()); ?></span><br>
    <span>Créé le <?php echo Format::datetime($ticket->getCreateDate()) ?></span>
    <!--<th class="col-md-6"><?php echo __('Department');?>:</th>
    <td class="col-md-6"><?php echo Format::htmlchars($ticket->getDeptName()); ?></td>
    <th class="col-md-6"><?php echo __('Create Date');?>:</th>
    <td class="col-md-6"><?php echo Format::datetime($ticket->getCreateDate()); ?></td>-->
</div>

<div class="infoDemandeur">
    <hr>
    <h4>Sujet : </h4>
    <p>
        <?php $subject_field = TicketForm::getInstance()->getField('subject');
        echo $subject_field->display($ticket->getSubject()); ?>
    </p>
</div>

<div class='infoDemandeur'>
    <hr>
    <h4>Infos du demandeur</h4>
    <div class="avatar icon">
        <img width="50" src="../assets/default/images/avatar.png">
        <h3><?php echo strtoupper(substr(Format::htmlchars(ucfirst($ticket->getUser()->getFirstName()) . ' ' . ucfirst($ticket->getUser()->getName())),0,1)) ?></h3>
        <a href="./users.php?id=<?php echo $ticket->getUserId() ?>#tickets"><span><?php echo Format::htmlchars(ucfirst($ticket->getUser()->getFirstName()) . ' ' . ucfirst($ticket->getUser()->getName())) ?></span></a>
    </div>
    <div class="mail icon">
        <?php if(!empty($orgName)) { ?>
            <img width="20" src="../assets/default/images/company.png">
            <a href="./orgs.php?id=<?php echo Format::htmlchars($orgName) ?>"><span><?php echo Format::htmlchars($orgName) ?></span></a>
        <?php } else { ?>
            <img width="20" src="../assets/default/images/company.png">
            <input class="user_org" type="text">
            <p>( <?= $ticket->getUser()->getInfoOrg() ?> )</p>
        <?php } ?>
    </div>
    <div class="mail icon org" id="<?php echo $ticket->getOwner()->getOrgId(); ?>">
        <img width="20" src="../assets/default/images/mail.png">
        <a href="mailto:<?php echo Format::htmlchars($ticket->getEmail()) ?>"><span><?php echo Format::htmlchars($ticket->getEmail()) ?></span></a>
    </div>
    <div class="mail icon">
        <img width="20" src="../assets/default/images/tel.png">
        <span><?php
            $phone = Format::htmlchars($ticket->getPhoneNumber());
            $phone = str_replace(array('(',')',' ','-'), '',$phone);
            $phone = chunk_split($phone,2,' ');
            echo $phone;
        ?></span>
    </div>
    <div class="orgsList" style="display:none">

    </div>
</div>

<!--<div class="ticket_info">
            <table border="0" cellspacing="" cellpadding="4" width="100%">
                <tr>
                    <th class="col-md-6"><?php echo __('User'); ?>:</th>
                    <td class="col-md-6"><a href="#tickets/<?php echo $ticket->getId(); ?>/user"
                        onclick="javascript:
                            $.userLookup('ajax.php/tickets/<?php echo $ticket->getId(); ?>/user',
                                    function (user) {
                                        $('#user-'+user.id+'-name').text(user.name);
                                        $('#user-'+user.id+'-email').text(user.email);
                                        $('#user-'+user.id+'-phone').text(user.phone);)mùp
                                        $('select#emailreply option[value=1]').text(user.name+' <'+user.email+'>');
                                    });
                            return false;
                            "><i class="icon-user"></i> <span id="user-<?php echo $ticket->getOwnerId(); ?>-name"
                            ><?php echo Format::htmlchars($ticket->getName());
                        ?></span></a>
                        <?php
                        if ($user) { ?>
                            <a href="tickets.php?<?php echo Http::build_query(array(
                                'status'=>'open', 'a'=>'search', 'uid'=> $user->getId()
                            )); ?>" title="<?php echo __('Related Tickets'); ?>"
                            data-dropdown="#action-dropdown-stats">
                            (<b><?php echo $user->getNumTickets(); ?></b>)
                            </a>
                            <div id="action-dropdown-stats" class="action-dropdown anchor-right">
                                <ul>
                                    <?php
                                    if(($open=$user->getNumOpenTickets()))
                                        echo sprintf('<li><a href="tickets.php?a=search&status=open&uid=%s"><i class="icon-folder-open-alt icon-fixed-width"></i> %s</a></li>',
                                                $user->getId(), sprintf(_N('%d Open Ticket', '%d Open Tickets', $open), $open));

                                    if(($closed=$user->getNumClosedTickets()))
                                        echo sprintf('<li><a href="tickets.php?a=search&status=closed&uid=%d"><i
                                                class="icon-folder-close-alt icon-fixed-width"></i> %s</a></li>',
                                                $user->getId(), sprintf(_N('%d Closed Ticket', '%d Closed Tickets', $closed), $closed));
                                    ?>
                                    <li><a href="tickets.php?a=search&uid=<?php echo $ticket->getOwnerId(); ?>"><i class="icon-double-angle-right icon-fixed-width"></i> <?php echo __('All Tickets'); ?></a></li>
<?php   if ($thisstaff->hasPerm(User::PERM_DIRECTORY)) { ?>
                                    <li><a href="users.php?id=<?php echo
                                    $user->getId(); ?>"><i class="icon-user
                                    icon-fixed-width"></i> <?php echo __('Manage User'); ?></a></li>
<?php   } ?>
                                </ul>
                            </div>
<?php                   } # end if ($user) ?>
                    </td>
                </tr>
                <tr>
                    <th class="col-md-6"><?php echo __('Email'); ?>:</th>
                    <td class="col-md-6">
                        <span id="user-<?php echo $ticket->getOwnerId(); ?>-email"><?php echo $ticket->getEmail(); ?></span>
                    </td>
                </tr>
<?php   if ($user->getOrganization()) { ?>
                <tr>
                    <th class="col-md-6"><?php echo __('Organization'); ?>:</th>
                    <td class="col-md-6"><i class="icon-building"></i>
                    <?php echo Format::htmlchars($user->getOrganization()->getName()); ?>
                        <a href="tickets.php?<?php echo Http::build_query(array(
                            'status'=>'open', 'a'=>'search', 'orgid'=> $user->getOrgId()
                        )); ?>" title="<?php echo __('Related Tickets'); ?>"
                        data-dropdown="#action-dropdown-org-stats">
                        (<b><?php echo $user->getNumOrganizationTickets(); ?></b>)
                        </a>
                            <div id="action-dropdown-org-stats" class="action-dropdown anchor-right">
                                <ul>
<?php   if ($open = $user->getNumOpenOrganizationTickets()) { ?>
                                    <li><a href="tickets.php?<?php echo Http::build_query(array(
                                        'a' => 'search', 'status' => 'open', 'orgid' => $user->getOrgId()
                                    )); ?>"><i class="icon-folder-open-alt icon-fixed-width"></i>
                                    <?php echo sprintf(_N('%d Open Ticket', '%d Open Tickets', $open), $open); ?>
                                    </a></li>
<?php   }
        if ($closed = $user->getNumClosedOrganizationTickets()) { ?>
                                    <li><a href="tickets.php?<?php echo Http::build_query(array(
                                        'a' => 'search', 'status' => 'closed', 'orgid' => $user->getOrgId()
                                    )); ?>"><i class="icon-folder-close-alt icon-fixed-width"></i>
                                    <?php echo sprintf(_N('%d Closed Ticket', '%d Closed Tickets', $closed), $closed); ?>
                                    </a></li>
                                    <li><a href="tickets.php?<?php echo Http::build_query(array(
                                        'a' => 'search', 'orgid' => $user->getOrgId()
                                    )); ?>"><i class="icon-double-angle-right icon-fixed-width"></i> <?php echo __('All Tickets'); ?></a></li>
<?php   }
        if ($thisstaff->hasPerm(User::PERM_DIRECTORY)) { ?>
                                    <li><a href="orgs.php?id=<?php echo $user->getOrgId(); ?>"><i
                                        class="icon-building icon-fixed-width"></i> <?php
                                        echo __('Manage Organization'); ?></a></li>
<?php   } ?>
                                </ul>
                            </div>
                        </td>
                    </tr>
<?php   } # end if (user->org) ?>
                <tr>
                    <th class="col-md-6"><?php echo __('Source'); ?>:</th>
                    <td class="col-md-6"><?php
                        echo Format::htmlchars($ticket->getSource());

                        if (!strcasecmp($ticket->getSource(), 'Web') && $ticket->getIP())
                            echo '&nbsp;&nbsp; <span class="faded">('.Format::htmlchars($ticket->getIP()).')</span>';
                        ?>
                    </td>
                </tr>
            </table>
</div>-->

<div class='ticketProperty'>
    <hr>
    <h4>Propriétés du ticket</h4>
        <div class="">
            <?php
            if($ticket->isOpen()) { ?>
                <tr>
                    <th class="col-md-6"><?php echo __('Assigned To');?>:</th>
                    <td class="col-md-6">
                        <?php
                        if($ticket->isAssigned())
                            echo Format::htmlchars(implode('/', $ticket->getAssignees()));
                        else
                            echo '<span class="faded">&mdash; '.__('Unassigned').' &mdash;</span>';
                        ?>
                    </td>
                </tr>
                <?php
                } else { ?>
                <div class="col-md-6 ticketinfo">
                    <h4 style="width:100%"><b><?php echo __('Closed By');?>:</b></h4>
                    <span>
                        <?php
                        if(($staff = $ticket->getStaff()))
                            echo Format::htmlchars($staff->getName());
                        else
                            echo '<span class="faded">&mdash; '.__('Unknown').' &mdash;</span>';
                        ?>
                    </span>
                </div>
                <?php
                } ?>
                <div class="col-md-6 ticketinfo">
                    <h4 style="width:100%"><b><?php echo __('SLA Plan');?>:</b></h4>
                    <span><?php echo $sla?Format::htmlchars($sla->getName()):'<span class="faded">&mdash; '.__('None').' &mdash;</span>'; ?></span>
                </div>
                <div class="col-md-6 ticketinfo">
                    <h4 style="width:100%"><b><?php echo __('Last Message');?>:</b></h4>
                    <span><?php echo Format::datetime($ticket->getLastMsgDate()); ?></span>
                </div>
                <?php
                    if(!is_null($ticket->getLastRespDate())){
                        echo '<div class="col-md-6 ticketinfo">';
                        echo '<h4 style="width:100%"><b>' . __('Last Response') . ':</b></h4>';
                        echo '<span>' . Format::datetime($ticket->getLastRespDate()) . '</span>';
                        echo '</div>';
                }?>
                <div class="col-md-6 ticketinfo">
                    <h4 style="width:100%"><b>Status :</b></h4>
                        <?php // Status change options
                            echo TicketStatus::status_options();
                        ?>
                </div>
        </div>
</div>
<?php
foreach (DynamicFormEntry::forTicket($ticket->getId()) as $form) {
    // Skip core fields shown earlier in the ticket view
    // TODO: Rewrite getAnswers() so that one could write
    //       ->getAnswers()->filter(not(array('field__name__in'=>
    //           array('email', ...))));
    $answers = $form->getAnswers()->exclude(Q::any(array(
        'field__flags__hasbit' => DynamicFormField::FLAG_EXT_STORED,
        'field__name__in' => array('subject', 'priority')
    )));
    $displayed = array();
    foreach($answers as $a) {
        if (!($v = $a->display()))
            continue;
        $displayed[] = array($a->getLocal('label'), $v);
    }
    if (count($displayed) == 0)
        continue;
    ?>
    <!--<div class="ticket_info">
    <table cellspacing="0" cellpadding="0" width="100%" border="0">
    <tbody>
<?php

    foreach ($displayed as $stuff) {
        list($label, $v) = $stuff;
?>
        <tr>
            <th class="col-md-6"><?php
echo Format::htmlchars($label);
            ?>:</th>
            <td class="col-md-6"><?php
echo $v;
            ?></td>
        </tr>
<?php } ?>
    </tbody>
    </table>
</div>-->
</div>
</div>
<?php } ?>

<?php
$tcount = $ticket->getThreadEntries($types)->count();
?>
</div>
<div style="display:none;" class="dialog" id="print-options">
    <h3><?php echo __('Ticket Print Options');?></h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <form action="tickets.php?id=<?php echo $ticket->getId(); ?>"
        method="post" id="print-form" name="print-form" target="_blank">
        <?php csrf_token(); ?>
        <input type="hidden" name="a" value="print">
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <fieldset class="notes">
            <label class="fixed-size" for="notes"><?php echo __('Print Notes');?>:</label>
            <label class="inline checkbox">
            <input type="checkbox" id="notes" name="notes" value="1"> <?php echo __('Print <b>Internal</b> Notes/Comments');?>
            </label>
        </fieldset>
        <fieldset>
            <label class="fixed-size" for="psize"><?php echo __('Paper Size');?>:</label>
            <select id="psize" name="psize">
                <option value="">&mdash; <?php echo __('Select Print Paper Size');?> &mdash;</option>
                <?php
                  $psize =$_SESSION['PAPER_SIZE']?$_SESSION['PAPER_SIZE']:$thisstaff->getDefaultPaperSize();
                  foreach(Export::$paper_sizes as $v) {
                      echo sprintf('<option value="%s" %s>%s</option>',
                                $v,($psize==$v)?'selected="selected"':'', __($v));
                  }
                ?>
            </select>
        </fieldset>
        <hr style="margin-top:3em"/>
        <p class="full-width">
            <span class="buttons pull-left">
                <input type="reset" value="<?php echo __('Reset');?>">
                <input type="button" value="<?php echo __('Cancel');?>" class="close">
            </span>
            <span class="buttons pull-right">
                <input type="submit" value="<?php echo __('Print');?>">
            </span>
         </p>
    </form>
    <div class="clear"></div>
</div>
<div style="display:none;" class="dialog" id="confirm-action">
    <h3><?php echo __('Please Confirm');?></h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <p class="confirm-action" style="display:none;" id="claim-confirm">
        <?php echo __('Are you sure you want to <b>claim</b> (self assign) this ticket?');?>
    </p>
    <p class="confirm-action" style="display:none;" id="answered-confirm">
        <?php echo __('Are you sure you want to flag the ticket as <b>answered</b>?');?>
    </p>
    <p class="confirm-action" style="display:none;" id="unanswered-confirm">
        <?php echo __('Are you sure you want to flag the ticket as <b>unanswered</b>?');?>
    </p>
    <p class="confirm-action" style="display:none;" id="overdue-confirm">
        <?php echo __('Are you sure you want to flag the ticket as <font color="red"><b>overdue</b></font>?');?>
    </p>
    <p class="confirm-action" style="display:none;" id="banemail-confirm">
        <?php echo sprintf(__('Are you sure you want to <b>ban</b> %s?'), $ticket->getEmail());?> <br><br>
        <?php echo __('New tickets from the email address will be automatically rejected.');?>
    </p>
    <p class="confirm-action" style="display:none;" id="unbanemail-confirm">
        <?php echo sprintf(__('Are you sure you want to <b>remove</b> %s from ban list?'), $ticket->getEmail()); ?>
    </p>
    <p class="confirm-action" style="display:none;" id="release-confirm">
        <?php echo sprintf(__('Are you sure you want to <b>unassign</b> ticket from <b>%s</b>?'), $ticket->getAssigned()); ?>
    </p>
    <p class="confirm-action" style="display:none;" id="changeuser-confirm">
        <span id="msg_warning" style="display:block;vertical-align:top">
        <?php echo sprintf(Format::htmlchars(__('%s <%s> will longer have access to the ticket')),
            '<b>'.Format::htmlchars($ticket->getName()).'</b>', Format::htmlchars($ticket->getEmail())); ?>
        </span>
        <?php echo sprintf(__('Are you sure you want to <b>change</b> ticket owner to %s?'),
            '<b><span id="newuser">this guy</span></b>'); ?>
    </p>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font color="red"><strong><?php echo sprintf(
            __('Are you sure you want to DELETE %s?'), __('this ticket'));?></strong></font>
        <br><br><?php echo __('Deleted data CANNOT be recovered, including any associated attachments.');?>
    </p>
    <div><?php echo __('Please confirm to continue.');?></div>
    <form action="tickets.php?id=<?php echo $ticket->getId(); ?>" method="post" id="confirm-form" name="confirm-form">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="a" value="process">
        <input type="hidden" name="do" id="action" value="">
        <hr style="margin-top:1em"/>
        <p class="full-width">
            <span class="buttons pull-left">
                <input type="button" value="<?php echo __('Cancel');?>" class="close">
            </span>
            <span class="buttons pull-right">
                <input type="submit" value="<?php echo __('OK');?>">
            </span>
         </p>
    </form>
    <div class="clear"></div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/1.5.3/signature_pad.min.js"></script>
<script src="./js/moment.js"></script>
<script src="./js/rapport.js"></script>
<script src="./js/atelier.js"></script>
<script src="../js/autosize.js"></script>
<script src="./js/docxtemplater.js"></script>
<script type="text/javascript" src="./js/jszip.js"></script>
<script type="text/javascript" src="./js/jszip-utils.js"></script>
<script type="text/javascript" src="./js/file-saver.min.js"></script>
<script src="./js/pdf.js"></script>
<script src="./js/pdf.worker.js"></script>

<script type="text/javascript">
$(function() {
    $(document).on('click', 'a.change-user', function(e) {
        e.preventDefault();
        var tid = <?php echo $ticket->getOwnerId(); ?>;
        var cid = <?php echo $ticket->getOwnerId(); ?>;
        var url = 'ajax.php/'+$(this).attr('href').substr(1);
        $.userLookup(url, function(user) {
            if(cid!=user.id
                    && $('.dialog#confirm-action #changeuser-confirm').length) {
                $('#newuser').html(user.name +' &lt;'+user.email+'&gt;');
                $('.dialog#confirm-action #action').val('changeuser');
                $('#confirm-form').append('<input type=hidden name=user_id value='+user.id+' />');
                $('#overlay').show();
                $('.dialog#confirm-action .confirm-action').hide();
                $('.dialog#confirm-action p#changeuser-confirm')
                .show()
                .parent('div').show().trigger('click');
            }
        });
    });

    // Post Reply or Note action buttons.
    $('a.post-response').click(function (e) {
        var $r = $('ul.tabs > li > a'+$(this).attr('href')+'-tab');
        if ($r.length) {
            // Make sure ticket thread tab is visiable.
            var $t = $('ul#ticket_tabs > li > a#ticket-thread-tab');
            if ($t.length && !$t.hasClass('active'))
                $t.trigger('click');
            // Make the target response tab active.
            if (!$r.hasClass('active'))
                $r.trigger('click');

            // Scroll to the response section.
            var $stop = $(document).height();
            var $s = $('div#response_options');
            if ($s.length)
                $stop = $s.offset().top-125

            $('html, body').animate({scrollTop: $stop}, 'fast');
        }

        return false;
    });

});
</script>


<script>

    $(document).ready(function(){

        //Récupération asynchrone des données de l'organisation
        $.ajax({
          type:'GET',
          url:'ajaxs.php/org/find/'+<?= json_encode($orgName); ?>
        }).success(function(data){
          data = $.parseJSON(data);
          data = data[0].data;
          console.log(data);
          $('#contact').val(data[1] + " " + data[2] + "\n" + data[3] + " " + data[4]);
        });

        var user = <?php echo $user->getId(); ?>;
        /*$(document).on('click','.rapports tbody tr',function(){
            var id = $(this).attr('id');
            $('.col-md-4.rapport').removeClass('active');
            $('#'+id+'.col-md-4.rapport').addClass('active');
            $('.rapports tbody tr').removeClass('active');
            $(this).addClass('active');
            $('.eachRapport.active').removeClass('active');
            $('#'+id+'.eachRapport').addClass('active');
        });*/

        //INIT DATEPIKCER
         $('input[name="date_new_inter"]').datepicker({
                            startView: 1,
                            dateFormat: 'dd/mm/yy',
                            autoclose: true
                        });
        $('input[name="date_inter"]').datepicker({
                            startView: 1,
                            dateFormat: 'dd/mm/yy',
                            autoclose: true
                });

        //Afficher le nouveau rapport
        $('.addRapport').click(function(){
            //$(this).addClass('animated fadeOutDown');
            $(this).css('display','none');
            $('#rapport').addClass('animated fadeInDown');
            $('#rapport').css('display','block');
        });

        //Afficher le bouton nouveau rapport
        $('.cancel.pending.newRapport').click(function(){
            $('.addRapport').css('display','block');
            $('.addRapport').addClass('animated fadeInUp');
            $(this).parent().parent().css('display','none');
        });

        $(window).resize(function(){
            if($(this).width() <= 992){
                $(".ticket_right").css('height','auto');
            }
            /*if($('.fixed-right').css('position') == "fixed"){
                $('.fixed-right').css('height',$('.ticket_left').height());
            } else {
                $('.fixed-right').css('height','auto');
            }*/
        });

        var timeout;
        $(window).scroll(function() {
            //clearTimeout(timeout);
            timeout = setTimeout(function() {
                if($('.fixed-right').css('display') != 'none' && $(document).width() > 974){
                $('.ticket_right').height($('.ticket_left').height());

                var tlHeight = $('.ticket_left').offset().top + $('.ticket_left').height();
                var trHeight = $(this).scrollTop() + $('.fixed-right').height() + 90;

                /*if(trHeight >= tlHeight){
                    $('.fixed-right').css('position','absolute');
                    $('.fixed-right').css('bottom','30px');
                    $('.fixed-right').css('top','135px');
                    $('.fixed-right').css('width','100%');
                    $('.fixed-right').css('height','auto');
                } else */if($(document).scrollTop() > 208 ){
                    $('.fixed-right').css('position','fixed');
                    $('.fixed-right').css('top','135px');
                    $('.fixed-right').css('bottom','30px');
                    $('.fixed-right').css('width','20%');
                    $('.fixed-right').css('height','auto');
                } else if($('.fixed-right').css('position') != "relative"){
                    $('.fixed-right').css('position','relative');
                    $('.fixed-right').css('top','initial');
                    $('.fixed-right').css('bottom','initial');
                    $('.fixed-right').css('width','100%');
                    $('.fixed-right').css('height','auto');
                }
            }
          }, 10);
        });

        //$(".fixed-right").stick_in_parent();

        var clicky;

    $(document).mousedown(function(e) {
        // The latest element clicked
        clicky = $(e.target);
    });

    $(document).on('focusout','.user_org',function(e){
        if(!$(clicky).is('p')){
            $(".orgsList").css('display','none');
            //$("tr td:contains('Organisation:')").siblings().find('input').focus();
        } else {
            $(".user_org").focus();
        }
    });

    $(document).on('focusin','.user_org',function(e){
        if($('.user_org').val().length > 0){
            var orgInput = $(this);
            var pos = orgInput.position();
            var top = pos.top + 27;
            var left = pos.left;
            $(".orgsList").css('top',top);
            $(".orgsList").css('left',left);
            $(".orgsList").css('width','auto');
            $(".orgsList").css('display','block');
        }
    });

    (function ($) {
        $.fn.delayKeyup = function(callback, ms){
            var timer = 0;
            $(this).keyup(function(){
                clearTimeout (timer);
                timer = setTimeout(callback, ms);
            });
            return $(this);
        };
    })(jQuery);

    $('.user_org').delayKeyup(function(){
        //alert("5 secondes passed from the last event keyup.");
            var orgInput = $('.user_org');
            var pos = orgInput.position();
            var top = pos.top + 27;
            var left = pos.left;

            if(orgInput.val().length > 0){
                $.ajax({
                    method: "GET",
                    url: "./ajaxs.php/orgs/"+orgInput.val()
                })
                .success(function( data ) {
                    data = $.parseJSON(data);
                    $(".orgsList").empty();
                    $(".orgsList").css('top',top);
                    $(".orgsList").css('left',left);
                    $(".orgsList").css('width','auto');
                    $(data).each(function(number,obj){
                        $(".orgsList").append('<p data-org-name="" id="'+obj.data[0]+'">'+obj.data[0]+'</p>')
                    });
                    $(".orgsList").css('display','block');
                });
            } else {
                $(".orgsList").css('display','none');
            }
    }, 500);

    //temporisation

    $(document).on('click','.orgsList p',function(){
        $(".user_org").val($(this).text());
        //var org = $(this).attr('id');
        $.ajax({
                method: "POST",
                url: "./ajax.php/users/"+user+"/org",
                data: {
                    orgId:$(this).attr('id'),
                    orgName:$(this).text()
                }
        }).success(function( data ) {

        });
    });


    //SIGNATURE
    //printRapport

    //GESTION DU SNIPPER
    $(document).on('click','.number-spinner button',function () {
        btn = $(this);
        input = btn.closest('.number-spinner').find('input');
        btn.closest('.number-spinner').find('button').prop("disabled", false);

        if (btn.attr('data-dir') == 'up') {
            if ( input.attr('max') == undefined || parseInt(input.val()) < parseInt(input.attr('max')) ) {
                input.val(parseInt(input.val())+1);
            }
        } else {
            if ( input.attr('min') == undefined || parseInt(input.val()) > parseInt(input.attr('min')) ) {
                    input.val(parseInt(input.val())-1);
            }
        }
    });

});

</script>
