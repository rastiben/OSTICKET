function Planche(callback=null) {

    /*
    *CONSTRUCTEUR
    */
    var self = this;
    AtelierAjax.getPlanches(function (data) {
        var data = $.parseJSON(data);
        self.contenu = [];
        $(data).each(function ($number, $obj) {

            //Récupération de l'organisation
            if($obj['org_id'] != "0"){
                self.contenu.push(new Contenu($obj['ticket_id'],
                                              $obj['number'],
                                              $obj['org_name'],
                                              $obj['contenuType'],
                                              $obj['numContenue'],
                                              $obj['planche'],
                                              $obj['etat'],
                                              ($obj['contenuType'] == "prepa" ?
                new Preparation(new VD($obj['id'],$obj['client'],$obj['type'],$obj['numeroSerie'],$obj['versionWindows'],$obj['numLicenceW'],$obj['versionOffice'],$obj['numLicenceO'],$obj['garantie'],$obj['debutGarantie'],$obj['mail'],$obj['mdp']), $obj['acrobat'], $obj['activation'], $obj['autre'], $obj['dossierSAV'],$obj['type'], $obj['etiquetage'], $obj['flash'], $obj['id_contenu'], $obj['java'], $obj['maj'], $obj['mdp'], $obj['modele'], $obj['pdf'], $obj['register'], $obj['septZip'], $obj['uninstall'], $obj['userAccount'], $obj['verifActivation'], $obj['divers']) :
                new Reparation($obj['marque'],$obj['model'],$obj['sn'],$obj['vd'],$obj['os'],$obj['motDePasse'],$obj['login'],$obj['office'],$obj['autreSoft'])),$obj['priority'],$obj['org_id']));
            }
        });
        if(callback != null)
            callback(self.contenu);

    });

    /*
    *Mise a jour de la colonne en cours.
    */
    self.majEnCours = function(){
        //reset
        var array = ["b1","b2","b3","p1","p2","p3","m1","m2","m3","m4","s1","s2","s3","s4","s5","s6"];
        $.each(array,function(key,value){
            $('.'+value+" h4").text(value.toUpperCase() + " : ");
        });

        //maj
        $.each(self.contenu,function(key,value){
            $('.'+value.planche+" h4").text($('.'+value.planche).text() + value.org_name + " ; ");
        });

        //remove two lasts char
        $.each(array,function(key,value){
            $('.'+value+" h4").text($('.'+value+" h4").text().substr(0,$('.'+value+" h4").text().length-2));
        });
    }

    /*
    *Recupération des contenu non affectés à une planche
    */
    self.getContenues = function(callback) {
        var contenues = $.grep(self.contenu,function(obj){
            return (obj.getPlanche() == null && obj.getEtat() != "Terminé")
        });
        callback(contenues);
    };

    /*
    *Recupération des contenu d'une planche
    */
    self.getPlanche = function(planche) {
        return $.grep(self.contenu,function(obj){
            return obj.getPlanche() == planche
        });
    };

    /*
    *Recupération du contenu d'un contenu d'une planche
    */
    self.getContenu = function(id){
        return $.grep(self.contenu,function(obj){
            return obj.getId() == id
        });
    };

    /*
    *Affectation d'un nouveau contenu sur une planche
    */
    self.affectContenu = function(id,planche,callback){
        AtelierAjax.affectContenu(id,planche,function(){
            var contenu =  self.getContenu(id);

            contenu[0].planche = planche;
            callback(contenu[0]);
        });
        self.majEnCours();
    };

    self.changeState = function(id,state){
        var contenu =  self.getContenu(id);
        contenu[0].etat = state;

        AtelierAjax.changeState(id,state,function(){
        });
    }

    /*
    *Ajout d'un nouveau contenu sur une planche
    */
    /*self.addContenu = function(id,type,planche=null){
        var etat = (type == "prepa" ? "Planche" : "Entrées");
        AtelierAjax.addContenu(id,type,planche,function(data){
            self.contenu.push(new Contenu(type,
                                        data,
                                        planche,
                                        "",
                                        (type == "prepa" ?
            new Preparation() :
            new Reparation())));
            //callback(data);
        });
    };*/

    /*
    *Mise a jour ou ajout du contenu d'une prepa
    */
    self.insertOfUpdatePrepa = function(id_contenu, planche, modele, etiquetage, dossierSAV, septZip, acrobat, flash, java, pdf, autre, type, userAccount, mdp, activation, uninstall, maj, register, verifActivation, divers, client, type, numeroSerie, versionWindows, numLicenceW, versionOffice, numLicenceO, garantie, debutGarantie, mail, mdpMail) {
        var contenu = self.getContenu(id_contenu);
        contenu = contenu[0];
        contenu = contenu['contenu'];
        contenu.modele = modele;
        contenu.etiquetage = etiquetage;
        contenu.dossierSAV = dossierSAV;
        contenu.septZip = septZip;
        contenu.acrobat = acrobat;
        contenu.flash = flash;
        contenu.java = java;
        contenu.pdf = pdf;
        contenu.autre = autre;
        contenu.type = type;
        contenu.userAccount = userAccount;
        contenu.mdp = mdp;
        contenu.activation = activation;
        contenu.uninstall = uninstall;
        contenu.maj = maj;
        contenu.register = register;
        contenu.verifActivation = verifActivation;
        contenu.divers = divers;

        //MAJ VD
        contenu.VD.client = client;
        contenu.VD.type = type;
        contenu.VD.numeroSerie = numeroSerie;
        contenu.VD.versionWindows = versionWindows;
        contenu.VD.numLicenceW = numLicenceW;
        contenu.VD.versionOffice = versionOffice;
        contenu.VD.numLicenceO = numLicenceO;
        contenu.VD.garantie = garantie;
        contenu.VD.debutGarantie = debutGarantie;
        contenu.VD.mail = mail;
        contenu.VD.mdp = mdpMail;

        AtelierAjax.insertOrUpdatePrepa(id_contenu,modele,etiquetage,dossierSAV, septZip, acrobat, flash, java, pdf, autre, type, userAccount, mdp, activation, uninstall, maj, register, verifActivation, divers);
        AtelierAjax.updateVD(contenu.VD.id,client,type,numeroSerie,versionWindows,numLicenceW,versionOffice,numLicenceO,garantie,debutGarantie,mail,mdpMail);
    }

     /*
    *Mise a jour ou ajout du contenu d'une repa
    */
    self.insertOfUpdateRepa = function(id_contenu,marque,model,sn,vd,os,motDePasse,login,office,autreSoft) {
        var contenu = self.getContenu(id_contenu);
        contenu = contenu[0];
        contenu = contenu['contenu'];
        contenu.marque = marque;
        contenu.model = model;
        contenu.sn = sn;
        contenu.vd = vd;
        contenu.os = os;
        contenu.motDePasse = motDePasse;
        contenu.login = login;
        contenu.office = office;
        contenu.autreSoft = autreSoft;
        AtelierAjax.insertOrUpdateRepa(id_contenu,marque,model,sn,vd,os,motDePasse,login,office,autreSoft);
    }


    /*
    * Suppression d'un contenu
    */
    self.deleteContenu = function(id,callback){
        var index = self.contenu.indexOf(self.getContenu(id));
        self.contenu.splice(index,1);

        AtelierAjax.deleteContenu(id,callback);
    }
}



