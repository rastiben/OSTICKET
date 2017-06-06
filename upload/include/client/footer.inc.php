</div>
    <div id="footer">
        <!--<p>Copyright &copy; <?php echo date('Y'); ?> <?php echo (string) $ost->company ?: 'osTicket.com'; ?> - All rights reserved.</p>-->
        <p>150 rue des Hauts de la Chaume – 86280 SAINT BENOIT – Tél. 05 49 30 31 31 – Fax 05 49 53 69 53<br>
Internet : http://www.viennedoc.com – E-mail : vdoc@viennedoc.com<br>
SAS au capital de 75 000€ - Siret 382 940 054 00051 – APE 4651Z – TVA Intracommunautaire FR 92 382 940 054
</p>
        <!--<a id="poweredBy" href="http://osticket.com" target="_blank"><?php echo __('Helpdesk software - powered by osTicket'); ?></a>-->
        <a href="https://www.facebook.com/Viennedoc" target="_blank" <i class="icon-facebook-sign icon-2x" style="color:#3b5998" aria-hidden="true"></i></a>
        <a href="https://twitter.com/vdocumentique" target="_blank" <i class="icon-twitter-sign icon-2x" style="margin-left:15px;color:#55acee" aria-hidden="true"></i></a>
</div>
</div>
<div id="overlay"></div>
<div id="loading">
    <h4><?php echo __('Please Wait!');?></h4>
    <p><?php echo __('Please wait... it will take a second!');?></p>
</div>
<?php
if (($lang = Internationalization::getCurrentLanguage()) && $lang != 'en_US') { ?>
    <script type="text/javascript" src="ajax.php/i18n/<?php
        echo $lang; ?>/js"></script>
<?php } ?>
<script type="text/javascript">
    getConfig().resolve(<?php
        include INCLUDE_DIR . 'ajax.config.php';
        $api = new ConfigAjaxAPI();
        print $api->client(false);
    ?>);
</script>
</body>
</html>
