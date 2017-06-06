<?php
    require_once('./Request/Tickets.php');
    require_once('./Request/Atelier.php');

    //$orgsC = OrganisationCollection::getInstance();

?>

    <div class="balls">
        <div class="ball"></div>
        <div class="ball1"></div>
    </div>
    <div class="plan col-md-12">
        <h1 class="col-md-12">Plan de l'atelier : </h1>
        <div class="atelier col-md-9">
            <div class="img">
                <div class="bureau" id="un" data_planche="b1"></div>
                <div class="bureau" id="deux" data_planche="b2"></div>
                <div class="bureau" id="trois" data_planche="b3"></div>
                <div class="portable" id="un" data_planche="p1"></div>
                <div class="portable" id="deux" data_planche="p2"></div>
                <div class="portable" id="trois" data_planche="p3"></div>
                <div class="mur" id="un" data_planche="m1"></div>
                <div class="mur" id="deux" data_planche="m2"></div>
                <div class="mur" id="trois" data_planche="m3"></div>
                <div class="mur" id="quatre" data_planche="m4"></div>
                <div class="serveur" id="un" data_planche="s1"></div>
                <div class="serveur" id="deux" data_planche="s2"></div>
                <div class="serveur" id="trois" data_planche="s3"></div>
                <div class="serveur" id="quatre" data_planche="s4"></div>
                <div class="serveur" id="cinq" data_planche="s5"></div>
                <div class="serveur" id="six" data_planche="s6"></div>
                <img src="../assets/atelier/atelier.png"/>
            </div>
        </div>
        <div class="enCours col-md-3">
            <h2>En cours</h2>

            <div class="b1"><div class="color"></div><h4>B1 : </h4></div>
            <div class="b2"><div class="color"></div><h4>B2 : </h4></div>
            <div class="b3"><div class="color"></div><h4>B3 : </h4></div>
            <div class="p1"><div class="color"></div><h4>P1 : </h4></div>
            <div class="p2"><div class="color"></div><h4>P2 : </h4></div>
            <div class="p3"><div class="color"></div><h4>P3 : </h4></div>
            <div class="m1"><div class="color"></div><h4>M1 : </h4></div>
            <div class="m2"><div class="color"></div><h4>M2 : </h4></div>
            <div class="m3"><div class="color"></div><h4>M3 : </h4></div>
            <div class="m4"><div class="color"></div><h4>M4 : </h4></div>
            <div class="s1"><div class="color"></div><h4>S1 : </h4></div>
            <div class="s2"><div class="color"></div><h4>S2 : </h4></div>
            <div class="s3"><div class="color"></div><h4>S3 : </h4></div>
            <div class="s4"><div class="color"></div><h4>S4 : </h4></div>
            <div class="s5"><div class="color"></div><h4>S5 : </h4></div>
            <div class="s6"><div class="color"></div><h4>S6 : </h4></div>
        </div>

            <div class="modal fade" id="fichesModal" data_planche="" data_id_contenu="" data_staff="<?php echo $thisstaff->getId() ?>">
              <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <!--<button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>-->
                  </div>
                  <div class="modal-body">
                    <div class="container home">
                       <div class="col-md-12">
                        <table class="list atelierT" border="0" cellspacing="1" cellpadding="2" width="100%">
                            <thead>
                                <th>Ticket</th>
                                <th>Organisation</th>
                                <th>Type</th>
                                <th>Etat</th>
                                <th>Affecter</th>
                                <th>Supprimer</th>
                             </thead>
                             <tbody>

                            </tbody>
                            <tfoot>
                            </tfoot>
                        </table>
                      </div>
                    </div>
                    <div class="container fiche">
                        <div class="retour title">
                            <h3></h3>
                            <select class="custom-select changeState">
                               <option>Entrées</option>
                               <option>Planche</option>
                               <option>Sorties</option>
                               <option>RMA</option>
                           </select>
                        </div>
                        <div class="repaTmpl" style="display:none">

                           <div class="col-md-12">
                                <div class="inputField col-md-6">
                                    <input id="marque" required>
                                    <label for="marque">Marque</label>
                                </div>

                                <div class="inputField col-md-6">
                                    <input id="model" required>
                                    <label for="model">Modèle</label>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="inputField col-md-6">
                                    <input id="sn" required>
                                    <label for="sn">Numéro de série</label>
                                </div>
                                <div class="inputField col-md-6">
                                    <input id="vd" required>
                                    <label for="vd">Numéro de VD</label>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="inputField col-md-6">
                                    <input id="os" required>
                                    <label for="os">Système d'exploitation</label>
                                </div>

                                <div class="inputField col-md-6">
                                    <input id="motDePasse" required>
                                    <label for="motDePasse">Mot de passe</label>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="inputField col-md-6">
                                    <input id="login" required>
                                    <label for="login">Login</label>
                                </div>
                                <div class="inputField col-md-6">
                                    <input id="office" required>
                                    <label for="office">Office</label>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="inputField col-md-12">
                                    <textarea id="autreSoft" required></textarea>
                                    <label for="autreSoft">Autres Soft</label>
                                </div>
                            </div>

                        </div>

                   <div class="prepaTmpl" style="display:none">
                            <div class="col-md-12 VD">
                                <div class="col-md-12" id="nomDuPoste">
                                    <!--<input id="nomDuPoste" required>
                                    <label for="nomDuPoste">Nom du poste</label>-->
                                </div>
                                <div class="col-md-12">
                                    <div class="inputField col-md-12">
                                        <input type="text" id="client" required>
                                        <label for="client">Client</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="inputField col-md-6">
                                        <input type="text" id="denomination" required>
                                        <label for="denomination">Type</label>
                                    </div>
                                    <div class="inputField col-md-6">
                                        <input type="text" id="numeroSerie" required>
                                        <label for="numeroSerie">Numero de serie</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="inputField col-md-6">
                                        <input type="text" id="versionWindows" required>
                                        <label for="versionWindows">Version de windows</label>
                                    </div>
                                    <div class="inputField col-md-6">
                                        <input type="text" id="numLicenceW" required>
                                        <label for="numLicenceW">Numero de licence Windows</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="inputField col-md-6">
                                        <input type="text" id="versionOffice" required>
                                        <label for="versionOffice">Version d'office</label>
                                    </div>
                                    <div class="inputField col-md-6">
                                        <input type="text" id="numLicenceO" required>
                                        <label for="numLicenceO">Numero de licence Office</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="inputField col-md-6">
                                        <input type="text" id="garantie" required>
                                        <label for="garantie">Garantie</label>
                                    </div>
                                    <div class="inputField col-md-6">
                                        <input type="text" id="debutGarantie" required>
                                        <label for="debutGarantie">Debut de la garantie</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="inputField col-md-6">
                                        <input type="text" id="mail" required>
                                        <label for="mail">Mail</label>
                                    </div>
                                    <div class="inputField col-md-6">
                                        <input type="text" id="mdpMail" required>
                                        <label for="mdpMail">Mot de passe</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="inputField col-md-12">
                                    <textarea id="modele" required></textarea>
                                    <label for="modele">Modèle</label>
                                </div>
                            </div>

                            <div class="col-md-12">
                               <div class="checkboxField col-md-6">
                                    <input type="checkbox" id="etiquetage" required>
                                    <label for="etiquetage">Etiquetage du poste</label>
                                </div>
                                <div class="checkboxField col-md-6">
                                    <input type="checkbox" id="dossierSAV" required>
                                    <label for="dossierSAV">Création dossier savvdoc</label>
                                </div>
                            </div>



                            <div class="col-md-12">
                               <div class="checkboxField col-md-4">
                                    <input type="checkbox" id="septZip" required>
                                    <label for="septZip">7-zip</label>
                                </div>
                                <div class="checkboxField col-md-4">
                                    <input type="checkbox" id="acrobat" required>
                                    <label for="acrobat">Acrobat Reader</label>
                                </div>
                                <div class="checkboxField col-md-4">
                                    <input type="checkbox" id="flash" required>
                                    <label for="flash">Flash Player</label>
                                </div>
                            </div>

                            <div class="col-md-12">
                               <div class="checkboxField col-md-4">
                                    <input type="checkbox" id="java" required>
                                    <label for="java">Java</label>
                                </div>
                                <div class="checkboxField col-md-4">
                                    <input type="checkbox" id="pdf" required>
                                    <label for="pdf">PDF Creator</label>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="inputField col-md-12">
                                    <textarea id="autre" required></textarea>
                                    <label for="autre">Autre</label>
                                </div>
                            </div>




                            <div class="col-md-12">
                                <div class="inputField col-md-4">
                                    <input id="type" required>
                                    <label for="type">Type</label>
                                </div>
                                <div class="inputField col-md-4">
                                    <input id="userAccount" required>
                                    <label for="userAccount">Compte utilisateur créé</label>
                                </div>
                                <div class="inputField col-md-4">
                                    <input id="mdp" required>
                                    <label for="mdp">Mot de passe</label>
                                </div>
                                <div class="checkboxField col-md-12">
                                    <input type="checkbox" id="activation" required>
                                    <label for="activation">Activation</label>
                                </div>
                            </div>




                            <div class="col-md-12">
                                <div class="checkboxField col-md-4">
                                    <input type="checkbox" id="uninstall" required>
                                    <label for="uninstall">Désinstallation antivirus préinstallé</label>
                                </div>

                                <div class="checkboxField col-md-4">
                                    <input type="checkbox" id="maj" required>
                                    <label for="maj">M à J Windows et autres produits</label>
                                </div>
                                <div class="checkboxField col-md-4">
                                    <input type="checkbox" id="register" required>
                                    <label for="register">Enregistrement du produit</label>
                                </div>
                                <div class="checkboxField col-md-4">
                                    <input type="checkbox" id="verifActivation" required>
                                    <label for="verifActivation">Vérification activation windows</label>
                                </div>
                            </div>



                            <div class="col-md-12">
                                <div class="inputField col-md-12">
                                    <textarea id="divers" required></textarea>
                                    <label for="divers">Divers</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                  <div class="modal-footer">
                    <!--<button type="button" class="btn btn-primary"></button>-->
                    <button type="button" class="btn btn-secondary validerOuEnregistrer">Valider</button>
                    <button type="button" class="btn btn-secondary validerOuEnregistrer" style="display:none">Retour</button>
                  </div>
                </div>
              </div>
            </div>
        </div>


      <script type="text/javascript">

        //$(function() {

            var planches = new Planche();

            $(document).ajaxStop(function() {
                $('.balls').css('display','none');
                planches.majEnCours();
            });

            //Initiate
            /*$(document).off('click', '.atelier div');
            $(document).off('click', '.addContenu');
            $(document).off('click', '.contenu img');
            $(document).off('click', '.validerOuEnregistrer');
            $(document).off('hidden.bs.modal', '.modal');*/

            //Gestion de l'atelier
            $(document).on('click', '.atelier div div', function(e) {
                var planche = $(this);

                planches.getContenues(function(contenues){
                    /*INIT*/
                    $('.modal-body .container.home').show();
                    $('.modal-body .container.fiche').hide();

                    $('.modal-title').text((planche.attr('class') + ' ' + planche.attr('id')).replace(/\b[a-z]/g,function(f){return f.toUpperCase();}));
                    $('#fichesModal').attr('data_planche',planche.attr('data_planche'));


                    var contenu = planches.getPlanche(planche.attr('data_planche'));
                    $('.modal-body .contenu').remove();

                    $(contenu).each(function(number,obj){
                            addContenuInPlanche(obj.getId(),obj);
                    });

                    $('#fichesModal').modal({backdrop: 'static', keyboard: false});

                    $('.list.atelierT tbody').empty();
                    $(contenues).each(function(number,obj){
                        addContenuInListe(obj);
                    });
                });
            });

            //Ajouter une prepa/repa
            $(document).on('click','.addContenu',function(){
                var id = $(this).attr('id');
                var planche = $('#fichesModal').attr('data_planche');
                var tr = $(this).closest('tr');

                planches.changeState(id, "Planche");
                planches.affectContenu(id,planche,function(contenu){
                    addContenuInPlanche(id,contenu);
                    tr.remove();
                });

            });

          //Suppression d'un contenu.
          $(document).on('click','.removeContenu',function(){
                var id = $(this).attr('id');
                var tr = $(this).closest('tr');

                planches.deleteContenu(id,function(contenu){
                    tr.remove();
                });

            });

            $(document).on('click','.contenu img.remove',function(){
                var contenu = $(this).closest('.contenu');
                var obj = planches.getContenu(contenu.attr('id'));
                obj = obj[0];

                planches.changeState(obj.getId(), "Entrée");
                planches.affectContenu(obj.getId(),null,function(){
                    addContenuInListe(obj);
                    contenu.remove();
                });
            });

            $(document).on('click','.contenu .finish img',function(){
                var img = $(this);
                var parent = img.parent();

                if(parent.width() != 45){
                    parent.animate({
                        width: '45px'
                    },{
                        duration:300,
                        queue:false,
                        complete: function(){
                            parent.css({
                                'border': 'none'
                            });
                        }
                    });

                    img.animate({
                        left:'0px'
                    },{
                        duration:300,
                        queue:false
                    });
                    img.siblings().animate({
                        right:'-82px'
                    },{
                        duration:250,
                        queue:false
                    });
                } else {
                    parent.css({
                            'border': '1px solid #28B463',
                            'border-right': '0px',
                            'border-radius': '20px 0px 0px 20px'
                    });
                    parent.animate({
                        width: '130px'
                    },{
                        duration:300,
                        queue:false
                    });

                    img.animate({
                        left:'0px'
                    },{
                        duration:300,
                        queue:false
                    });
                    img.siblings().animate({
                        right:'12px'
                    },{
                        duration:350,
                        queue:false
                    });
                }
            });

            $(document).on('click','.contenu .finish h3',function(){
                var contenu = $(this).closest('.contenu');
                var obj = planches.getContenu(contenu.attr('id'));
                obj = obj[0];
                planches.changeState(obj.getId(),'Terminé');
                planches.affectContenu(obj.getId(),null,function(){
                    contenu.remove();
                });
            });

            //Affichage de la fiche
            $(document).on('click','.contenu img.computer',function(){

                var id = $(this).closest('.contenu').attr('id');
                var data = planches.getContenu(id);
                data = data[0];

                //Récupération de la valeur d'une checkbox
                function getValue(value){
                    return value != null ? !!+value.substr(1,value.indexOf(':')-1) : null;
                }

                //Récupération du staff ayant coché
                function getStaffId(value){
                    return value != null ? value.substring(value.indexOf(':')+1,value.indexOf('}')) : null;
                    //return value.substr(value.length-2,1);
                }

                //AFFECTATION DES VALEURS
                var type = "";
                if(data.getType() == 'prepa'){
                    type = "Fiche de préparation";
                    //SET FIELD PREPA
                    $('#nomDuPoste').html("VD"+data['contenu'].VD.id+" <span class='glyphicon glyphicon-plus' aria-hidden='true'></span>");
                    $('#modele').val(data['contenu'].modele);
                    $('#etiquetage').attr('checked',data['contenu'].etiquetage == "1" ? true:false);
                    $('#dossierSAV').attr('checked',data['contenu'].dossierSAV == "1" ? true:false);
                    $('#septZip').prop('checked', getValue(data['contenu'].septZip))
                            .attr('data_staff', getStaffId(data['contenu'].septZip) == "null" ? null:getStaffId(data['contenu'].septZip));
                    $('#acrobat').prop('checked', getValue(data['contenu'].acrobat))
                            .attr('data_staff', getStaffId(data['contenu'].acrobat) == "null" ? null:getStaffId(data['contenu'].acrobat));
                    $('#flash').prop('checked', getValue(data['contenu'].flash))
                            .attr('data_staff', getStaffId(data['contenu'].flash) == "null" ? null:getStaffId(data['contenu'].flash));
                    $('#java').prop('checked', getValue(data['contenu'].java))
                            .attr('data_staff', getStaffId(data['contenu'].java) == "null" ? null:getStaffId(data['contenu'].java));
                    $('#pdf').prop('checked', getValue(data['contenu'].pdf))
                            .attr('data_staff', getStaffId(data['contenu'].pdf) == "null" ? null:getStaffId(data['contenu'].pdf));
                    $('#autre').val(data['contenu'].autre);
                    $('#type').val(data['contenu'].type);
                    $('#userAccount').val(data['contenu'].userAccount);
                    $('#mdp').val(data['contenu'].mdp);
                    $('#activation').prop('checked', getValue(data['contenu'].activation))
                            .attr('data_staff', getStaffId(data['contenu'].activation) == "null" ? null:getStaffId(data['contenu'].activation));
                    $('#uninstall').attr('checked', data['contenu'].uninstall == "1" ? true:false);
                    $('#maj').attr('checked', data['contenu'].maj == "1" ? true:false);
                    $('#register').attr('checked', data['contenu'].register == "1" ? true:false);
                    $('#verifActivation').attr('checked', data['contenu'].verifActivation == "1" ? true:false);
                    $('#divers').val(data['contenu'].divers);
                    //SET FIELD VD
                    $('#client').val(data.contenu.VD.client);
                    $('#denomination').val(data.contenu.VD.type);
                    $('#numeroSerie').val(data.contenu.VD.numeroSerie);
                    $('#versionWindows').val(data.contenu.VD.versionWindows);
                    $('#numLicenceW').val(data.contenu.VD.numLicenceW);
                    $('#versionOffice').val(data.contenu.VD.versionOffice);
                    $('#numLicenceO').val(data.contenu.VD.numLicenceO);
                    $('#garantie').val(data.contenu.VD.garantie);
                    $('#debutGarantie').val(data.contenu.VD.debutGarantie);
                    $('#mail').val(data.contenu.VD.mail);
                    $('#mdpMail').val(data.contenu.VD.mdp);
                } else {
                    type = "Fiche de réparation";
                    $('#id_contenu').val(data['contenu'].id_contenu);
                    $('#marque').val(data['contenu'].marque);
                    $('#model').val(data['contenu'].model);
                    $('#sn').val(data['contenu'].sn);
                    $('#vd').val(data['contenu'].vd);
                    $('#os').val(data['contenu'].os);
                    $('#motDePasse').val(data['contenu'].motDePasse);
                    $('#login').val(data['contenu'].login);
                    $('#office').val(data['contenu'].office);
                    $('#autreSoft').val(data['contenu'].autreSoft);
                }

                //CHANGEMENT DES CHOIX DE LA SELECT
                if(type=="Fiche de réparation"){
                    $('.changeState').val(data.getEtat());
                    $('.changeState').css('display','block');
                }
                else{
                    $('.changeState').css('display','none');
                }

                autosize($('#modele'));
                //CHANGEMENT DU TITRE
                $('.retour.title h3').html(type);

                //CHANGEMENT DES INFOS
                $('#fichesModal').attr('data_id_contenu',id);

                /*GESTION DU CONTENU*/
                $('.container.fiche .repaTmpl').css('display',type=="Fiche de réparation"?"block":"none");
                $('.container.fiche .prepaTmpl').css('display',type=="Fiche de préparation"?"block":"none");

                //fade left out.
                $('.modal-body .container.home').hide("slide", { direction: "left" }, 600);
                //fade right in
                $('.modal-body .container.fiche').css('display','block');
                $('.modal-body .container.fiche').animate({
                    right : 0,
                    left : 0
                },{
                    duration:600,
                    queue:false,
                    complete: function(){
                    $('.modal-body .container.fiche').css('position','relative');
                    $('.modal-body .container.fiche').css('margin-top','0px');
                }
                });


                $('.modal-body').animate({
                    height: $('.container.fiche').height()+30
                },{
                    duration:600,
                    queue:false,
                    complete: function(){
                        $('.modal-body').css('height','auto');
                    }
                });

                //CHANGER LE BOUTON POUR ENREGISTRER
                $('.validerOuEnregistrer').first().text('Enregistrer');
                $('.validerOuEnregistrer').last().css('display','inline-block');
            });

            function switchModal() {
                    $('.modal-body').css('height',$('.modal-body .container.fiche').height()+30);
                    $('.modal-body .container.fiche').css('position','absolute');
                    $('.modal-body .container.fiche').css('margin-top','15px');
                    $('.modal-body .container.fiche').animate({
                        right : '-100%',
                        left : '100%'
                    },{
                        duration:600,
                        queue:false,
                        complete :function(){
                            $('.modal-body .container.fiche').css('display','none');
                        }
                    });

                    $('.modal-body').animate({
                        height: $('.container.home').height()+30
                    },{
                        duration:600,
                        queue:false,
                        complete:function(){
                            $('.modal-body').css('height','auto');
                        }
                    });

                    //fade right in
                    $('.modal-body .container.home').show("slide", { direction: "left" }, 600);

                    //CHANGER LE BOUTON POUR ENREGISTRER
                    $('.validerOuEnregistrer').first().text('Valider');
                    $('.validerOuEnregistrer').last().css('display','none');
                }

            //Retour sur la planche.
            $(document).on('click','.validerOuEnregistrer',function(){

                if($(this).text() == 'Enregistrer'){
                    //fade right out
                    if($('.retour h3').text() == 'Fiche de préparation'){
                        //Recuperation des champs prepa
                        var id_contenu = $('#fichesModal').attr('data_id_contenu');
                        var modele = $('#modele').val();
                        var etiquetage = $('#etiquetage').is(':checked') ? '1' : '0';
                        var dossierSAV = $('#dossierSAV').is(':checked') ? '1' : '0';
                        var septZip = $('#septZip').is(':checked') ? '{1:'+ $('#septZip').attr('data_staff') +'}' : '{0:null}';
                        var acrobat = $('#acrobat').is(':checked') ? '{1:'+ $('#acrobat').attr('data_staff') +'}' : '{0:null}';
                        var flash = $('#flash').is(':checked') ? '{1:'+ $('#flash').attr('data_staff') +'}' : '{0:null}';
                        var java = $('#java').is(':checked') ? '{1:'+ $('#java').attr('data_staff') +'}' : '{0:null}';
                        var pdf = $('#pdf').is(':checked') ? '{1:'+ $('#pdf').attr('data_staff') +'}' : '{0:null}';
                        var autre = $('#autre').val();
                        var type = $('#type').val();
                        var userAccount = $('#userAccount').val();
                        var mdp = $('#mdp').val();
                        var activation = $('#activation').is(':checked') ? '{1:'+ $('#activation').attr('data_staff') +'}' : '{0:null}';
                        var uninstall = $('#uninstall').is(':checked') ? '1' : '0';
                        var maj = $('#maj').is(':checked') ? '1' : '0';
                        var register = $('#register').is(':checked') ? '1' : '0';
                        var verifActivation = $('#verifActivation').is(':checked') ? '1' : '0';
                        var divers = $('#divers').val();
                        //recuperation des champs VD
                        var client = $('#client').val();
                        var denomination = $('#denomination').val();
                        var numeroSerie = $('#numeroSerie').val();
                        var versionWindows = $('#versionWindows').val();
                        var numLicenceW = $('#numLicenceW').val();
                        var versionOffice = $('#versionOffice').val();
                        var numLicenceO = $('#numLicenceO').val();
                        var garantie = $('#garantie').val();
                        var debutGarantie = $('#debutGarantie').val();
                        var mail = $('#mail').val();
                        var mdpMail = $('#mdpMail').val();

                        //Insertion ou mise a jour
                        planches.insertOfUpdatePrepa(id_contenu
                                                    ,$('.modal').attr('data_planche')
                                                    ,modele
                                                    ,etiquetage
                                                    ,dossierSAV
                                                    ,septZip
                                                    ,acrobat
                                                    ,flash
                                                    ,java
                                                    ,pdf
                                                    ,autre
                                                    ,type
                                                    ,userAccount
                                                    ,mdp
                                                    ,activation
                                                    ,uninstall
                                                    ,maj
                                                    ,register
                                                    ,verifActivation
                                                    ,divers
                                                    ,client
                                                    ,denomination
                                                    ,numeroSerie
                                                    ,versionWindows
                                                    ,numLicenceW
                                                    ,versionOffice
                                                    ,numLicenceO
                                                    ,garantie
                                                    ,debutGarantie
                                                    ,mail
                                                    ,mdpMail);

                    } else {
                        var id_contenu = $('#fichesModal').attr('data_id_contenu');
                        var marque = $('#marque').val();
                        var model = $('#model').val();
                        var sn = $('#sn').val();
                        var vd = $('#vd').val();
                        var os = $('#os').val();
                        var motDePasse = $('#motDePasse').val();
                        var login = $('#login').val();
                        var office = $('#office').val();
                        var autreSoft = $('#autreSoft').val();

                        planches.insertOfUpdateRepa(id_contenu
                                                    ,marque
                                                    ,model
                                                    ,sn
                                                    ,vd
                                                    ,os
                                                    ,motDePasse
                                                    ,login
                                                    ,office
                                                    ,autreSoft);

                    }
                    switchModal();

                } else if($(this).text() == 'Retour') {
                    switchModal();
                } else {

                    $('#fichesModal').modal('toggle');
                }
            });

            $(document).on('hidden.bs.modal','.modal', function () {
                //INIT
                $('.modal-body .container.fiche').css('right','-100%');
                $('.modal-body .container.fiche').css('left','100%');
                $('.modal-body .container.fiche').css('display','none');
            });


            /*ASSIGNATION DU VISA*/
            $(document).on('click','.prepaTmpl input[type="checkbox"]',function(){
                $(this).attr('data_staff',$('.modal').attr('data_staff'));
            });

              //CHANGER L'ETAT
            $(document).on('change','.changeState',function(){
                var id = $('.modal').attr('data_id_contenu');
                var state = $(':selected',this).val();
                planches.changeState(id,state);

                //SWITCH CONTENT
                if(state != "Planche"){
                    var contenu = planches.getContenu(id)[0];
                    planches.affectContenu(id,null,function(){
                        $('#'+id+'.contenu').remove();
                        addContenuInListe(contenu);
                        switchModal();
                    });
                }
            });

          $('.prepaTmpl .VD #nomDuPoste').click(function(){
              var css = {};
              var degD = 0;
              var degF = 0;

              var self = $(this).parent();

              if(self.css('height') == "370px"){
                    css = { height: "48px"};
                    degD = 45;
                    degF = 0;
              }
              else{
                  css = { height: "370px"};
                  degD = 0;
                  degF = 45;
              }

              self.animate(css,600);

              var elem = $('span',self);
              //ANIMATE PLUS
              $({deg: degD}).animate({deg: degF}, {
                    duration: 450,
                    step: function(now){
                        elem.css({
                             transform: "rotate(" + now + "deg)"
                        });
                    }
                });
          });
            /*obj.getType(),obj.contenu.VD != undefined ? obj.contenu.VD.id : null*/
            var addContenuInPlanche = function(id,contenu){
                var type = contenu.getType();
                var VD = contenu.contenu.VD != undefined ? contenu.contenu.VD.id : null;
                var ticket_id = contenu.ticket_id;
                var number = contenu.number;
                var org_name = contenu.org_name;

                $('.modal-body div:first').prepend('<div class="col-md-3 contenu" id="'+id+'">'+
                    '<div class="prepa">'+
                    '<div class="finish"><img src="../assets/default/images/finish.png"><h3>Valider</h3></div>'+
                    '<img class="remove" src="../assets/default/images/remove.png">'+
                    '<img class="computer" src="../assets/default/images/computer.png">'+
                    '<h2>'+ (type == "prepa"  ? "VD"+VD : "REPA") +'</h2>'+
                    '<p><a class="no-pjax" href="./tickets.php?id='+ticket_id+'">'+number + '</a>-' + org_name+'</p>'+
                    '</div>'+
                    '</div>');
            }

            var addContenuInListe = function(obj){
                $('.list.atelierT tbody').append('<tr><td>'+obj.number+'</td><td>'+obj.org_name+'</td><td>'+obj.getType()+'</td><td>'+obj.getEtat()+'</td><td><button class="btn btn-success addContenu" id="'+ obj.getId() +'" >Affecter</button></td><td><button class="btn btn-danger removeContenu" id="'+ obj.getId() +'" ><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button></td></tr>');
            }


        //});

        </script>
