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
	
	//  Validate the Meta Data
	$ret = validateMetaData($obj);
	if($ret){
		var_dump($obj);
	}else{
		echo $obj['schema']." meta data invalid\r\n";
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
		$schemaFields = array_keys($schema);
		
		//  Loop through all the fields
		foreach($schemaFields as $field){
			if($field == "schema" || $field == "version")//  Exlude the schema and version fields
				continue;
			
			//  Check if a field is SET
			if(isset($data[$field])){
				//echo $field." is SET\r\n";
			}else{
				//  Check if the NOT SET field is required
				if($schema[$field]['required']){
					//  Invalidate the Meta Data because at least 1 field is missing
					echo "Schema validation error: ".$field."-field is required...\r\n";
					return false;
				}
				//  The current field is missing, but not required
				echo $field." is NOT set\r\n";
			}
		}
	}else{
		//  The software engineer will have to decide how it's program will behave when there is nog Schema file found
		//echo "Schema file not found.\r\n";
	}
	
	//  All is good. Meta Data is valid
	return true;
}
?>
