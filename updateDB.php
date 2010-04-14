<?php
	// check this file's MD5 to make sure it wasn't called before
	$prevMD5=@implode('', @file(dirname(__FILE__).'/setup.md5'));
	$thisMD5=md5(@implode('', @file("./updateDB.php")));
	if($thisMD5==$prevMD5){
		$setupAlreadyRun=true;
	}else{
		// set up tables
		if(!isset($silent)){
			$silent=true;
		}

		// set up tables
		setupTable('diseases', "create table if not exists `diseases` ( `id` INT unsigned not null auto_increment , primary key (`id`), `short_name` VARCHAR(40) not null , `latin_name` VARCHAR(40) , `description` TEXT , `other_details` TEXT , `comments` TEXT ) CHARSET latin1", $silent);
		setupTable('patients', "create table if not exists `patients` ( `id` INT unsigned not null auto_increment , primary key (`id`), `last_name` VARCHAR(40) not null , `first_name` VARCHAR(40) not null , `gender` VARCHAR(10) not null default 'Unknown' , `birth_date` DATE , `age` INT , `address` TEXT , `city` VARCHAR(40) , `state` VARCHAR(15) , `zip` CHAR(8) , `home_phone` VARCHAR(40) , `work_phone` VARCHAR(40) , `mobile` VARCHAR(40) , `other_details` TEXT , `comments` TEXT , `filed` DATETIME , `last_modified` VARCHAR(40) ) CHARSET latin1", $silent);
		setupTable('symptoms', "create table if not exists `symptoms` ( `id` INT unsigned not null auto_increment , primary key (`id`), `name` VARCHAR(80) not null , `description` TEXT , `comments` TEXT ) CHARSET latin1", $silent);
		setupTable('disease_symptoms', "create table if not exists `disease_symptoms` ( `id` INT unsigned not null auto_increment , primary key (`id`), `disease` INT unsigned not null , `symptom` INT unsigned not null , `expected_probability` VARCHAR(40) , `minimum` VARCHAR(40) , `maximum` VARCHAR(40) , `reading_other_value` VARCHAR(40) , `comments` TEXT ) CHARSET latin1", $silent);
		setupIndexes('disease_symptoms', array('disease','symptom'));
		setupTable('patient_symptoms', "create table if not exists `patient_symptoms` ( `id` INT unsigned not null auto_increment , primary key (`id`), `patient` INT unsigned not null , `symptom` INT unsigned not null , `observation_date` DATE , `observation_time` TIME , `symptom_value` VARCHAR(40) ) CHARSET latin1", $silent);
		setupIndexes('patient_symptoms', array('patient','symptom'));


		// save MD5
		if($fp=@fopen(dirname(__FILE__).'/setup.md5', 'w')){
			fwrite($fp, $thisMD5);
			fclose($fp);
		}
	}


	function setupIndexes($tableName, $arrFields){
		if(!is_array($arrFields)){
			return false;
		}

		foreach($arrFields as $fieldName){
			if(!$res=@mysql_query("SHOW COLUMNS FROM `$tableName` like '$fieldName'")){
				continue;
			}
			if(!$row=@mysql_fetch_assoc($res)){
				continue;
			}
			if($row['Key']==''){
				@mysql_query("ALTER TABLE `$tableName` ADD INDEX `$fieldName` (`$fieldName`)");
			}
		}
	}


	function setupTable($tableName, $createSQL='', $silent=true, $arrAlter=''){
		global $Translation;
		ob_start();

		echo "<div style=\"padding: 5px; border-bottom:solid 1px silver; font-family: verdana, arial; font-size: 10px;\">";
		if($res=@mysql_query("select count(1) from `$tableName`")){
			if($row=@mysql_fetch_array($res)){
				echo str_replace("<TableName>", $tableName, str_replace("<NumRecords>", $row[0],$Translation["table exists"]));
				if(is_array($arrAlter)){
					echo '<br />';
					foreach($arrAlter as $alter){
						if($alter!=''){
							echo "$alter ... ";
							if(!@mysql_query($alter)){
								echo "<font color=red>".$Translation["failed"]."</font><br />";
								echo "<font color=red>".$Translation["mysql said"]." ".mysql_error()."</font><br />";
							}else{
								echo "<font color=green>".$Translation["ok"]."</font><br />";
							}
						}
					}
				}else{
					echo $Translation["table uptodate"];
				}
			}else{
				echo str_replace("<TableName>", $tableName, $Translation["couldnt count"]);
			}
		}else{
			echo str_replace("<TableName>", $tableName, $Translation["creating table"]);
			if(!@mysql_query($createSQL)){
				echo "<font color=red>".$Translation["failed"]."</font><br />";
				echo "<font color=red>".$Translation["mysql said"].mysql_error()."</font>";
			}else{
				echo "<font color=green>".$Translation["ok"]."</font>";
			}
		}

		echo "</div>";

		$out=ob_get_contents();
		ob_end_clean();
		if(!$silent){
			echo $out;
		}
	}
?>