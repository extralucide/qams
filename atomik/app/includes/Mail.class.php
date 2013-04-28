<?php
define('ENC_NONE', 1725);
define('ENC_QUOTED_PRINTABLE', 1726);
define('ENC_BASE64', 1727);
define('ENC_IDENTITY_7BIT', 1728);
define('ENC_IDENTITY_8BIT', 1729);
define('ENC_IDENTITY_BINARY', 1730);
define('ENC_EXTENSION', 1731);
define('EMBED_ATTACHMENT', 1454);
define('EMBED_OBJECT', 1453);
	
class Mail {
	const html_signature_link = '<table><tr><td><font size=1 color=#808080 face="Arial"><b><u>ECE</u></b><br>AIRCRAFT SYSTEMS</font>
								<td><img src="cid:_2_032EAAC4032EA6F0003D60EAC12579E3" alt="logo"><tr>
								<td colspan=2><font size=1 face="Arial"><b>Olivier Appere</b><br></font><tr>
								<td colspan=2><font size=1 color=#808080 face="Arial">129 Boulevard Davout - Paris - France<br>Tel: 01 56 06 1104<br>
								</font><a href=mailto:Olivier.Appere@zodiacaerospace.com><font size=1 color=#808080 face="Arial">Olivier.Appere@zodiacaerospace.com</font></a>
								<font size=1 color=#808080 face="Arial"><br>
								</font><a href=http://www.zodiacaerospace.com/><font size=1 color=#808080 face="Arial">http://www.zodiacaerospace.com</font></a></table><br>';
	private $project_id;
	private $sub_project_id;	
	private $document;
	private $from;
	private $session;
	private $database;
	private $password;
	private $access_lotus_fail;
	private $context;
	private $subject;
	private $recipients;
	private $body;
	private $archive;
	private $email_object;
	private $body_object;
	private $parent_object;
	private $child_object;
	private $stream;	
	
