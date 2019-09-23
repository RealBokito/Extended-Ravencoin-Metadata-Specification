<?php
/*
The following is an example of validating a set of Meta Data, using schema files (where applicable).

Scenarios

Normal operation:
This test will validate all fields in metadata.json, without 
any errors

Invalid Meta Data:
When metadata.json is edited to utilze v0.2 of the 
asset_data_schema_o.2.json file, the asset_metadata will be invalidated and not 
display.

===============================================================================
MIT License

Copyright (c) 2019 RealBokito

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

//  Read the Meta Data 
$json_data = file_get_contents('metadata.json');

//  Decode the Meta Data to associative arrays
$data = json_decode($json_data,true);

//  Loop through each Meta Data object
foreach($data['metadata'] as $obj){
	//echo "Using schema: ".$obj['schema']." ";
	//echo "version: ".$obj['version']."\r\n";
	echo "Checking ".$obj['schema']." meta data.....\r\n";
	//  Validate the Meta Data
	$ret = validateMetaData($obj);
	if($ret){
		echo $obj['schema']." meta data is valid\r\n\r\n";
		//var_dump($obj);
	}else{
		echo $obj['schema']." meta data invalid\r\n\r\n";
	}
}

function validateMetaData($data){
	//  Put together the link the to schema file used for the Meta Data
	$schema = $data['schema'].'_schema_'.$data['version'].'.json';
	
	//  Validate if the Meta Data exists.
	//  It's up to the software engineer to decide if the non existence of a Meta Data Schema file results into a true or false validation
	if(file_exists('schemas/'.$schema)){
		//  Read the Schema file and decode into associative arrays
		//  NOTE: this part does not yet support nested arrays or objects!!!
		$schema = file_get_contents('schemas/'.$schema);
		$schema = json_decode($schema,true);
		
		//  Get the fieldnames from the Schema by abstracting the array keys
		//$schemaFields = array_keys($schema);
		//var_dump($data);
		//var_dump($schema);
		$ret = validate($data,$schema);
		if(!$ret)
			return false;
	}else{
		//  The software engineer will have to decide how it's program will behave when there is nog Schema file found
		echo "Schema file not found.\r\n";
		echo "Assume ";
	}
	
	//  All is good. Meta Data is valid
	return true;
}

function validate($data,$schema,$level=1){
	echo "Level: ".$level."...\r\n";
	//var_dump($data);
	//var_dump($schema);
	//  Get the fieldnames from the Schema by abstracting the array keys
	$schemaFields = array_keys($schema);
	
	//  Loop through all the fields
	foreach($schemaFields as $schemaField){
		if($schemaField == "schema" || $schemaField == "version")//  Exlude the schema and version fields
			continue;
		
		//  Check if a field is SET
		if(isset($data[$schemaField]) && !is_array($data[$schemaField])){
			echo $schemaField." is SET\r\n";
		}elseif(isset($data[$schemaField]) && is_array($data[$schemaField])){
			$level++;
			$ret = validate($data[$schemaField],$schema[$schemaField]['fields'],$level);			
			if(!$ret)
				return false;
			$level--;
			echo "Back at level: ".$level."\r\n";
		}else{
			//  Check if the NOT SET field is required
			if($schema[$schemaField]['required']){
				//  Invalidate the Meta Data because at least 1 field is missing
				echo "Schema validation error: ".$schemaField."-field is required...\r\n";
				return false;
			}
			//  The current field is missing, but not required
			echo $schemaField." is NOT set\r\n";
		}
	}
	
	//  Do one final check for fields described in the metadata but NOT in the schema and mark them as Unknown Field(s)
	$dataFields = array_keys($data); //  Get keys from the metadata
	$unknownFields = array_diff($dataFields, $schemaFields); //  Calculate the differences between the metadata keys and schema keys
	//  If any metadata keys are not found in the schema keys array, mark as unknown...
	if($unknownFields){
		foreach($unknownFields as $unknownField){
			echo $unknownField."  is an unknown field.\r\n";
		}
	}
	
	//  If we get here, assume everything is OK
	return true;
}
?>
