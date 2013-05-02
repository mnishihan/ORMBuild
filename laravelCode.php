<?php
	//has the template for generating node files

	class laravelCode{

	var $dir_name = "larvavel/eloquent/";
	var $index_file_name = "index.php";
	var $log_dir = "./laravelModels";	
	var $output_dir = "./laravelModels"; //this has to be here... its set from the outside!!

	var $ORM_file_content = array();
	var $ORM_base_file_content = array();
	var $ORM_process_file_content = array();

	var $ORM_file_name = array();
	var $ORM_base_file_name = array();
	var $ORM_process_file_name = array();

	var $overwrite = true;

	var $debug = true;
	var $c = ''; //comma character starts life as an empty string...
	var $other_tables = array(); //where we store our table names..
	var $links_js = "";
	var $map = array();

	var $relations = array();

	//var $namespace = 'namespace nod\orm;';
	var $namespace = '';


	function generate($table_data){

                $object_name = $table_data['object_name'];
                $table_name = $table_data['table_name'];
                $database = $table_data['database'];
                $object_label = $table_data['object_label'];
                $has_many = $table_data['has_many'];
                $belongs_to = $table_data['belongs_to'];

		$file_name = "$object_name.php";
		$full_file = "$this->output_dir/$file_name";
		$this->ORM_file_name[$object_name] = $full_file;

		$base_file_name = "$object_name"."Base.php";
		$full_base_file = "$this->output_dir/$base_file_name";
		$this->ORM_base_file_name[$object_name] = $full_base_file;
		
		$process_file_name = "$object_name.log";
		$full_process_file = "$this->log_dir/$process_file_name";
		$this->ORM_process_file_name[$object_name] = $full_process_file;


		$this->each_table_start($table_data);

		$this->ORM_process_file_content[$object_name] = "Object $object_name has_many:\n";
		$this->ORM_process_file_content[$object_name] .= var_export($has_many,true);
		$this->ORM_process_file_content[$object_name] .= "Object $object_name belongs_to:\n";
		$this->ORM_process_file_content[$object_name] .= var_export($belongs_to,true);
/*
		foreach($table_data['table_cols'] as $col){
			$this->each_col(array(
				'object_name' => $object_name,
				'table_name' => $table_name,
				'database' => $database,
				'object_label' => $object_label,
				'col_name' => $col,
				));
		}
*/
		
		$this->each_table_end($table_data);
		$this->write_file($object_name);
	}

	function each_table_start($table_data){

		$object_name = $table_data['object_name'];
		$table_name = $table_data['table_name'];
		$database = $table_data['database'];
		$object_label = $table_data['object_label'];

		$namespace = $this->namespace;

				//start headers...
		$this->ORM_file_content[$object_name] = "<?php
	$namespace

	class $object_name extends  $object_name"."Base{ 
		//put custom code here... look in the base class for generated relations..
		
	}

?>";
		$this->ORM_base_file_content[$object_name] = "<?php
//Generated by buildORM from the $database:$table_name

	$namespace

	class $object_name"."Base extends BaseORM{ //which extends Eloquent...

		public static \$table = '$table_name';
 
";


//		$this->index_text .= "\n//importing $object_name from table $database:$this_table\n"; 
//		$this->index_text .= "var $object_name = sequelize.import(__dirname + '/$this_file_name');\n";
//		$this->index_text .= "exports.$object_name = $object_name;\n";

	}



	function each_table_end($table_data){
                $object_name = $table_data['object_name'];
                $table_name = $table_data['table_name'];
                $database = $table_data['database'];
                $object_label = $table_data['object_label'];
                $has_many = $table_data['has_many'];
                $belongs_to = $table_data['belongs_to'];

	
//		echo "\n\n\n has many \n\n";
//		var_export($has_many);
//		echo "\n\n\n belongs to \n\n";
//		var_export($belongs_to);



		foreach($belongs_to as $key => $belongs_to_array){
			$prefix = $belongs_to_array['prefix'];
			$type = $belongs_to_array['type'];

			if($prefix == $type){
				$function_name = $type;			
			}else{
				$function_name = $prefix . "_" . $type;
			}
			$link_field = strtolower($function_name . "_id");

			
			$this->ORM_base_file_content[$object_name] .= "
        //autogenerated this function... 
        public function $function_name()
        {
                return \$this->belongs_to('$type','$link_field');
        }

";		
		}

		


                foreach($has_many as $key => $has_many_array){

			$prefix = $has_many_array['prefix'];
			$type = $has_many_array['type'];
	
		
			if($prefix == $object_name){
				$link_field = $prefix . "_id";
				$function_name = $type . "_bunch";
			}else{
				$link_field = $prefix . "_" . $type . "_id";
				$function_name = $type . "_from_$prefix"."_bunch";
			}
	
			$link_field = strtolower($link_field);

                        $this->ORM_base_file_content[$object_name] .= "
        //autogenerated this function... 
        public function $function_name()
        {
                return \$this->has_many('$type',$link_field);
               // return \$this->has_one('$type',$link_field);
        }

";
                }


		$this->ORM_base_file_content[$object_name] .= "
	}//the end of class $object_name		
?>";

	}

	function write_file($object_name){
		//always write the auto-generated stuff..
		file_put_contents($this->ORM_base_file_name[$object_name],$this->ORM_base_file_content[$object_name]);
		//always write the generate log for this file...
		file_put_contents($this->ORM_process_file_name[$object_name],$this->ORM_process_file_content[$object_name]);
		if(!file_exists($this->ORM_file_name[$object_name]) || $this->overwrite){
			//but only write the child class if it does not already exist...
			//unless we are overwriting everything... 
			//so that user customizations are remembered.
			file_put_contents($this->ORM_file_name[$object_name],$this->ORM_file_content[$object_name]);
		}
	}


}//end class

?>