	public function __construct($context=null) {
		$user = new User;
		if($context != null){
			$this->context = $context;
			$this->project_id = isset($context['project_id'])?$context['project_id']:"";
			$this->sub_project_id = isset($context['sub_project_id'])?$context['sub_project_id']:"";
			if (isset($context['user_logged_id'])){
				$this->setFrom($user->getEmail($context['user_logged_id']));
				$this->database = $user->getDatabase($context['user_logged_id']);
				$this->password = $user->getPassword($context['user_logged_id']);				
			}
			else {
				$this->setFrom($user->getEmail(1));
				$this->database = $user->getDatabase(1);
				$this->password = $user->getPassword(1);
			}
		}
		else {
			$this->context = null;
			$this->setFrom($user->getEmail(1));
			$this->database = $user->getDatabase(1);
			$this->password = $user->getPassword(1);		
		}
		// $this->setFrom($user->getEmail(1));
		// $this->database = $user->getDatabase(1);
		// $this->password = $user->getPassword(1);
		$this->archive=false;
		$this->setCopy("");
		$this->body = "";
		$this->subject = "";
		if (class_exists("COM")) {
			$this->session = new COM( "Lotus.NotesSession" ) or die("Can't init Notes Session"); 
			try {
				$this->session->Initialize($this->password);
				$access_lotus=true;
			} catch (Exception $e) {
				echo "<p>Fail to initialize session (wrong password ?).</p>";
				///echo "<p>".$e->getMessage()."</p>";
				$access_lotus=false;
			}
			if ($access_lotus){
				//print "Current user: " . $this->session->CommonUserName . "<br/>";
				//print "Server name: " . $this->session->ServerName . "<br/>";
				//print "User ID: " .$this->session->UserName . "<br/>";
				//Show the name of the current Notes user
				/* print "Current user: " . $session->CommonUserName . "\n\n"; */
				//Get database handle http://spar-dom1.in.com
				/* 
				 * Database name: mail/oappere.nsf 
				 * Server name: CN=SPAR-DOM1/OU=BAIRSYST/O=ZODIAC
				*/
				$db = $this->session->getDatabase( "CN=SPAR-DOM1/OU=BAIRSYST/O=ZODIAC", $this->database );
				try {
					$this->session -> ConvertMime = FALSE; 
					$this->document = $db -> CreateDocument();	
					$access_lotus=true;					
				} catch (Exception $e) {
					//echo $e->getMessage();
					unset($db);
					echo "<p>Fail to connect to database on Zodiac server.</p>";
					$db = $this->session->getDatabase( "", "archive\a_oapper.nsf" );
					$this->archive=true;
					try {
						$this->session -> ConvertMime = FALSE; 
						$this->document = $db -> CreateDocument();	
						$access_lotus=true;					
					} catch (Exception $e) {	
						unset($db);
						echo "<p>Fail to connect to archive database.</p>";					
						$access_lotus=false;
					}	
				}
			}
		}else{
			$db=false;
			$access_lotus=false;
		}
		if ($access_lotus){
			/* Lotus access succeeded */
			$this->access_lotus_fail= false;
			$this->email_object = $this->getDocument();
			$this->email_object->ReplaceItemValue("Form","Memo"); 
			$this->body_object = $this->email_object -> CreateMIMEEntity(); 

		}
		else {
			$this->access_lotus_fail= true;
		}
	}	
	public function __destruct(){
		if ($this->session != null){
			$this->session -> ConvertMime = TRUE; 
			$this->session = null;
		}
	}
	public function setSubject($subject){
		Atomik::needed('Logbook.class');
		$logbook = new Logbook(&$this->context);
		$this->subject = $logbook->board." ".$subject;
		// if($this->context != null){
			// Atomik::needed('Project.class');
			// $project = new Project($this->context);
			// $project_name = $project->get_project_name($this->project_id);
			// $sub_project_name = $project->get_sub_project_name($this->sub_project_id);
			// $this->subject = $project_name." ".$sub_project_name." ".$subject;
		// }
		// else {
			// $this->subject = $logbook->board." ".$subject;
		// }
	}
	public function getSubject(){	
		return($this->subject);
	}
	public function setBody($body){	
		$this->body = $body;
	}
	public function getBody(){	
		return($this->body);
	}	
	function validEmail($inputEmail)
	{
		if(filter_var($inputEmail, FILTER_VALIDATE_EMAIL) == false){
			$result=false;
		}
		else{
			$result=true;
		}
		return($result);
	}
	public function setRecipients($recipients){
		$error="";
		$this->recipients = "";
		if ($recipients != null){
			foreach ($recipients as $email){
				if($this->validEmail($email)){
					$this->recipients .= $email.",";
				}
				else {
					$this->recipients .= $email.",";
					$error = "<p>Warning email ".$email." is not valid.</p>";
				}
			}
			/* Remove last separator */
			$this->recipients = preg_replace("/,$/i","",$this->recipients);
		}
		return($error);
	}
	public function setCopy($copy){
		$this->copy = "";
		if ($copy != null){
			foreach ($copy as $email){
				$this->copy .= $email.";";
			}
		}
	}	
	public function setFrom($from){
		$this->from = $from;
	}		
	public function getRecipients(){
		return($this->recipients);		
	}	
	public function getCopy(){
		return($this->copy);		
	}	
	public function getFromWho(){
		return($this->from);		
	}
	public function getDocument(){
		return($this->document);
	}
	public function getAccess(){
		return(!$this->access_lotus_fail);
	}
	public function getArchive(){
		return($this->archive);
	}	
	public function createStream(){
		return($this->session -> CreateStream());
	}
	public function createHeader(){
		$bodyHeader = $this->body_object -> CreateHeader("Content-Type"); 
		$bodyHeader -> SetHeaderVal("multipart/related");
		$bodyHeader = $this->body_object -> CreateHeader("Subject"); 
		$bodyHeader -> SetHeaderVal($this->subject); 
		$bodyHeader = $this->body_object -> CreateHeader("To"); 
		$bodyHeader -> SetHeaderVal($this->getRecipients());
		$bodyHeader = $this->body_object -> CreateHeader("Cc"); 
		$bodyHeader -> SetHeaderVal($this->copy);
		//$bodyHeader = $body -> CreateHeader("From"); 
		//$bodyHeader -> SetHeaderVal($this->from);		
	}	
	public function attach($filename,$location=null){
		$obAttachment = $this->email_object->CreateRichTextItem($filename);
		if ($location == null){
			$location = dirname(__FILE__).DIRECTORY_SEPARATOR.
						"..".DIRECTORY_SEPARATOR.
						"..".DIRECTORY_SEPARATOR.
						"..".DIRECTORY_SEPARATOR.
						"result".DIRECTORY_SEPARATOR.				
						$filename;
		}
		// else{
			// $location = dirname(__FILE__).DIRECTORY_SEPARATOR.
									// "..".DIRECTORY_SEPARATOR.
									// "..".DIRECTORY_SEPARATOR.
									// "..".DIRECTORY_SEPARATOR.$filename;
		// }
		$EmbedObject = $obAttachment->EmbedObject(EMBED_ATTACHMENT,"",$location);
	}
	public function writeBody(){
		$this->stream ->WriteText($this->getBody());
		/* see 1729 quoted-printable by gmail */
		$this->child_object -> SetContentFromText($this->stream,"text/HTML;charset=UTF-8",ENC_NONE);
		$this->stream ->close;
	}
	public function createParent(){
		$this->parent_object = $this->body_object->CreateParentEntity();
		/* First child */
		$this->stream = $this -> CreateStream();
		$this->child_object = $this->parent_object->CreateChildEntity($this->body_object);	
	}
	public function create_child($file,$id,$child=""){
		if ($child !="") {
			$child_second = $this->parent_object->CreateChildEntity($child);
		}else{
			$child_second = $this->parent_object->CreateChildEntity($this->body_object);
		}
		//echo "</br>TEST:".$file."</br>";
		require_once("Tool.class.php");
		$data = Tool::base64_encode_image($file,true);
		$this->stream ->WriteText($data);
		$child_second -> SetContentFromText($this->stream,'image/jpeg; name="'.$file.'"',ENC_BASE64); // ENCODE_NONE
		$this->stream->close;
		$bodyHeader = $child_second -> CreateHeader("Content-ID"); 
		$bodyHeader -> SetHeaderVal("<".$id.">");
		return ($child_second);
	}	
	public function save(){
		$result = $this->email_object -> Save(true,false);
		return($result);
	}
	public function send(){
		$result = $this->email_object -> Send(False);
		return($result);		
	}
}