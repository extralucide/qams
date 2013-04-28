<?php
class Company{
	public $id;
	private $name;
	private $type_id;	
	private $description;
 	public $photo_file;
 	
	public function getName(){
		return($this->name);
	}
	public function getType(){
		return($this->type_id);
	}	
	public function getDescription(){
		return($this->description);
	}
	public function get($id){
		$sql_query = "SELECT enterprises.id,enterprises.name,enterprises.type_id,enterprises.description FROM enterprises LEFT OUTER JOIN enterprise_type ON enterprise_type.id = enterprises.type_id WHERE enterprises.id = {$id} ORDER BY enterprises.name ASC";
		//echo $sql_query;
		//$list = Atomik_Db::findAll('enterprises',null,'`name` ASC');
		$result = A('db:'.$sql_query)->fetch();
		$this->id = $result['id'];
		$this->name = $result['name'];
		$this->type_id = $result['type_id'];	
		$this->description = $result['description'];			
	}
	public static function getCompany($type_id=""){
		Atomik::needed('Tool.class');
		$which_type = Tool::setFilterWhere("enterprises.type_id",$type_id);	
		$sql_query = "SELECT enterprises.id,enterprises.name FROM enterprises LEFT OUTER JOIN enterprise_type ON enterprise_type.id = enterprises.type_id {$which_type} ORDER BY enterprises.name ASC";
		//echo $sql_query;
		//$list = Atomik_Db::findAll('enterprises',null,'`name` ASC');
		$list = A('db:'.$sql_query);
		return($list);	
	}
	public static function getCompanyTypeList(){
		$list = Atomik_Db::findAll('enterprise_type',null,'`name` ASC');
		return($list);	
	}			
	public static function getSelectCompany($selected,
											$onchange="inactive",
											$name="show_company",
											$type_id=""){
		$html='<label for="'.$name.'">Company:</label>';
		$html.='<select class="combobox"';
		if ($onchange=="active") {
			$html .= ' onchange="this.form.submit()"';
		}
		$html .= ' name="'.$name.'">';
		$html .= '<option value=""/> --All--';
		
		foreach(Company::getCompany($type_id) as $row):
			$html .= '<option value="'.$row['id'].'"';
			if ($row['id'] == $selected){ 
				$html .= " SELECTED ";
			}
			$html .=">".$row['name'];
		endforeach;
		$html .='</select>';
		return($html);
	}	
	public static function crap_getSelectCompany($selected,
											$onchange="inactive",
											$type=""){
		$html='<label for="'.A('menu/company').'">Company:</label>';
		$html.='<select class="combobox"';
		if ($onchange=="active") {
			$html .= ' onchange="this.form.submit()"';
		}
		$html .= ' name="'.A('menu/company').'">';
		$html .= '<option value=""/> --All--';
		
		foreach(Company::getCompany($type) as $row):
			$html .= '<option value="'.$row['id'].'"';
			if ($row['id'] == $selected){ 
				$html .= " SELECTED ";
			}
			$html .=">".$row['name'];
		endforeach;
		$html .='</select>';
		return($html);
	}	
	public static function getSelectCompanyType($selected,
											$onchange="inactive"){
		$html='<label for="company_type_id">Type:</label>';
		$html.='<select class="combobox"';
		if ($onchange=="active") {
			$html .= ' onchange="this.form.submit()"';
		}
		$html .= ' name="company_type_id">';
		$html .= '<option value=""/> --All--';
		
		foreach(Company::getCompanyTypeList() as $row):
			$html .= '<option value="'.$row['id'].'"';
			if ($row['id'] == $selected){ 
				$html .= " SELECTED ";
			}
			$html .=">".$row['name'];
		endforeach;
		$html .='</select>';
		return($html);
	}	
	public function __construct(){
		$this->id="";
		$this->name="";
		$this->description="";
		$this->photo_file=Atomik::asset("assets/images/les-temps-moderne.jpg");
	}
}