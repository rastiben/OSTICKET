<?php
global $cfg;


$info['title'] = "Ajout d'un contrat";

if (!$_POST) {
    $info['sendemail'] = true; // send email confirmation.
}

?>
<h3 class="drag-handle"><?php echo $info['title']; ?></h3>
<b><a class="close" href="#"><i class="icon-remove-circle"></i></a></b>
<div class="clear"></div>

<div id="contrat-add" style="display:block; margin:5px;">
    <form method="post" class="user" action="#contrats/add">

      <div class="form-group row col-md-12">
        <label for="code" class="col-sm-2 col-form-label">Code</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" name="code" id="code" placeholder="Code" value="toto">
        </div>
      </div>

      <div class="form-group row col-md-12">
        <label for="org" class="col-sm-2 col-form-label">Client</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="org" name="org" placeholder="Client" value="toto">
        </div>
      </div>

      <div class="form-group row col-md-12">
        <label for="etat" class="col-sm-2 col-form-label">Etat</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="etat" name="etat" placeholder="Etat" value="toto">
        </div>
      </div>

      <div class="form-group row col-md-12">
        <label for="debut" class="col-sm-2 col-form-label">Date de début</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="debut" name="debut" placeholder="Date de début" value="15/05/2017">
        </div>
      </div>

      <div class="form-group row col-md-12">
        <label for="fin" class="col-sm-2 col-form-label">Date de fin</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="fin" name="fin" placeholder="Date de fin" value="15/05/2017">
        </div>
      </div>

      <div class="form-group row col-md-12">
        <label for="type" class="col-sm-2 col-form-label">Type de contrat</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="type" name="type" placeholder="Type de contrat" value="toto">
        </div>
      </div>

      <div class="form-group row col-md-12">
        <label for="created" class="col-sm-2 col-form-label">Date d'établissement</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="created" name="created" placeholder="Date d'établissement" value="15/05/2017">
        </div>
      </div>

      <input type="submit" name"submit"/>
    </form>
</div>
<div class="clear"></div>
<script type="text/javascript">
$(function() {
    $(document).on('click', 'input#sendemail', function(e) {
        if ($(this).prop('checked'))
            $('tbody#password').hide();
        else
            $('tbody#password').show();
    });
});
</script>
