<html>

<head>
   <?php include_once(SCP_DIR . '/Request/Rapport.php') ?>
    <style type="text/css">
        @page {
            header: html_def;
            footer: html_def;
            margin: 10mm;
            margin-top: 5mm;
            margin-bottom: 22mm;
        }

        .round{
            border:0.1mm solid #220044;
            background-color: #f0f2ff;
            background-gradient: linear #c7cdde #f0f2ff 0 1 0 0.5;
            border-top-left-radius: 4em;
            background-clip: border-box;
        }

        .signature{
            float:right;
            width: 300px;
            height: 150px;
            border: 1px solid black;
            margin-top: 15px;
            padding-left: 15px;
        }

<?php include ROOT_DIR . 'css/thread.css';?>

    </style>
</head>
<body>

<?php

    $rapport = Rapport::getInstance()->getRapport($_GET['idR']);
    //$horaires = Rapport::getInstance()->getRapportsHoraires($_GET['idR']);

    //SIGNATURE
    //$img = (array)json_decode(trim(file_get_contents('php://input')))['img'];

?>

<!--<htmlpageheader name="def" style="display:none">
<!--<?php if ($logo = $cfg->getClientLogo()) { ?>
    <img src="cid:<?php echo $logo->getKey(); ?>" class="logo"/>
<?php } else { ?>
    <img src="<?php echo INCLUDE_DIR . 'fpdf/print-logo.png'; ?>" class="logo"/>
<?php } ?>
    <div class="hr">&nbsp;</div>
    <table><tr>
        <td class="flush-left"><?php echo (string) $ost->company; ?></td>
        <td class="flush-right"><?php echo Format::daydatetime(Misc::gmtime()); ?></td>
    </tr></table>
</htmlpageheader>-->

<htmlpagefooter name="def">
  <div class="signature"><h5>Cachet et signature du client le</h5>
    <img src="<?= $img ?>"></img>
  </div>
</htmlpagefooter>

<!-- Ticket metadata -->
<!--<h1>Ticket #<?php echo $ticket->getNumber(); ?></h1>-->
<table style="border-collapse: collapse;border-spacing: 0;border:1px solid black;margin-bottom:15px" width="100%">
  <tbody>
    <tr>
		<td colspan="1"><img height="100" src="<?php echo INCLUDE_DIR . 'fpdf/logo_OK.jpg'; ?>" class="logo"/></td>
		<td colspan="2" style="border-right:1px solid black;">VIENNE DOCUMENTIQUE S.A.S<br>
		150 avenue des Hauts de la Chaume<br>
		86280 SAINT BENOIT<br>
		Téléphone : 05.49.30.31.31<br>
		Télécopie : 05.49.53.69.53<br>
		Email:gclemenceau@viennedoc.com<br>
		Site internet: http://www.viennedoc.com</td>
		<td colspan="3"><span>
		    APE Etiquettes<br>
		    12 Avenue de l'Europe<br>
		    86170 Neuville-de-Poitou
		</span></td>
    </tr>
    <tr style="text-align:center">
       <th colspan="6" style="text-align:center;border-top:1px solid black;border-bottom:1px solid black" height="30">
            RAPPORT D'INTERVENTION N° <?php echo $rapport[0]['id'] ?>
        </th>
    </tr>
    <tr style="text-align:center">
       <th style="text-align:center;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;" height="20">
            N° DU TICKET
        </th>
        <th style="text-align:center;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;">
            Date d'appel
        </th>
        <th style="text-align:center;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;">
            Heure
        </th>
        <th style="text-align:center;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;">
            Intervenant
        </th>
        <th style="text-align:center;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;">
            N° de contrat
        </th>
        <th style="text-align:center;border-bottom:1px solid black;border-left:1px solid black;border-right:1px solid black;">
            Code site
        </th>
    </tr>
    <tr>
        <td style="text-align:center;border-left:1px solid black;border-right:1px solid black;">
        <?php echo $ticket->getNumber(); ?>
        </td>
        <td style="text-align:center;border-left:1px solid black;border-right:1px solid black;">
                   <?php $createDate = new DateTime($ticket->getCreateDate());
                    echo $createDate->format('d/m/Y'); ?>
                    </td>
        <td style="text-align:center;border-left:1px solid black;border-right:1px solid black;">
          <?php echo $createDate->format('H:i'); ?>
        </td>
        <td style="text-align:center;border-left:1px solid black;border-right:1px solid black;">
        <?php echo  $rapport[0]['firstname'] . ' ' . $rapport[0]['lastname']; ?>
        </td>
        <td style="text-align:center;border-left:1px solid black;border-right:1px solid black;">

        </td>
        <td style="text-align:center;border-left:1px solid black;border-right:1px solid black;">

        </td>
    </tr>
    <tr>
        <th colspan="1" style="text-align:center;border-top:1px solid black;border-right:1px solid black;" >Demande de</th>
        <th colspan="5" style="text-align:center;border-top:1px solid black;border-left:1px solid black;">Sujet</th>
    </tr>
    <tr>
        <td colspan="1" style="text-align:center;border-top:1px solid black;border-right:1px solid black;">
            <?= ucfirst($ticket->getUser()->getName()) . ' ' . ucfirst($ticket->getUser()->getFirstName()); ?>
        </td>
        <td colspan="5" style="text-align:center;border-top:1px solid black;border-left:1px solid black;"><?php echo $ticket->getSubject(); ?>
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>
