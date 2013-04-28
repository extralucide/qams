<?php
require_once('openxml.class.php');

$documents = array('sample1.docx', 'sample1.xlsx');

foreach ($documents as $document) {

   echo "<b><u>$document</u></b><br/>";
   
   try  {

      $mydoc = OpenXMLDocumentFactory::openDocument($document);
   
      echo '<br/><i>Metadonn�es :</i><br/><br/>';
      echo 'Cr�ateur: ' . $mydoc->getCreator() . '<br/>';
      echo 'Sujet: ' . $mydoc->getSubject() . '<br/>';
      echo 'Mots-cl�s: ' . $mydoc->getKeywords() . '<br/>';
      echo 'Description: ' . $mydoc->getDescription() . '<br/>';
      echo 'Date de cr�ation : ' . $mydoc->getCreationDate() . '</br>';
      echo 'Date de derni�re modification : ' . $mydoc->getLastModificationDate() . '<br/>';
      echo 'Modifi� en dernier par: ' . $mydoc->getLastWriter() . '<br/>';
      echo 'R�vision: ' . $mydoc->getRevision() . '<br/>';
         
      echo '<br/><i>Propri�t�s du document:</i><br/><br/>';
      
      echo 'G�n�r� par: ' . $mydoc->getApplication() . '<br/>';
   
      $document_class = get_class($mydoc); 
      
      if ($document_class == 'WordDocument') {
      
   
         echo 'Nombre de paragraphes: ' . $mydoc->getNbOfParagraphs() . '<br />';
         echo 'Nombre de caract�res: ' . $mydoc->getNbOfCharacters() . '<br />';
         echo 'Nombre de caract�res (avec les espaces): ' . $mydoc->getNbOfCharactersWithSpaces() . '<br/>';
         echo 'Nombre de pages: ' . $mydoc->getNbOfPages() . '<br/>';
         echo 'Nombre de mots: ' . $mydoc->getNbOfWords() . '<br/>';
         
      }
      
      echo '<br/><i>Aper�u du document:</i> <br/>';
      echo $mydoc->getHTMLPreview();
   
   }
   catch (OpenXMLFatalException $e) {
   
      echo $e->getMessage();
   
   }
   echo '<br/><br/>';
   
}
