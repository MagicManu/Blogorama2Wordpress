<?php
require 'Fonctions.php';

$ListeDomaines = array(
    'artblog.fr',
    'auto-blog.fr',
    'monbebeblog.com',
    'blogspace.fr',
    'bricoblog.fr',
    'cuisineblog.fr',
    'designblog.fr',
    'blogparty.fr',
    'footblog.fr',
    'blogourt.fr',
    'blogzoom.fr',
    'gamingblog.fr',
    'musicblog.fr',
    'sportblog.fr',
    'travelblog.fr',
    'blog.toutlecine.com',
    'anous.fr'
);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <title>Convertion d'un blog Blogorama vers un fichier XML pour Wordpress</title>    
        <style>
            BODY {
                margin: 0px;
                font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
                font-size: 10px;
            }
        </style>
    </head>
    <body>
        
        <form method="post" action="ParcoursPages.php" target="IfrPages">
        <table align="center" width="900">
            <tr height="60">
                <td colspan="4" align="center"><b>Convertion d'un blog Blogorama vers un fichier XML pour Wordpress</b></td>
            </tr>
            <tr height="60">
                <td colspan="4" align="center">Les explications sur <a href="http://blog.magicmanu.com/blogorama-vers-worpress/" target="_blank">http://blog.magicmanu.com/blogorama-vers-worpress/</a></td>
            </tr>            
            <tr>
                <td>Adresse du blog à récupérer :</td>
                <td>
                    http://
                    <input type="text" name="NomBlog" value="monblog" style="width:100px;">
                    <select name="Domaine">
                        <?php
                        foreach ($ListeDomaines as $Dom) {
                            $Sel = $Dom == 'bricoblog.fr' ? ' Selected' : '';
                            echo '<option value="'.$Dom.'"'.$Sel.'>'.$Dom."</option>\n";
                        }
                        ?>
                    </select>
                </td>
                <td>Votre Prénom :</td>
                <td><input type="text" name="VotreNom" value="Manu"></td>
            </tr>
            <tr>
                <td>Adresse du nouveau blog :</td>
                <td><input type="text" name="NouveauBlog" value="http://monblog.wordpress.com" style="width:250px;"></td>
                <td>Votre Email :</td>
                <td><input type="text" name="VotreEmail" value="votre@email.com"></td>
            </tr>
            <tr>
                <td>Type du nouveau blog :</td>
                <td colspan="3">
                    <select name="Type" style="width:350px;">
                        <option value="com" SELECTED>Compte créé sur wordpress.com (gratuit ou payant)
                        <option value="org">Blog hébergé sur votre serveur (en PHP)
                    </select>
                </td>
            </tr>
            <tr height="30">
                <td align="center" colspan="4"><input type="submit" value="Lancer"><td>
            </tr>
        </table>
        </form>

        <center>
            <iframe name="IfrPages" width="900" height="250" frameborder="1" src="Readme.html"></iframe>
            <br><br>
            Compatible avec les blogs : <?php echo implode(', ', $ListeDomaines); ?>            
        </center>
    </body>
</html>
