<?php
Class Aircraft{
    public $id;
    public $name;
    public $manufacturer_id;    
    public $description;
    public $photo_file;
    public $thumbnail;
    public $list;

    function __construct(){  
    	$this->id = 0;
    	$this->name = "";
    	$this->manufacturer_id = 0;
        $this->photo_file=Atomik::asset("assets/images/aircrafts/earhart12_240x116.jpg");
        $this->thumbnail="";//""Atomik::asset("assets/images/256x256/OldPlane.png");
    }
    public static function getList($company_id){
		$aircraft_w_photo = new Aircraft;
        $list_data = self::getAircrafts($company_id);
        $list = $list_data->fetchAll(PDO::FETCH_ASSOC);
        foreach($list as $id => $aircraft):
            $aircraft_w_photo->get($aircraft['id']);
            //echo  $id.":".$aircraft['id'].":".$aircraft_w_photo->photo_file."<br/>";
            $list[$id]['photo_file'] = $aircraft_w_photo->photo_file;
            $list[$id]['thumbnail'] = $aircraft_w_photo->thumbnail;
            //echo $aircraft_w_photo->thumbnail."<br/>";
        endforeach;
        //var_dump($list);
        return($list);
    }
	public static function getAircrafts($company_id=""){
		Atomik::needed('Tool.class');
		$filter = Tool::setFilterWhere("enterprises.id",$company_id);	
        $sql_query = "SELECT aircrafts.id,".
                    "aircrafts.name as aircraft,".
                    "aircrafts.description,".
                    "company_id,".
                    "enterprises.name as company".
                    " FROM aircrafts LEFT OUTER JOIN enterprises ON aircrafts.company_id = enterprises.id".
                    $filter." ORDER BY `enterprises`.`name` ASC";        
        $list = A("db:".$sql_query);
		//$list = $aircrafts_list->fetchall();
		return($list);					
	}
	public static function getSystems($aircraft_id=""){
		Atomik::needed('Tool.class');
		$filter = Tool::setFilterWhere("aircraft_id",$aircraft_id);	
        $sql_query = "SELECT projects.id,".
                    "projects.project as system,".
                    "projects.description".
                    " FROM projects LEFT OUTER JOIN aircrafts ON aircrafts.id = projects.aircraft_id".
                    $filter." ORDER BY `projects`.`project` ASC";        
        $list = A("db:".$sql_query);
		//$list = $aircrafts_list->fetchall();
		return($list);		
	}
    public static function getAircraft($id){
        $aircraft = Atomik_Db::find('aircrafts','id='.$id);	
        return($aircraft);
    } 
    public static function getAircraftName($id){
		if ($id != ""){
			Atomik::needed('Tool.class');
			$filter = Tool::setFilterWhere("aircrafts.id",$id);	
			$sql_query = "SELECT aircrafts.id,".
						"aircrafts.name,".
						"aircrafts.description,".
						"company_id,".
						"enterprises.name as company".
						" FROM aircrafts LEFT OUTER JOIN enterprises ON aircrafts.company_id = enterprises.id".
						$filter." LIMIT 1";        
			$result = A("db:".$sql_query);
			if ($result != false){
				$aircraft = $result->fetch();
				$aircraft_name = $aircraft['company']." ".$aircraft['name'];
			}
			else{
				$aircraft_name = "";
			}
		}
		else{
			$aircraft_name = "";
		}
        return($aircraft_name);
    }   	
    public function get($id){
        $row = self::getAircraft($id);
        //var_dump($row);
        $this->id=$row['id'];
        $this->name=$row['name'];
        $this->manufacturer_id = $row['company_id'];
        $this->description=isset($row['description'])?$row['description']:"";
        $img_ext=isset($row['img_ext'])?$row['img_ext']:"jpg";
        $img_name = $id.".".$img_ext;
        $thumbnail_name = $id."_tb.".$img_ext;
        /*
        * Get picture $id.".jpg"
        */
        $img_path = dirname(__FILE__).DIRECTORY_SEPARATOR.
                "..".DIRECTORY_SEPARATOR.
                "..".DIRECTORY_SEPARATOR.
                "assets".DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR."aircrafts".DIRECTORY_SEPARATOR;
        if (file_exists($img_path.DIRECTORY_SEPARATOR.$img_name)) {
            $this->photo_file=Atomik::asset("assets/images/aircrafts/".$img_name);
            $this->thumbnail=Atomik::asset("assets/images/aircrafts/".$thumbnail_name);
        }
        else {
            $this->photo_file=Atomik::asset("assets/images/aircrafts/earhart12_240x116.jpg");
			$this->thumbnail=Atomik::asset("assets/images/aircrafts/earhart12_240x116.jpg");
        }
    }
}