function Contenu(ticket_id, number, org_name, type, id, planche, etat, contenu, priority, org_id) {

    var self = this;
    self.ticket_id = ticket_id;
    self.number = number;
    self.org_name = org_name;
    self.type = type;
    self.id = id;
    self.planche = planche;
    self.contenu = contenu;
    self.etat = etat;
    self.priority = priority;
    self.org_id = org_id;

    self.getType = function () {
        return self.type;
    }
    self.getId = function () {
        return self.id;
    }
    self.getPlanche = function () {
        return self.planche;
    }
    self.getEtat = function () {
        return self.etat;
    }
}

function Preparation(VD=null,acrobat=null,activation=null,autre=null,dossierSAV=null,type=null,etiquetage=null,flash=null,id_contenu=null,java=null,maj=null,mdp=null,modele=null,pdf=null,register=null,septZip=null,uninstall=null,userAccount=null,verifActivation=null,divers=null){

    var self = this;
    self.VD = VD;
    self.acrobat = acrobat;
    self.activation = activation;
    self.autre = autre;
    self.dossierSAV = dossierSAV;
    self.etiquetage = etiquetage;
    self.flash = flash;
    self.id_contenu = id_contenu;
    self.java = java;
    self.maj = maj;
    self.mdp = mdp;
    self.type = type;
    self.modele = modele;
    self.pdf = pdf;
    self.register = register;
    self.septZip = septZip;
    self.uninstall = uninstall;
    self.userAccount = userAccount;
    self.verifActivation = verifActivation;
    self.divers = divers;
}

