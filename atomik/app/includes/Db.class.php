<?php
/**
 * Description of Dbclass
 *
 * @author olivier
 */
function ob_file_callback($buffer)
{
  global $ob_file;
  fwrite($ob_file,$buffer);
} 
function manage_log($buffer){
	$filename = dirname(__FILE__).DIRECTORY_SEPARATOR.
			"..".DIRECTORY_SEPARATOR.
			"..".DIRECTORY_SEPARATOR.					
			"..".DIRECTORY_SEPARATOR.A('db_config/log');
	$monfichier = fopen($filename, 'a');
	fputs($monfichier, $buffer."\n");
	fclose($monfichier);	
}
class Db {
    private $db_server;
    private $db_user;
    private $db_pass;
    private $dsn;	
	private $dbh;
	private	$pdo;
	private static $os_system;
	private static $os_server_system;	
    public 	$db_select;
	public  $backup_dir;
    private $bin_path;
    private $command;
    public 	$backup_filename;
    public 	$taille;

    public function db_query ($query) {
		// try {
			$query_result = $this->dbh->query($query);
		// }
		// catch (Exception $e) {

		// }
        if($query_result === FALSE) {
            print "Could not execute query :";
			var_dump($this->dbh->getErrorInfo());
			echo "<br/>";
			echo "Debug MySQL request:<br/>".$query."<br/>"; 
            exit();
        }
        else
            return ($query_result);
    }
	public function pdo_query($query,$stay_connect=false){
		if ($this->pdo === null){
			try { 
				$this->pdo = new PDO($this->dsn, $this->db_user, $this->db_pass);
				$this->pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_WARNING);
				$this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
			} 
			catch (PDOException $e) {
				die( "Erreur ! : " . $e->getMessage() );			
			}
		}
		// $this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
		$statement = $this->pdo->query($query);
		// ob_start("manage_log");
		// $text = "Query executed at ".date('H:i:s')." on ".date('Y/m/d').":<br/>".$query;
		// echo $text;
		// ob_end_clean();
		if($stay_connect === false){
			$this->pdo = null;
		}
		return($statement);
	}
	public function quote($query){
		return($this->pdo->quote($query));
	}	
	public function exec($query){
		return($this->pdo->exec($query));
	}	
	public function lastInsertId(){
		return($this->pdo->lastInsertId());
	}
	public function fetchAll($query){
		return($this->pdo->query($query)->fetchAll());
	}	
    public function db_prepare ($query) {
		$result = $this->dbh->prepare($query);
        return ($result);
    }
    public function db_execute ($query) {
		$result = $this->dbh->execute($query);
		return($result);
    }		
	public function db_insert ($table,$data){
		$result = $this->dbh->insert ($table,$data);
		return($result);		
	}
	public function update($table, $data, $where){
		$result = $this->dbh->update($table, $data, $where);
		return($result);
	}
	public function findAll($table, $where = null, $orderBy = null, $limit = null, $fields = null){
		return($this->dbh->findAll($table, $where = null, $orderBy = null, $limit = null, $fields = null));
	}	
    /* This function connects to the database specified in the argument */
	public function db_create_qams_db(){
		$this->dsn = 'mysql:host='.$this->db_server;
		$db = $this->db_select;
		$user = $this->db_user;
		$pass = $this->db_pass;
		/* create database qams */
$sql_query = <<<____SQL
	CREATE DATABASE IF NOT EXISTS `$db`;
		CREATE USER '$user'@'localhost' IDENTIFIED BY '$pass';
		GRANT ALL ON `$db`.* TO '$user'@'localhost';
		FLUSH PRIVILEGES;
____SQL;
$this->pdo_query($sql_query);
require_once("Atomik/Db/Instance.php");
$this->dsn = 'mysql:host='.$this->db_server.';dbname='.$this->db_select;
$this->dbh = new Atomik_Db_Instance($this->dsn, $this->db_user, $this->db_pass);
$this->dbh->connect();
/* create user table */
$sql_query = <<<____SQL
		CREATE TABLE IF NOT EXISTS `bug_users` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `fname` text NOT NULL,
		  `lname` text CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
		  `username` text NOT NULL,
		  `password` varchar(16) NOT NULL DEFAULT '',
		  `function` text NOT NULL,
		  `enterprise_id` int(11) NOT NULL,
		  `telephone` tinytext NOT NULL,
		  `email` text NOT NULL,
		  `is_admin` tinyint(4) NOT NULL DEFAULT '0',
		  `last_logged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  `dismissed` int(11) NOT NULL,
		  `service_id` int(11) NOT NULL,
		  `department_id` int(11) NOT NULL,
		  `lotus_database` text NOT NULL,
		  `overview` longtext NOT NULL,
		  `property` text NOT NULL,
		  `folder` text NOT NULL,
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
____SQL;
$this->pdo_query($sql_query);

/* create users's table */
$sql_query = file_get_contents("../sql/users.sql");
$this->pdo_query($sql_query);

$sql_query = <<<____SQL
CREATE TABLE IF NOT EXISTS `user_join_function` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `function_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
____SQL;
$this->pdo_query($sql_query);

$sql_query = <<<____SQL
CREATE TABLE IF NOT EXISTS `user_join_project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
____SQL;
$this->pdo_query($sql_query);

$sql_query = <<<____SQL
CREATE TABLE IF NOT EXISTS `user_join_review` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL,
  `copy` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
____SQL;
$this->pdo_query($sql_query);

/* create company table */
$sql_query = <<<____SQL
		CREATE TABLE IF NOT EXISTS `enterprises` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` text NOT NULL,
		   `description` text NOT NULL,
		  `type_id` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=latin1;
____SQL;
$this->pdo_query($sql_query);
$sql_query = file_get_contents("../sql/companies.sql");
$this->pdo_query($sql_query);
/* create department table */
$sql_query = <<<____SQL
		CREATE TABLE IF NOT EXISTS `departments` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` text NOT NULL,
		  `acronym` text NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
____SQL;
$this->pdo_query($sql_query);
$sql_query = <<<____SQL
--
-- Dumping data for table `departments`
--

	LOCK TABLES `departments` WRITE;
	INSERT INTO `departments` VALUES 
	(1,'Quality Department','DQ'),
	(2,'System Group','GS'),
	(3,'Electronic Group','GE'),
	(4,'Cockpit Group','GEC'),
	(5,'Contactor Group','GC'),
	(6,'Group','GVA'),	
	(7,'Technical Department','DT');
	UNLOCK TABLES;
____SQL;
$this->pdo_query($sql_query);
/* create aircrafts table */
$sql_query = <<<____SQL
			CREATE TABLE IF NOT EXISTS `aircrafts` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` text NOT NULL,
			  `description` text NOT NULL,
              `company_id` int(11) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
____SQL;
$this->pdo_query($sql_query);
$sql_query = file_get_contents("../sql/aircrafts.sql");
$this->pdo_query($sql_query);
/* create projects table */
$sql_query = <<<____SQL
				CREATE TABLE IF NOT EXISTS `projects` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `project` text COLLATE latin1_general_ci NOT NULL,
				  `description` text COLLATE latin1_general_ci NOT NULL,
				  `ata` int(11) NOT NULL,
				  `aircraft_id` int(11) NOT NULL,
				  `folder` text COLLATE latin1_general_ci NOT NULL,
				  `workspace` text COLLATE latin1_general_ci NOT NULL,
				  PRIMARY KEY (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
____SQL;
$this->pdo_query($sql_query);
$sql_query = file_get_contents("../sql/projects.sql");
$this->pdo_query($sql_query);
/* create sub projects table */
$sql_query = <<<____SQL
                CREATE TABLE IF NOT EXISTS `lrus` (
                                       `id` int(11) NOT NULL AUTO_INCREMENT,
                                       `lru` text COLLATE latin1_general_ci NOT NULL,
                                       `project` int(11) NOT NULL,
                                       `description_lru` text COLLATE latin1_general_ci NOT NULL,
                                       `parent_id` int(11) NOT NULL,
                                       `abstract` text COLLATE latin1_general_ci NOT NULL,
                                       `part_number` text COLLATE latin1_general_ci NOT NULL,
                                       `dal` text COLLATE latin1_general_ci NOT NULL,
                                       `scope_id` int(11) NOT NULL,
                                       `manager_id` int(11) NOT NULL,
                                       PRIMARY KEY (`id`)
                                       ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;        
____SQL;
$this->pdo_query($sql_query);
$sql_query = file_get_contents("../sql/eqpt.sql");
$this->pdo_query($sql_query);
/* create reviews/meeting table */
$sql_query = <<<____SQL
					CREATE TABLE IF NOT EXISTS `reviews` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `title` text NOT NULL,
				  `attendee` text NOT NULL,
				  `managed_by` text NOT NULL,
				  `aircraft` int(11) NOT NULL,
				  `project` int(11) NOT NULL,
				  `lru` int(11) NOT NULL,
				  `type` int(11) NOT NULL,
				  `objective` text NOT NULL,
				  `description` longtext NOT NULL,
				  `comment` longtext NOT NULL,
				  `status` int(11) NOT NULL,
				  `date` date NOT NULL,
				  `mom_id` int(11) NOT NULL,
				  `event` int(11) NOT NULL,
				  `previous_id` int(11) NOT NULL,
				  `date_end` date NOT NULL,
				  `subject` text NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
____SQL;
$this->pdo_query($sql_query);
/* create reviews attachment table */
$sql_query = <<<____SQL
			CREATE TABLE IF NOT EXISTS `reviews_attachment` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `data_id` int(11) NOT NULL,
			  `ext` text NOT NULL,
			  `real_name` text NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
____SQL;
$this->pdo_query($sql_query);			
/* create reviews type table */	
$sql_query = <<<____SQL
				CREATE TABLE IF NOT EXISTS `review_type` (
				  `id` int(11) NOT NULL auto_increment,
				  `type` text NOT NULL,
				  `description` text NOT NULL,
				  `scope_id` int(11) NOT NULL,
				  `objectives` text NOT NULL,
				  `inputs` text NOT NULL,
				  `activities` text NOT NULL,
				  `outputs` text NOT NULL,
				  `schedule` text NOT NULL,
				  `company_id` tinyint(4) NOT NULL,
				  `last_item` int(11) NOT NULL,
				  KEY `id` (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=52 ;
____SQL;
$this->pdo_query($sql_query);
/* insert reviews type list */
$sql_query = file_get_contents("../sql/reviews_type.sql");
$this->pdo_query($sql_query);

/* checklists */
$sql_query = <<<____SQL
--
-- Table structure for table `checklist_questions`
--

CREATE TABLE IF NOT EXISTS `checklist_questions` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `review_id` int(11) NOT NULL,
				  `tag` text NOT NULL,
				  `question` text NOT NULL,
				  `item_order` int(11) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
____SQL;
$this->pdo_query($sql_query);
$sql_query = file_get_contents("../sql/questions.sql");
$this->pdo_query($sql_query);

/* insert status table */
$sql_query = <<<____SQL
					CREATE TABLE IF NOT EXISTS `bug_status` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `name` text NOT NULL,
				  `description` text NOT NULL,
				  `type` mediumtext NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM AUTO_INCREMENT=53 DEFAULT CHARSET=latin1;
____SQL;
$this->pdo_query($sql_query);
/* import status list */
$sql_query = file_get_contents("../sql/status.sql");
$this->pdo_query($sql_query);
/* insert category table */
$sql_query = <<<____SQL
			CREATE TABLE IF NOT EXISTS `bug_category` (
			  `id` smallint(6) NOT NULL AUTO_INCREMENT,
			  `name` text NOT NULL,
			  `description` text,
			  `type` text NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
____SQL;
$this->pdo_query($sql_query);
/* import category list */
$sql_query = file_get_contents("../sql/categories.sql");
$this->pdo_query($sql_query);
/* insert actions table */
$sql_query = <<<____SQL
					CREATE TABLE IF NOT EXISTS `actions` (
				  `id` smallint(6) NOT NULL AUTO_INCREMENT,
				  `project` int(11) NOT NULL,
				  `context` text,
				  `review` int(11) NOT NULL,
				  `lru` int(11) DEFAULT NULL,
				  `posted_by` int(11) DEFAULT NULL,
				  `assignee` int(11) NOT NULL,
				  `Description` longtext,
				  `criticality` int(11) DEFAULT NULL,
				  `status` int(11) DEFAULT NULL,
				  `date_open` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  `date_expected` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
				  `date_closure` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
				  `comment` text NOT NULL,
				  `duration` int(11) NOT NULL,
				  KEY `id` (`id`)
				) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
____SQL;
$this->pdo_query($sql_query);
/* table of attachemnts*/
$sql_query = <<<____SQL
--
-- Table structure for table `action_attachment`
--

CREATE TABLE IF NOT EXISTS `actions_attachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_id` int(11) NOT NULL,
  `name` text NOT NULL,
  `real_name` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
____SQL;
$this->pdo_query($sql_query);			
/* insert severity table */
$sql_query = <<<____SQL
				CREATE TABLE IF NOT EXISTS `bug_criticality` (
			  `level` smallint(6) NOT NULL AUTO_INCREMENT,
			  `name` text NOT NULL,
			  `description` text,
			  `type` mediumtext NOT NULL,
			  KEY `level` (`level`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
____SQL;
$this->pdo_query($sql_query);
$sql_query = file_get_contents("../sql/severity.sql");
$sql_query = <<<____SQL
		INSERT INTO `bug_criticality` (`level`, `name`, `description`, `type`) VALUES
		(3, 'Severe', 'work product is still usable in some cases, data could be lost or corrupted', 'spr'),
		(2, 'Medium', 'he work product is usable and fairly stable, but some functionality is not available or creates problems', 'spr'),
		(1, 'Minor', 'the work product is usable and stable', 'spr'),
		(4, 'Showstopper', 'user cannot continue to use the work product', 'spr'),
		(10, 'Action', 'An action is an assignment to an organization or person with a date for completion to correct a finding, error, or deficiency identified when conducting a review. ', 'action'),
		(11, 'Finding', 'A finding is the identification of a failure to show compliance to one or more of the means of compliance objectives.', 'action'),
		(12, 'Observation', 'An observation is the identification of a potential life cycle process improvement. An observation is not a compliance issue and does not need to be addressed before approval.', 'action'),
		(13, 'Major', 'Used in Airbus proof reading', 'Airbus'),
		(14, 'Task', NULL, 'action'),
		(15, 'Issue', 'An issue is a concern not specific to software compliance or process improvement but may be a safety, system, program management, organizational, or other concern that is detected during a software review.', 'action'),
		(16, 'Low', 'Low priority', 'data'),
		(17, 'Medium', 'Medium priority', 'data'),
		(18, 'High', 'High priority', 'data');
____SQL;
$this->pdo_query($sql_query);
$this->pdo_query($sql_query);
/* insert remarks table */
$sql_query = <<<____SQL
			CREATE TABLE IF NOT EXISTS `bug_messages` (
			  `category` int(11) NOT NULL DEFAULT '0',
			  `criticality` int(11) NOT NULL DEFAULT '0',
			  `application` int(11) NOT NULL DEFAULT '0',
			  `subject` text NOT NULL,
			  `description` longtext NOT NULL,
			  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  `posted_by` text NOT NULL,
			  `status` int(11) NOT NULL,
			  `paragraph` text NOT NULL,
			  `line` text NOT NULL,
			  `justification` text NOT NULL,
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `reply_id` int(11) NOT NULL DEFAULT '0',
			  `action_id` int(11) NOT NULL,
			  KEY `id` (`id`),
			  FULLTEXT KEY `description` (`description`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
____SQL;
$this->pdo_query($sql_query);
/* insert peer review tables */
$sql_query = <<<____SQL
            CREATE TABLE IF NOT EXISTS `peer_review_location` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `data_id` int(11) NOT NULL,
            `name` text NOT NULL,
            `ext` text NOT NULL,
            `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `nb_remarks` int(11) NOT NULL,
            `open_remarks` int(11) NOT NULL,
            `type_id` int(11) NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
____SQL;
$this->pdo_query($sql_query);
/* insert peer review types */
$sql_query = file_get_contents("../sql/peer_review_type.sql");
$this->pdo_query($sql_query);
/* insert data table */
$sql_query = <<<____SQL
			CREATE TABLE IF NOT EXISTS `bug_applications` (
                                                           `id` smallint(6) NOT NULL AUTO_INCREMENT,
                                                           `project` int(11) NOT NULL,
                                                           `application` text NOT NULL,
                                                           `description` text NOT NULL,
                                                           `abstract` text NOT NULL,
                                                           `version` text NOT NULL,
                                                           `lru` int(11) NOT NULL,
                                                           `type` int(11) NOT NULL,
                                                           `status` int(11) NOT NULL,
                                                           `location` text NOT NULL,
                                                           `peer_review` text NOT NULL,
                                                           `date_published` date NOT NULL,
                                                           `date_review` date NOT NULL,
                                                           `author_id` int(11) NOT NULL,
                                                           `last_read` date NOT NULL,
                                                           `previous_data_id` int(11) NOT NULL,
                                                           `acceptance` longtext NOT NULL,
                                                           `password` text NOT NULL,
                                                           `keywords` text NOT NULL,
														   `priority_id` int(11) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
____SQL;
$this->pdo_query($sql_query);

/* import documents */
$sql_query = file_get_contents("../sql/documents.sql");
$this->pdo_query($sql_query);

/* import document types */
$sql_query = <<<____SQL
--
-- Table structure for table `data_cycle_type`
--

CREATE TABLE IF NOT EXISTS `data_cycle_type` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `description` text,
  `cc` int(11) NOT NULL,
  `comment` text NOT NULL,
  `group_id` int(11) NOT NULL,
  `review` int(11) NOT NULL DEFAULT '1',
  KEY `level` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
____SQL;
$this->pdo_query($sql_query);
$sql_query = file_get_contents("../sql/data_types.sql");
$this->pdo_query($sql_query);

$sql_query = <<<____SQL
		--
		-- Table structure for table `data_join_review`
		--

		CREATE TABLE IF NOT EXISTS `data_join_review` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `data_id` int(11) NOT NULL,
		  `review_id` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
____SQL;
$this->pdo_query($sql_query);

$sql_query = <<<____SQL
			--
			-- Table structure for table `data_location`
			--

			CREATE TABLE IF NOT EXISTS `data_location` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `data_id` int(11) NOT NULL,
			  `name` text NOT NULL,
			  `real_name` text NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=719 DEFAULT CHARSET=latin1;
____SQL;
$this->pdo_query($sql_query);
$sql_query = file_get_contents("../sql/data_location.sql");
$this->pdo_query($sql_query);
/* insert baseline tables */
$sql_query = <<<____SQL
			CREATE TABLE IF NOT EXISTS `baselines` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `description` text NOT NULL,
			  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
____SQL;
$this->pdo_query($sql_query);

$sql_query = <<<____SQL
			CREATE TABLE IF NOT EXISTS `baseline_join_review` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `baseline_id` int(11) NOT NULL,
			  `review_id` text NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
____SQL;
$this->pdo_query($sql_query);

$sql_query = <<<____SQL
			CREATE TABLE IF NOT EXISTS `baseline_join_project` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `baseline_id` int(11) NOT NULL,
			  `project_id` int(11) NOT NULL,
			  `lru_id` int(11) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
____SQL;
$this->pdo_query($sql_query);

$sql_query = <<<____SQL
			CREATE TABLE IF NOT EXISTS `baseline_join_data` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `baseline_id` int(11) NOT NULL,
			  `data_id` int(11) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
____SQL;
$this->pdo_query($sql_query);
$sql_query = file_get_contents("../sql/baselines.sql");
$this->pdo_query($sql_query);
/*  Last revision of a document */
$sql_query = <<<____SQL
			CREATE TABLE IF NOT EXISTS `data_last` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `reference` text NOT NULL,
			  `data_id` int(11) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
____SQL;
$this->pdo_query($sql_query);
/*  Last data read */
$sql_query = <<<____SQL
			CREATE TABLE IF NOT EXISTS `last_data_read` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `data_id` int(11) NOT NULL,
			  `read_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  `user_id` int(11) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
____SQL;
$this->pdo_query($sql_query);
/*  Enterprises type */
$sql_query = <<<____SQL
			CREATE TABLE IF NOT EXISTS `enterprise_type` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` text NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5;
			INSERT INTO `enterprise_type` (`id`, `name`) VALUES
			(1, 'Aircraft Manufacturer'),
			(2, 'Equipment Supplier'),
			(3, 'Engineering Consulting'),
			(4, 'Rail sector manufacturer');
____SQL;
$this->pdo_query($sql_query);
/* Group type */
$sql_query = <<<____SQL
				CREATE TABLE IF NOT EXISTS `group_type` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `name` text NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;
				
				--
				-- Contenu de la table `group_type`
				--
				
				INSERT INTO `group_type` (`id`, `name`) VALUES
				(1, 'Specification'),
				(2, 'Plan'),
				(3, 'Interface'),
				(4, 'Verification'),
				(5, 'Certification'),
				(6, 'Safety'),
				(7, 'Production'),
				(8, 'Design'),
				(9, 'Configuration'),
				(10, 'Notes and MoM'),
				(11, 'Methodology');
____SQL;
$this->pdo_query($sql_query);
/* Scope */
$sql_query = <<<____SQL
			CREATE TABLE IF NOT EXISTS `scope` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `scope` text NOT NULL,
			  `description` text NOT NULL,
			  `abrvt` text NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;
			
			--
			-- Contenu de la table `scope`
			--
			
			INSERT INTO `scope` (`id`, `scope`, `description`, `abrvt`) VALUES
			(1, 'System', '', 'Sys'),
			(2, 'Software', '', 'Sw'),
			(3, 'Hardware', '', 'Hw'),
			(4, 'Equipment', '', 'Eqpt'),
			(5, 'PLD', '', 'PLD'),
			(6, 'Board', '', 'Board');
____SQL;
$this->pdo_query($sql_query);
/* Wiki */
$sql_query = <<<____SQL
			CREATE TABLE IF NOT EXISTS `spip_articles` (
			  `id_article` bigint(21) NOT NULL auto_increment,
			  `surtitre` text NOT NULL,
			  `titre` text NOT NULL,
			  `soustitre` text NOT NULL,
			  `id_rubrique` bigint(21) NOT NULL default '0',
			  `descriptif` text NOT NULL,
			  `chapo` mediumtext NOT NULL,
			  `texte` longtext NOT NULL,
			  `ps` mediumtext NOT NULL,
			  `date` datetime NOT NULL default '0000-00-00 00:00:00',
			  `statut` varchar(10) NOT NULL default '0',
			  `id_secteur` bigint(21) NOT NULL default '0',
			  `maj` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
			  `export` varchar(10) default 'oui',
			  `date_redac` datetime NOT NULL default '0000-00-00 00:00:00',
			  `visites` int(11) NOT NULL default '0',
			  `referers` int(11) NOT NULL default '0',
			  `popularite` double NOT NULL default '0',
			  `accepter_forum` char(3) NOT NULL default '',
			  `date_modif` datetime NOT NULL default '0000-00-00 00:00:00',
			  `lang` varchar(10) NOT NULL default '',
			  `langue_choisie` varchar(3) default 'non',
			  `id_trad` bigint(21) NOT NULL default '0',
			  `extra` longtext,
			  `id_version` int(10) unsigned NOT NULL default '0',
			  `nom_site` tinytext NOT NULL,
			  `url_site` varchar(255) NOT NULL default '',
			  PRIMARY KEY  (`id_article`),
			  KEY `id_rubrique` (`id_rubrique`),
			  KEY `id_secteur` (`id_secteur`),
			  KEY `id_trad` (`id_trad`),
			  KEY `lang` (`lang`),
			  KEY `statut` (`statut`,`date`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
____SQL;
$this->pdo_query($sql_query);
$sql_query = file_get_contents("../sql/wiki.sql");
$this->pdo_query($sql_query);
$sql_query = file_get_contents("../sql/do.sql");
$this->pdo_query($sql_query);
$sql_query = file_get_contents("../sql/abd100.sql");
$this->pdo_query($sql_query);
	}
    private function db_connect() {
		if ($this->dbh === null){
			require_once("Atomik/Db/Instance.php");
			//$this->dsn = 'mysql:host='.$this->db_server.';dbname='.$this->db_select;
			$this->dsn = 'mysql:host='.$this->db_server;
			$statement = $this->pdo_query("show databases where `Database` = '".Atomik::get('db_config/select')."'");
			$list = $statement->fetch();
			if ($list !== false){
				$statement = $this->pdo_query("SHOW TABLES FROM ".Atomik::get('db_config/select'));
				$nb = $statement->rowCount();
				if ($nb == 0){
					$list = false;
				}
			}
			if ($list === false){
				/* no database exists */
				if (!defined('NO_ATOMIK')){
					header('Location:create_db');
				}
				else{
					header('Location:atomik/index.php?action=create_db');
				}			
				exit();				
			}
			else {
				$this->dsn = 'mysql:host='.$this->db_server.';dbname='.$this->db_select;
				$this->dbh = new Atomik_Db_Instance($this->dsn, $this->db_user, $this->db_pass);
				$this->dbh->connect();
			
			}
		} 
    }
    public function db_backup() {
		Atomik::needed('Tool.class');
		Atomik::needed('User.class');
		Atomik::needed('Mail.class');
		$this->backup_filename = Tool::dbBackup();
		if ($this->backup_filename !== false){
	        $this->taille = filesize($this->backup_filename);
			/* send database by mail */
			$context['user_logged_id']=User::getIdUserLogged();
			$mail = new Mail(&$context);
	        if ($mail->getAccess()){
	            $mail->setSubject("Database backup");
	            $to['me'] = "olivier.appere@zodiacaerospace.com";
	            $mail->setRecipients(&$to);
	            $mail->createHeader();
	            $mail->createParent();
	            $html = '<img src="cid:id_database" border="0" alt ="" title="" />';
	            $html.= "<h3 style='font-family: \"Century Gothic\",\"Trebuchet MS\",Helvetica,Arial,Geneva,sans-serif;font-size: 1.2em;line-height: 1.1;'>Database backup.</h3>";
	            $mail->setBody($html);
				if ($this->backup_filename != ""){ 
					$mail->attach($this->backup_filename,dirname(__FILE__).DIRECTORY_SEPARATOR.
																	 "..".DIRECTORY_SEPARATOR.
																	 "..".DIRECTORY_SEPARATOR.$this->backup_filename);
				}
				$mail->writeBody();			
				/* Second child, zodiac logo */
				$dir_img_path=dirname(__FILE__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."actions".DIRECTORY_SEPARATOR."mail".DIRECTORY_SEPARATOR."jpeg".DIRECTORY_SEPARATOR;
				$child_second = $mail->create_child($dir_img_path."db_comit.png","id_database");			
				if ($mail->getArchive()){
					$result = $mail->save();
				}
				else{
					$result = $mail->save();
				}
			}
			$dest_path = dirname(__FILE__).DIRECTORY_SEPARATOR.
									"..".DIRECTORY_SEPARATOR.
									"..".DIRECTORY_SEPARATOR.
									"..".DIRECTORY_SEPARATOR."result".DIRECTORY_SEPARATOR;
			Tool::move($this->backup_filename,
						$dest_path.$this->backup_filename);		
			ob_start("manage_log");
			$text = "MySQL Database backup performed at ".date('H:i:s')." on ".date('Y/m/d');
			echo $text;
			ob_end_clean();	
			echo "<li class='success' style='list-style-type: none;'>".$text."</li>";
		}
		else{
			$text = "No database backup done.";
			echo "<li class='failed' style='list-style-type: none;'>".$text."</li>";
		}
    }
    public function qams_backup() {
		Atomik::needed('Tool.class');
		$this->backup_filename = Tool::appliBackup();
		if ($this->backup_filename !== false){		
			$dest_path = dirname(__FILE__).DIRECTORY_SEPARATOR.
									"..".DIRECTORY_SEPARATOR.
									"..".DIRECTORY_SEPARATOR.
									"..".DIRECTORY_SEPARATOR."result".DIRECTORY_SEPARATOR;
			Tool::move($this->backup_filename,
						$dest_path.$this->backup_filename);
			ob_start("manage_log");
			$text = "QAMS backup perfomed at ".date('H:i:s')." on ".date('Y/m/d');
			echo $text;
			ob_end_clean();
			echo "<li class='success' style='list-style-type: none;'>".$text."</li>";
		}
		else{
			$text = "No QAMS backup done.";
			echo "<li class='failed' style='list-style-type: none;'>".$text."</li>";
		}				
    }	
    public function db_drop_database() {
		$this->db_query("DROP DATABASE ".$this->db_select);
    }
    public function db_create_database() {
		$this->db_query("CREATE DATABASE ".$this->db_select);
    }
    public function db_update($update_filename){
		$config = Atomik::get('db_config');
		/* Line for finister.freeheberg.com */
		if($config['select'] == "finister"){
			$text = "No database update possible on this server (exec command is disabled).";
			echo "<li class='failed' style='list-style-type: none;'>".$text."</li>";
		}
		else{    	
			/* erase database */
			$this->db_drop_database();
			$this->db_create_database();
			if (preg_match("#\.sql$#",$update_filename)) {
				if ((self::getServerOS() == "mac")||(self::getServerOS() == "unix")){
					$command = "/Applications/XAMPP/xamppfiles/bin/mysql -h{$this->db_server} -u{$this->db_user} -p{$this->db_pass} {$this->db_select} < {$update_filename}";
				}
				else{
					$command = "{$this->bin_path}mysql -h{$this->db_server} -u{$this->db_user} -p{$this->db_pass} {$this->db_select} < {$update_filename}";
				}
			}
			else if (preg_match("#\.gz$#",$update_filename)) {
				$command = "gunzip -cf {$update_filename} | mysql -h{$this->db_server} -u{$this->db_user} -p{$this->db_pass} {$this->db_select}";
			}
			else if (preg_match("#\.zip$#",$update_filename)) {
				$command="";
			}	
			else {
				echo "TEST".$update_filename;
			}
			/* import database */
			//echo $command."<br>";
			//system($command,$retval);
			exec($command,$retval,$code);
			foreach($retval as $row){
				 echo $row."<br/>";
			}
			//if (!$retval) {
				//echo"Export error:".$retval."<br>";
	        //}
	        //exit();
			ob_start("manage_log");
			$text = "Database updated at ".date('H:i:s')." on ".date('Y/m/d');
			echo $text;
			ob_end_clean();
		}		
    }	
	/* basic login function, with username, password arguments */
	public function logout($user){
		/* date_default_timezone_set('Europe/Paris'); */
		ob_start("manage_log");
		$text = "<=== User {$user[1]} {$user[2]} logged out at ".date('H:i:s')." on ".date('Y/m/d');
		echo $text;
		ob_end_clean();		
	}
	public static function login($username="guest", 
						  		 $password="") {
		if($username=="guest"){
			$array = array("anonymous","Mister","Nobody","",0,"",1,"");
			$serial_array=serialize($array);
			setcookie("bug_cookie", $serial_array) or die('impossible de creer le cookie');
			/* Go to home page */
			header('Location:atomik/index.php?action=home');
		}				  	
		$user = new User;
		$row = $user->getUserByName($username);
		if($row !== false) {
			if ($password == $row['password']) {
				$array[0] = $row['username'];
				$array[1] = $row['fname'];
				$array[2] = $row['lname'];
				$array[3] = $row['email'];
				$array[4] = $row['is_admin'];
				$array[5] = $row['last_logged'];
				$array[6] = $row['id'];
				$array[7] = $_SERVER['REMOTE_ADDR'];
				ob_start("manage_log");
				$text = "===> User {$array[1]} {$array[2]} logged in at ".date('H:i:s')." on ".date('Y/m/d')." from IP address {$_SERVER['REMOTE_ADDR']}\n";
				$text.= "--- Navigator used: ".$_SERVER['HTTP_USER_AGENT'];
				$text.= "\n";
				echo $text;
				ob_end_clean();					
				/* serialize array and store it in a cookie */
				$serial_array=serialize($array);
				setcookie("bug_cookie", $serial_array) or die('impossible de creer le cookie');
				/* Go to home page */
				print("<script language='javascript' type='text/javascript'>self.document.location='atomik/index.php?action=home'</script>");
				exit();
			}
			else {
				print("<script language='javascript' type='text/javascript'>alert('Password is not valid. Please try again.')</script>");
				// header('Location: index.php');
				print("<script language='javascript' type='text/javascript'>self.document.location='index.php'</script>");
				exit();
			}
		}
		else{
				print("<script language='javascript' type='text/javascript'>alert('Username does not exits. Please try again.')</script>");
				print("<script language='javascript' type='text/javascript'>self.document.location='index.php'</script>");
				// header('Location: index.php');
				exit();			
		}
	}
	public static function getOS(){
		return(self::$os_system);
	}
	public static function getServerOS(){
		return(self::$os_server_system);
	}		
	public static function setOS(){
		/* This function get the OS used by the client. */
		if (preg_match("#Linux#", $_SERVER['HTTP_USER_AGENT'])) {
            self::$os_system = "unix";
		}
		else if (preg_match("#Macintosh#", $_SERVER['HTTP_USER_AGENT'])) {
            self::$os_system = "mac";
		}
		else if (preg_match("#iPhone#", $_SERVER['HTTP_USER_AGENT'])) {
            self::$os_system = "iphone";
		}
		else {
		    self::$os_system = "windows";
		}
		return($_SERVER['HTTP_USER_AGENT']);
	}
    public static function setServerOS(){
        ob_start();
        phpinfo(INFO_GENERAL);
        $phpinfo = array('phpinfo' => array());
        if(preg_match_all('#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(System.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s', ob_get_clean(), $matches, PREG_SET_ORDER))
        $system = $matches[0][3];
        /* This function get the OS used by the client. */
        if (preg_match("#Linux#", $system)) {
            self::$os_server_system = "unix";
        }
        else if (preg_match("#Darwin#", $_SERVER['HTTP_USER_AGENT'])) {
            self::$os_server_system = "mac";
        }
        else if (preg_match("#iPhone#", $_SERVER['HTTP_USER_AGENT'])) {
            self::$os_server_system = "iphone";
        }
        else {
            self::$os_server_system = "windows";
        }
        return($system);
    }		
    public function __construct ($connect=true){
		$config = Atomik::get('db_config');
		/* Line for finister.freeheberg.com */
		if($config['select'] == "finister"){
			ini_set('include_path',ini_get('include_path').':/home/finister/www/atomik/app/plugins/Db/libraries');
		}
        self::setOS();
	
		$this->bin_path =$config['bin_path'];
		$this->sept_zip_path = $config['sept_path'];
		$this->qams_path = $config['qams_path'];
		$this->working_dir = $config['zip_path'];
		$this->db_server = $config['server'];
		$this->db_user = $config['user'];
		$this->db_pass = $config['pass'];
		$this->db_select = $config['select'];
		$this->backup_dir = $config['backup_dir'];
		if ($connect == true){
			$result = $this->db_connect();
		}
		else {
			$result=true;
		}
		return($result);
	}
}
