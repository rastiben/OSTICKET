<?php
$BUTTONS = isset($BUTTONS) ? $BUTTONS : true;
?>
    <div class="col-md-12" id="homeClient">
<?php if ($BUTTONS) { ?>
<?php
    if ($cfg->getClientRegistrationMode() != 'disabled'
        || !$cfg->isClientLoginRequired()) { ?>
            <div class="col-md-4 col-md-offset-2" id="newTicket">
            <img id="icon" src="./assets/default/images/plus.png">
            <h3 id="titleNewTicket">Nouveau Ticket</h3>
            <hr>
            <p id="textNewTicket">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam faucibus dolor at neque iaculis ultricies. Nulla egestas ex ac pharetra iaculis. Phasellus nec massa nec est faucibus auctor.</p>
            <a href="open.php" style="display:block" class="blue button"><?php
                echo __('Open a New Ticket');?></a>
            </div>
<?php } ?>
           <div class="col-md-4" id="checkTicket">
            <img id="icon" src="./assets/default/images/check.png">
            <h3 id="titleCheckTicket">Vérifier le statut d'un Ticket</h3>
            <hr>
            <p id="textCheckTicket">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam faucibus dolor at neque iaculis ultricies. Nulla egestas ex ac pharetra iaculis. Phasellus nec massa nec est faucibus auctor.</p>
            <a href="view.php" style="display:block" class="green button"><?php
                echo __('Check Ticket Status');?></a>
        </div>
<?php } ?>
        <div class="content"><?php
    $faqs = FAQ::getFeatured()->select_related('category')->limit(5);
    if ($faqs->all()) { ?>
            <section><div class="header"><?php echo __('Featured Questions'); ?></div>
<?php   foreach ($faqs as $F) { ?>
            <div><a href="<?php echo ROOT_PATH; ?>kb/faq.php?id=<?php
                echo urlencode($F->getId());
                ?>"><?php echo $F->getLocalQuestion(); ?></a></div>
<?php   } ?>
            </section>
<?php
    }
    $resources = Page::getActivePages()->filter(array('type'=>'other'));
    if ($resources->all()) { ?>
            <section><div class="header"><?php echo __('Other Resources'); ?></div>
<?php   foreach ($resources as $page) { ?>
            <div><a href="<?php echo ROOT_PATH; ?>pages/<?php echo $page->getNameAsSlug();
            ?>"><?php echo $page->getLocalName(); ?></a></div>
<?php   } ?>
            </section>
<?php
    }
        ?></div>
    </div>

