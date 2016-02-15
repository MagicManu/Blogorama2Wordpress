<?php
// Afficher les erreurs à l'écran
ini_set('display_errors', 1);
// Enregistrer les erreurs dans un fichier de log
ini_set('log_errors', 1);
// Nom du fichier qui enregistre les logs (attention aux droits à l'écriture)
ini_set('error_log', dirname(__file__) . 'log_error_php.txt');
// Afficher les erreurs et les avertissements
error_reporting(E_ALL);


//function DOMinnerHTML(DOMNode $element) { 
//    // Contenu HTML d'un noeu
//    $innerHTML = ''; 
//    $children  = $element->childNodes;
//
//    foreach ($children as $child) 
//        $innerHTML .= $element->ownerDocument->saveHTML($child);
//    
//    return $innerHTML; 
//} 

function DOMinnerHTML($element){ 
   $innerHTML = ''; 
   $children = $element->childNodes; 
   
   foreach ($children as $child){ 
      $tmp_dom = new DOMDocument(); 
      //$tmp_dom = new DomDocument('1.0' ,'utf-8');
      $tmp_dom->appendChild($tmp_dom->importNode($child, true)); 
      $innerHTML.=trim($tmp_dom->saveHTML()); 
   } 
   //$innerHTML=html_entity_decode(html_entity_decode($innerHTML));
   return $innerHTML; 
} 



function DateCom2DateXML($Date, $Format=1){
    // Renvoi le format 2014-01-09 15:14:20 pour les commentaires
    // ou Tue, 28 Aug 2012 13:19:00 +0000 pour l'article
    // 2013/12 pour le dossier des images
    $MoisTxt2Num = array(
        'janv.'=>1, 'févr.'=>2, 'mars'=>3, 'avril'=>4, 'mai'=>5, 'juin'=>6, 'juil.'=>7, 'août'=>8, 'sept.'=>9, 'oct.'=>10, 'nov.'=>11, 'déc.'=>12,
        'janvier'=>1, 'février'=>2, 'mars'=>3, 'avril'=>4, 'mai'=>5, 'juin'=>6, 'juillet'=>7, 'août'=>8, 'septembre'=>9, 'octobre'=>10, 'novembre'=>11, 'décembre'=>12
    );
    list($JFr, $D, $M, $A, $H) = explode(' ', $Date);
    list($He, $Mi) = explode(':', $H);
    $MkDate = mktime($He, $Mi, 0, $MoisTxt2Num[$M], $D, $A);
    
    $ListeFormat = array(  
        1 => 'Y-m-d H:i:s',
        2 => 'D, d M Y H:i:s O',
        3 => 'Y/m'
    );
 
    return date($ListeFormat[$Format], $MkDate);
}
