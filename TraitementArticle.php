<?php
require 'Fonctions.php';



$AncienBlog  = isset($_POST['AncienBlog']) ? $_POST['AncienBlog'] : '';
$NouveauBlog = isset($_POST['NouveauBlog']) ? $_POST['NouveauBlog'] : '';
$VotreNom    = isset($_POST['VotreNom']) ? $_POST['VotreNom'] : '';
$VotreEmail  = isset($_POST['VotreEmail']) ? $_POST['VotreEmail'] : '';
$Lien        = isset($_POST['Lien']) ? $_POST['Lien'] : '';
$Page        = isset($_POST['Page']) ? $_POST['Page'] : 1;
$NumArt      = isset($_POST['NumArt']) ? $_POST['NumArt'] : 0;
$NumCom      = isset($_POST['NumCom']) ? $_POST['NumCom'] : 1;
$FichierXML  = isset($_POST['FichierXML']) ? $_POST['FichierXML'] : '';
$FichierNum  = isset($_POST['FichierNum']) ? $_POST['FichierNum'] : 1;
$Type        = isset($_POST['Type']) ? $_POST['Type'] : 'com';

$ChangeFichier = 0;

$IdArt = $Page * 100 + $NumArt;

$DecalCom = 0;

$XML = '';

// Traitement
if (!empty($Lien)){
    
    echo '<br>Traitement en cours article ' . ($NumArt + 1) . ' de la page ' . $Page . '...<br><br>';
    echo 'Lecture de ' . $Lien;

    $doc = new DomDocument();
    //$doc = new DomDocument('1.0' ,'utf-8');
    @$doc->loadHTMLFile($Lien);
    $finder = new DomXPath($doc);       // Pour la recherche par CLASS
    
    $NodesArt = $finder->query("//*[contains(@class, 'article-content article-content-')]");   // L'article  
 
    $Article        = $doc->getElementById('article-box-0');
    //$Titre          = trim($Article->getElementsByTagName('h2')->item(0)->textContent);
    $Titre          = trim($Article->getElementsByTagName('h2')->item(0)->getElementsByTagName('a')->item(0)->textContent);
    list(,,$Name)   = explode('/', $Article->getElementsByTagName('h2')->item(0)->getElementsByTagName('a')->item(0)->getAttribute('href'));
    $NodeDR         = $finder->query("//*[contains(@class, 'date_rubrique')]");   
    
    // Blog comme le miens
    if ($NodeDR->length > 0){
        $DateEtRubrique = $NodeDR->item(0)->textContent;
        if (substr_count($DateEtRubrique, ','))
            list($Date, $Rubrique) = explode(',', $DateEtRubrique);
        else{
            $Date = $DateEtRubrique;
            $Rubrique = '';
        }
            
        if ($NodeDR->item(0)->getElementsByTagName('a')->length > 0)
            list(, , $Rubrique) = explode('/', $NodeDR->item(0)->getElementsByTagName('a')->item(0)->getAttribute('href'));
        else
            $Rubrique = '';    
    }
    // Blog avec autre theme
    else{
        $NodeDR   = $finder->query("//*[contains(@class, 'article-title-date')]");           
        $DateFull = $NodeDR->item(0)->textContent;
        $Date     = str_replace('posté le', '', $DateFull);
        
        $Rubrique = '';  
        $NodeDR   = $finder->query("//*[contains(@class, 'article-title-rubrique')]");    
        if ($NodeDR->length > 0)
            if ($NodeDR->item(0)->getElementsByTagName('a')->length > 0)
                list(, , $Rubrique) = explode('/', $NodeDR->item(0)->getElementsByTagName('a')->item(0)->getAttribute('href'));

                  
        
        $DecalCom = 1;
    }
    $Date = trim($Date);
    $DateDossier = DateCom2DateXML($Date, 3);     
    
    
    // Les photos
    $LesImages   = $NodesArt->item(0)->getElementsByTagName('img');
    $TabloImages = array();
    
    
    $CheminDesImages = $Type == 'org' ? $NouveauBlog . '/wp-content/uploads/' : preg_replace('/\./', '.files.', $NouveauBlog, 1) . '/';


    foreach ($LesImages as $key => $NodeImg) {
        $IdImg = 10000 * intval($Page.$NumArt.$key);
        //$IdImg = $IdArt + 10000 + $key;
        
        $LienImage = $NodeImg->getAttribute('src');

        // On ne garde que la grande
        if (substr_count($LienImage, 'perlbal.hi-pi.com') and substr_count($LienImage, '/mn/')){
            $LienImage = str_replace('/mn/', '/gd/', $LienImage);
            //$NodeImg->setAttribute('src', $LienImage);
        }

        $NodeImg->setAttribute('classname', 'alignnone size-full wp-image-' . $IdImg);

        if (substr_count($LienImage, 'perlbal.hi-pi.com'))
            $NodeImg->setAttribute('width', '300');
        
        $NodeImg->setAttribute('src', $CheminDesImages.$DateDossier.'/'.basename($LienImage));
        
        $TabloImages[$IdImg] = $LienImage;
    }     
    
    
    if ($NumCom == 1){
        // Le texte de l'article
        //$TexteArticle = DOMinnerHTML($NodesArt->item(0));

        // Supprime les onclick des liens images et change le lien
        $LesLiens = $NodesArt->item(0)->getElementsByTagName('a');  
        foreach ($LesLiens as $key => $value) {
            $Href = $value->getAttribute('href');
            if (substr_count($Href, 'perlbal.hi-pi.com')){
                $value->removeAttribute('onclick');    
                $value->setAttribute('href', $CheminDesImages.$DateDossier.'/'.basename($Href));
            }

            // Liens vers une nouvelle fenetre
            $value->setAttribute("target", "_blank");
        }

        $ArticleFormate = DOMinnerHTML($NodesArt->item(0)); //die($ArticleFormate);


        // Les tags
        $TabloTags = array();
        $NodesTags = $finder->query("//*[contains(@class, 'tags')]");    
        if ($NodesTags->length > 0)
            $TabloTags = explode(', ', $NodesTags->item(0)->textContent);
        

        // L'article
        $XML .= '
        <item>
            <title>'.utf8_decode($Titre).'</title>
            <link>'.$NouveauBlog.'/?p='.$IdArt.'</link>
            <pubDate>'.DateCom2DateXML($Date, 2).'</pubDate>
            <dc:creator><![CDATA['.$VotreNom.']]></dc:creator>
            <guid isPermaLink="false">'.$NouveauBlog.'/?p='.$IdArt.'</guid>
            <description></description>
            <content:encoded><![CDATA['.html_entity_decode($ArticleFormate).']]></content:encoded>
            <excerpt:encoded><![CDATA[]]></excerpt:encoded>
            <wp:post_id>'.$IdArt.'</wp:post_id>
            <wp:post_date>'.DateCom2DateXML($Date).'</wp:post_date>
            <wp:post_date_gmt>'.DateCom2DateXML($Date).'</wp:post_date_gmt>
            <wp:comment_status>open</wp:comment_status>
            <wp:ping_status>open</wp:ping_status>
            <wp:post_name>'.utf8_decode(strtolower($Name)).'</wp:post_name>
            <wp:status>publish</wp:status>
            <wp:post_parent>0</wp:post_parent>
            <wp:menu_order>0</wp:menu_order>
            <wp:post_type>post</wp:post_type>
            <wp:post_password></wp:post_password>
            <wp:is_sticky>0</wp:is_sticky>
            <category domain="category" nicename="'.$Rubrique.'"><![CDATA['.$Rubrique.']]></category>';

        foreach ($TabloTags as $LeTag) {
            $XML .= '
                <category domain="post_tag" nicename="'.utf8_decode($LeTag).'"><![CDATA['.utf8_decode($LeTag).']]></category>';
        }

        $XML .= '
            <wp:postmeta>
                    <wp:meta_key>_edit_last</wp:meta_key>
                    <wp:meta_value><![CDATA[1]]></wp:meta_value>
            </wp:postmeta>
            <wp:postmeta>
                    <wp:meta_key>_edit_last</wp:meta_key>
                    <wp:meta_value><![CDATA[1]]></wp:meta_value>
            </wp:postmeta>';
    }
    

    // Les commentaires
    $NodesCom = $finder->query("//*[contains(@class, 'lire_commentaire_author')]");    
    
    // Il y a des comm, on recupere
    if ($NodesCom->length > 0){
    
        foreach ($NodesCom as $key => $NodeCom) {
            $NodeUserAvatar = $DecalCom==1 ? $finder->query("//*[contains(@class, 'lire_commentaire_author')]") : $finder->query("//*[contains(@class, 'user_avatar_text')]");
            //echo '-'.$NodeUserAvatar->item($key)->textContent.'-<br>';
            $NomEtDate      = $NodeUserAvatar->item($key)->textContent;
            $UrlCom         = $NodeUserAvatar->item($key)->getElementsByTagName('a')->length > 0 ? $NodeUserAvatar->item($key)->getElementsByTagName('a')->item(0)->getAttribute('href') : '';
            $NodeDateSeule  = $finder->query("//*[contains(@class, 'title-date')]");  
            $DateCom        = str_replace('posté le', '', $NodeDateSeule->item($key+$DecalCom)->textContent); //die('*'.$DateCom.'*');
            $NomCom         = trim(str_replace($DateCom, '', $NomEtDate));
            $DateCom        = DateCom2DateXML(trim($DateCom));
            $NodeComm       = $finder->query("//*[contains(@class, 'text_comment')]");  
            $Commentaire    = trim($NodeComm->item($key)->textContent);

            $MailCom = '';
            $SiteCom = $UrlCom; 
            if (substr($UrlCom, 0, 7) == 'mailto:'){
                $MailCom = substr($UrlCom, 7);
                $SiteCom = '';
            }

            //echo '.'.$NomCom.'.'.$MailCom.'.'.$SiteCom.'.'.$DateCom.'.'.$Commentaire.'.<br><br>'; 

            $IdImg = 10 * $NumCom + $key + 1;
            
            $XML .= '
                <wp:comment>
                    <wp:comment_id>'.$IdImg.'</wp:comment_id>
                    <wp:comment_author><![CDATA['.utf8_decode($NomCom).']]></wp:comment_author>
                    <wp:comment_author_email>'.$MailCom.'</wp:comment_author_email>
                    <wp:comment_author_url>'.$UrlCom.'</wp:comment_author_url>
                    <wp:comment_author_IP>::1</wp:comment_author_IP>
                    <wp:comment_date>'.$DateCom.'</wp:comment_date>
                    <wp:comment_date_gmt>'.$DateCom.'</wp:comment_date_gmt>
                    <wp:comment_content><![CDATA['.utf8_decode($Commentaire).']]></wp:comment_content>
                    <wp:comment_approved>1</wp:comment_approved>
                    <wp:comment_type></wp:comment_type>
                    <wp:comment_parent>0</wp:comment_parent>
                    <wp:comment_user_id>0</wp:comment_user_id>
                </wp:comment>';
        }        
        
        $NumCom++;  // Page de com suivante
    }

    // Plus de comm, on termine avec les photos
    else {
        $XML .= '
            </item>';
         

        // Les photos
        foreach ($TabloImages as $key=>$LienImage){    
            $TabUrl     = parse_url($LienImage);
            $FichSeul   = basename ($TabUrl["path"]);          
            $IdImg      = $key;//$IdArt + 10000 + $key;
            
            $XML .= '
                <item>
                    <title>'.utf8_decode($Titre).' '.$FichSeul.'</title>
                    <link>'.$NouveauBlog.'/?attachment_id='.$IdImg.'</link>
                    <pubDate>'.DateCom2DateXML($Date, 2).'</pubDate>
                    <dc:creator><![CDATA['.$VotreNom.']]></dc:creator>
                    <guid isPermaLink="false">'.$CheminDesImages.$DateDossier.'/'.$FichSeul.'</guid>
                    <description>'.utf8_decode($Titre).'</description>
                    <content:encoded><![CDATA[]]></content:encoded>
                    <excerpt:encoded><![CDATA[]]></excerpt:encoded>
                    <wp:post_id>'.$IdImg.'</wp:post_id>
                    <wp:post_date>'.DateCom2DateXML($Date).'</wp:post_date>
                    <wp:post_date_gmt>'.DateCom2DateXML($Date).'</wp:post_date_gmt>
                    <wp:comment_status>open</wp:comment_status>
                    <wp:ping_status>open</wp:ping_status>
                    <wp:post_name>'.$FichSeul.'</wp:post_name>
                    <wp:status>inherit</wp:status>
                    <wp:post_parent>'.$IdArt.'</wp:post_parent>
                    <wp:menu_order>0</wp:menu_order>
                    <wp:post_type>attachment</wp:post_type>
                    <wp:post_password></wp:post_password>
                    <wp:is_sticky>0</wp:is_sticky>
                    <wp:attachment_url>'.$LienImage.'</wp:attachment_url>
                    <wp:postmeta>
                            <wp:meta_key>_wp_attached_file</wp:meta_key>
                            <wp:meta_value><![CDATA[Import/'.$FichSeul.']]></wp:meta_value>
                    </wp:postmeta>
                    <wp:postmeta>
                            <wp:meta_key>_wp_attachment_metadata</wp:meta_key>
                            <wp:meta_value><![CDATA[]]></wp:meta_value>
                    </wp:postmeta>
                </item>';
        }
        
        $NumCom = 1;
        
        $NumArt++;  // Article suivant
        if ($NumArt>4){
            $NumArt = 0;
            $Page++;
        }        
        
        
        // Si on est proche de 2Mo, on change de fichier
        if (filesize($FichierXML) > 1900000){
            $XML .= '
                    </channel>
                </rss>';  
            
            $FichierNum++;
            $ChangeFichier = 1;
        }
    }
    

    //echo '.'.$Titre.'.<br>.'.$Date.'.<br>.'.$Rubrique.'.<br><br><br>'.$ArticleFormate;

   

    // Copie dans le fichier xml
    $FileXML = fopen($FichierXML, 'a');
    fwrite($FileXML, $XML);
    fclose($FileXML);    
   
    ?>
    <form method="post" action="ParcoursPages.php" name="FormArt" target="_parent">
        <input type="hidden" name="Page" value="<?php echo $Page; ?>">
        <input type="hidden" name="NumArt" value="<?php echo $NumArt; ?>">
        <input type="hidden" name="NumCom" value="<?php echo $NumCom; ?>">
        <input type="hidden" name="AncienBlog" value="<?php echo $AncienBlog; ?>">
        <input type="hidden" name="NouveauBlog" value="<?php echo $NouveauBlog; ?>">
        <input type="hidden" name="VotreNom" value="<?php echo $VotreNom; ?>">
        <input type="hidden" name="FichierXML" value="<?php echo $FichierXML; ?>">
        <input type="hidden" name="FichierNum" value="<?php echo $FichierNum; ?>">
        <input type="hidden" name="ChangeFichier" value="<?php echo $ChangeFichier; ?>">
        <input type="hidden" name="Type" value="<?php echo $Type; ?>">
    </form>
    <script type="text/javascript">
        setTimeout("FormArt.submit()", 500);
    </script>
    <?php    
}
else
    echo 'ERREUR : pas de lien de page';