function Reparation(marque=null,model=null,sn=null,vd=null,os=null,motDePasse=null,login=null,office=null,autreSoft=null){

    var self = this;
    self.marque = marque;
    self.model = model;
    self.sn = sn;
    self.vd = vd;
    self.os = os;
    self.motDePasse = motDePasse;
    self.login = login;
    self.office = office;
    self.autreSoft = autreSoft;
}

function VD(id,client,type,numeroSerie,versionWindows,numLicenceW,versionOffice,numLicenceO,garantie,debutGarantie,mail,mdp){

    var self = this;

    self.id = id;
    self.client = client;
    self.type = type;
    self.numeroSerie = numeroSerie;
    self.versionWindows = versionWindows;
    self.numLicenceW = numLicenceW;
    self.versionOffice = versionOffice;
    self.numLicenceO = numLicenceO;
    self.garantie = garantie;
    self.debutGarantie = debutGarantie;
    self.mail = mail;
    self.mdp = mdp;

}

class AtelierAjax{

    static doAjax(data,callback){
        $.ajax({
            url:'./Request/Atelier.php'
            ,method:'POST'
            ,data:data
        }).success(callback);
    }

    static getPlanches(callback){
         var data = {
                request:'getPlanches'
            };
         this.doAjax(data,callback);
    }

    static affectContenu(id,planche,callback){
         var data = {
                request:'affectContenu'
                ,id:id
                ,planche:planche
            };
         this.doAjax(data,callback);
    }

    static changeState(id,state,callback){
         var data = {
                request:'changeState'
                ,id:id
                ,etat:state
            };
         this.doAjax(data,callback);
    }

    static deleteContenu(id,callback){
        var data = {
            request:'deleteContenu'
            ,id:id
        }
        this.doAjax(data,callback)
    }

    static addContenu(id,type,planche,callback){
        var data = {
                request:'addContenu',
                ticket_id:id,
                type:type,
                planche:planche
            };
        this.doAjax(data,callback);
    }

    static insertOrUpdatePrepa(id_contenu,modele,etiquetage,dossierSAV, septZip, acrobat, flash, java, pdf, autre, type, userAccount, mdp, activation, uninstall, maj, register, verifActivation, divers){
        $.ajax({
            url:'./Request/Atelier.php'
            ,method:'POST'
            ,data : {
                request:'addPrepaInfo'
                ,id_contenu:id_contenu
                ,modele:modele
                ,etiquetage:etiquetage
                ,dossierSAV:dossierSAV
                ,septZip:septZip
                ,acrobat:acrobat
                ,flash:flash
                ,java:java
                ,pdf:pdf
                ,autre:autre
                ,type:type
                ,userAccount:userAccount
                ,mdp: mdp
                ,activation: activation
                ,uninstall: uninstall
                ,maj: maj
                ,register: register
                ,verifActivation: verifActivation
                ,divers:divers
            }
        });
    }

