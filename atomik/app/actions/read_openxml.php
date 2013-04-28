<?php
require_once('openxml.class.php');

$documents = array('sample1.docx', 'sample1.xlsx');

foreach ($documents as $document) {

   echo "<b><u>$document</u></b><br/>";
   
   try  {

      $mydoc = OpenXMLDocumentFactory::openDocument($document);
   
      echo '<br/><i>Metadonnées :</i><br/><br/>';
      echo 'Créateur: ' . $mydoc->getCreator() . '<br/>';
      echo 'Sujet: ' . $mydoc->getSubject() . '<br/>';
      echo 'Mots-clés: ' . $mydoc->getKeywords() . '<br/>';
      echo 'Description: ' . $mydoc->getDescription() . '<br/>';
      echo 'Date de création : ' . $mydoc->getCreationDate() . '</br>';
      echo 'Date de dernière modification : ' . $mydoc->getLastModificationDate() . '<br/>';
      echo 'Modifié en dernier par: ' . $mydoc->getLastWriter() . '<br/>';
      echo 'Révision: ' . $mydoc->getRevision() . '<br/>';
         
      echo '<br/><i>Propriétés du document:</i><br/><br/>';
      
      echo 'Généré par: ' . $mydoc->getApplication() . '<br/>';
   
      $document_class = get_class($mydoc); 
      
      if ($document_class == 'WordDocument') {
      
   
         echo 'Nombre de paragraphes: ' . $mydoc->getNbOfParagraphs() . '<br />';
         echo 'Nombre de caractères: ' . $mydoc->getNbOfCharacters() . '<br />';
         echo 'Nombre de caractères (avec les espaces): ' . $mydoc->getNbOfCharactersWithSpaces() . '<br/>';
         echo 'Nombre de pages: ' . $mydoc->getNbOfPages() . '<br/>';
         echo 'Nombre de mots: ' . $mydoc->getNbOfWords() . '<br/>';
         
      }
      
      echo '<br/><i>Aperçu du document:</i> <br/>';
      echo $mydoc->getHTMLPreview();
   
   }
   catch (OpenXMLFatalException $e) {
   
      echo $e->getMessage();
   
   }
   echo '<br/><br/>';
   
}