    static insertOrUpdateRepa(id_contenu,marque,model,sn,vd,os,motDePasse,login,office,autreSoft){
        $.ajax({
            url:'./Request/Atelier.php'
            ,method:'POST'
            ,data : {
                request:'addRepaInfo'
                ,id_contenu:id_contenu
                ,marque:marque
                ,model:model
                ,sn:sn
                ,vd:vd
                ,os:os
                ,motDePasse:motDePasse
                ,login:login
                ,office:office
                ,autreSoft:autreSoft
            }
        });
    }

    static updateVD(id,client,type,numeroSerie,versionWindows,numLicenceW,versionOffice,numLicenceO,garantie,debutGarantie,mail,mdp){
        $.ajax({
            url:'./Request/Atelier.php'
            ,method:'POST'
            ,data : {
                request:'updateVD'
                ,id:id
                ,client:client
                ,type:type
                ,numeroSerie:numeroSerie
                ,versionWindows:versionWindows
                ,numLicenceW:numLicenceW
                ,versionOffice:versionOffice
                ,numLicenceO:numLicenceO
                ,garantie:garantie
                ,debutGarantie:debutGarantie
                ,mail:mail
                ,mdp:mdp
            }
        });
    }

}


//moment.locale('fr');

//récupération des informations (Rapports et horaires) + Ajout d'un rapport ou maj d'un horaires
app.factory('atelierFactory',['$http',function($http){
   return{
       getAtelier: function(ticketID) {
             //return the promise.
             return $http({method: 'POST',
                            url: './Request/Atelier.php',
                            data: $.param({request: 'getAtelierTicket',
                                           ticketID:ticketID
                                          }),
                            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                        })
                       .then(function(result) {
                            //resolve the promise as the data
                            return result.data;
                        });
        },
       insertOrUpdateFicheSuivi : function(data){
           return $http({method: 'POST',
                            url: './Request/Atelier.php',
                            data: data,
                            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                        })
                       .then(function(result) {
                            //resolve the promise as the data
                            return result.data;
                        });
       },
       insertOrUpdateRepa : function(data){
           return $http({method: 'POST',
                            url: './Request/Atelier.php',
                            data: data,
                            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                        })
                       .then(function(result) {
                            //resolve the promise as the data
                            return result.data;
                        });
       },
       printFS : function(ticketID,data,img=undefined){
            return $http({method: 'POST',
                url: './ajaxs.php/print/fs/'+ticketID,
                data: {img:img,
                       data:data},
                headers: {'Content-Type': 'application/json'}
            })
            .then(function(data){
                return data.data;
            });
       }

   };
}]);

app.controller("atelierCtrl",["$scope","atelierFactory", function($scope,atelierFactory){
    //Init

    $scope.init = function(ticketID,staffName,agents){
        $scope.agents = agents;

        $.each(agents,function(key,value){
           if(value.name === staffName)
               $scope.tech = value;
        });

        autosize($('.inputField textarea'));
        $scope.ticketID = ticketID;
        atelierFactory.getAtelier($scope.ticketID).then(function(atelier){
            $scope.atelier = atelier;
            $scope.prepas = [];

            //INIT variable
            $scope.ficheSuiviText = "Fiche de suivi";
            $scope.buttonFicheSuivi = "Mettre à jour";
            if(atelier.length > 0){
                if(atelier[0].contenuType == "prepa"){
                    $scope.prepas = $scope.atelier;
                    $scope.showPrepa = true;
                } else {
                    $scope.showRepa = true;
                    $scope.idRepa = atelier[0].numContenue;
                    $scope.type = atelier[0].typeFiche;
                    $scope.accessoire = atelier[0].accessoireFiche;

                    $scope.etat = atelier[0].etat;
                    $scope.marque = atelier[0].marque;
                    $scope.model = atelier[0].model;
                    $scope.sn = atelier[0].sn;
                    $scope.vd = atelier[0].vd;
                    $scope.os = atelier[0].os;
                    $scope.motDePasse = atelier[0].motDePasse;
                    $scope.login = atelier[0].login;
                    $scope.office = atelier[0].office;
                    $scope.autreSoft = atelier[0].autreSoft;
                }
            }
        });
    }

    $scope.addFicheSuivi = function(type){
        if($scope.atelier.length == 0){
            $scope.addContenu(type,function(idRepa){
                $scope.idRepa = idRepa;
                $scope.addFiche();
            });
        } else {
            $scope.addFiche();
        }

        //ajout fiche de suivi
    }

    $scope.printRepa = function(){
        function loadFile(url,callback){
            JSZipUtils.getBinaryContent(url,callback);
        }
        loadFile("./documents/ficheSuivi.docx",function(error,content){
            if (error) { throw error };
            var zip = new JSZip(content);
            var doc=new Docxtemplater().loadZip(zip)
            doc.setData({
                tel:$scope.contact.tel || ""
                ,adresse:$scope.contact.address || ""
                ,organisation:$scope.org || ""
                ,name:$scope.names || ""
                ,openDate:$scope.dateOuverture || ""
                ,tech:$scope.tech.name || ""
                ,type:$scope.type || ""
                ,accessoire:$scope.accessoire || ""
                ,description:$scope.description || ""
                ,marque:$scope.marque || ""
                ,modele:$scope.model || ""
                ,sn:$scope.sn || ""
                ,vd:$scope.vd || ""
                ,os:$scope.os || ""
                ,mdp:$scope.motDePasse || ""
                ,login:$scope.login || ""
                ,office:$scope.office || ""
                ,autreSoft:$scope.autreSoft || ""
            });

            try {
                // render the document (replace all occurences of {first_name} by John, {last_name} by Doe, ...)
                doc.render()
            }
            catch (error) {
                var e = {
                    message: error.message,
                    name: error.name,
                    stack: error.stack,
                    properties: error.properties,
                }
                console.log(JSON.stringify({error: e}));
                // The error thrown here contains additional information when logged with JSON.stringify (it contains a property object).
                throw error;
            }

            var out=doc.getZip().generate({
                type:"blob",
                mimeType: "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            }) //Output the document using Data-URI
            saveAs(out,"output.docx")
        })
    }

    $scope.addFiche = function(){
        var data = $.param({request: 'insertOrUpdateFicheSuivi',
                 id_repa:$scope.idRepa,
                 type: $scope.type,
                 accessoire: $scope.accessoire
        });
        atelierFactory.insertOrUpdateFicheSuivi(data).then(function(){

        });
        //id_contenu,marque,model,sn,vd,os,motDePasse,login,office,autreSoft
        var data = $.param({request: 'addRepaInfo'
                            ,id_contenu:$scope.idRepa
                            ,marque:$scope.marque
                            ,model:$scope.model
                            ,sn:$scope.sn
                            ,vd:$scope.vd
                            ,os:$scope.os
                            ,motDePasse:$scope.motDePasse
                            ,login:$scope.login
                            ,office:$scope.office
                            ,autreSoft:$scope.autreSoft});
        atelierFactory.insertOrUpdateRepa(data).then(function(){

        });
    }

    $scope.addContenu = function(type,callback){
        var nb =  type == "repa" ? 1 : $scope.nbPrepa;

        for(var i=0;i<nb;i++){
            AtelierAjax.addContenu($scope.ticketID,type,null,function(data){
                data = $.parseJSON(data);

                if(type == "prepa"){
                    $scope.prepas.push({numContenue:data.id,contenuType:type,id_VD:data.vd});
                } else {

                }

                $scope.$apply();

                if(type == "repa") callback(data.id);
            });
        }
    }

    $scope.setTicketAtelierType = function(type){
        if(type == "repa")
            $scope.showRepa = "true";
        else
            $scope.showPrepa = "true";
    }

    $scope.displayCard = function(element){

        var element = $(element);

        var css = {};
        var degD = 0;
        var degF = 0;

        if(element.css('height') != "54px"){
            css = { height: "54px"};
            degD = 45;
            degF = 0;
        }
        else{
            //GET AUTO HEIGHT
            var curHeight = element.height(),
            autoHeight = element.css('height', 'auto').height();
            element.height(curHeight);

            css = { height: autoHeight};
            degD = 0;
            degF = 45;
        }

        element.animate(css,600,function(){
            if(degF == 45) element.css('height', 'auto');
        });

        var elem = $('span',element);
        //ANIMATE PLUS
        $({deg: degD}).animate({deg: degF}, {
            duration: 450,
            step: function(now){
                elem.css({
                        transform: "rotate(" + now + "deg)"
                });
            }
        });
    }

    $scope.pdfjsframe = undefined;
    $scope.displayPDF = function(pdf) {
        //Ajout de l'iframe.
        if($scope.pdfjsframe == undefined){
            $('#signatureFs .modal-body').append('<iframe id="pdfFrame" src="./viewer.html#zoom=page-fit"></iframe>');
        } else {
            $('#signatureFs .modal-body #pdfFrame').replaceWith('<iframe id="pdfFrame" src="./viewer.html#zoom=page-fit"></iframe>');
        }

        $scope.pdfjsframe = document.getElementById('pdfFrame');
        //Ajout du PDF dans la vue
        $scope.pdfjsframe.onload = function() {
            var pdfApp = $scope.pdfjsframe.contentWindow.PDFViewerApplication;
            pdfApp.open(pdf);
        };
    }

    $scope.removePDFView = function($event){
        var button = $event.currentTarget;
        if($(button).siblings().text() == "Valider")
            $scope.cancelSignature($event);
        else
            $scope.cancelPDF();
    }

    $scope.createPDF = function(img=undefined){

        //id_contenu,marque,model,sn,vd,os,motDePasse,login,office,autreSoft
        var data = {org:$scope.org
                    ,contact:$scope.contact
                    ,dateOuverture:$scope.dateOuverture
                    ,tech:$scope.tech
                    ,description:$scope.description
                    ,id_contenu:$scope.idRepa
                    ,marque:$scope.marque
                    ,model:$scope.model
                    ,sn:$scope.sn
                    ,vd:$scope.vd
                    ,os:$scope.os
                    ,motDePasse:$scope.motDePasse
                    ,login:$scope.login
                    ,office:$scope.office
                    ,autreSoft:$scope.autreSoft
                    ,type: $scope.type
                    ,accessoire: $scope.accessoire};

        atelierFactory.printFS($scope.ticketID,data,img).then(function(pdf){
            $scope.displayPDF(pdf);
        });
    }

    $scope.cancelPDF = function(){
        $('#signatureFs').modal('toggle');
        $('#pdfFrame').remove();
        $scope.pdfjsframe = undefined;
    }

    $scope.printFicheSuivi = function(){
        $('#signatureFs').modal('toggle');
        $scope.createPDF();
    }

    $scope.signaturePad = undefined;
    $scope.displaySignature = function($event){
        if($($event.currentTarget).text() == "Valider"){
            $scope.validSignature($event,$scope.signaturePad.toDataURL("image/jpeg"));
            //console.log($scope.signaturePad.toDataURL());
        } else {
            $($event.currentTarget).text('Valider');

            var canvas = $("#signature-pad2");
            canvas.css('display','block');
            canvas[0].width = $('#signatureFs .modal-body').width();
            canvas[0].height = '300';


            if($scope.signaturePad === undefined){
                $scope.signaturePad = new SignaturePad(canvas[0], {
                  backgroundColor: 'rgb(255, 255, 255)',
                  penColor: 'rgb(0, 0, 0)',
                  minWidth: 1,
                  maxWidth: 1,
                  dotSize: 1,
                  throttle: 50
                });
            }
        }
    }

    $scope.cancelSignature = function($event){
        $($event.currentTarget).siblings().text('Signer');
        $("#signature-pad2").css('display','none');
        $scope.signaturePad = undefined
    }

    $scope.validSignature = function($event,img){
        $($event.currentTarget).text('Signer');
        $("#signature-pad2").css('display','none');
        $scope.signaturePad = undefined

        $scope.createPDF(img);
    }

}]);
