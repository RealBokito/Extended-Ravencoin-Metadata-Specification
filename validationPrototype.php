<?php
/*
The following is an example of validating a set of Metadata, using schema files (where applicable).

Test Files:
multiSchemaExample.json includes JSON objects with and without schemas as well as an external resource test
singleSchemaExample.json show the use of only a single schema without the extended array
tronSpecExample.json proof backwards compatibility, i.e. simply ignoring any schema validation :)

Test Schema Files:
admin_data_schema_0.1.json
asset_data_schema_0.1.json
asset_data_schema_0.2.json
externalschema.json <= used for external schema validation tests: https://www.assetsexplorer.com/metadata/schemas/externalschema.json

===============================================================================
MIT License

Copyright (c) 2021 RealBokito

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

//  Read the Metadata
$json_data = file_get_contents('tests/multiSchemaExample.json');

//  Decode the Metadata to associative arrays
$data = json_decode($json_data,true);

//  Check if multiple metadata object exists
if(isset($data['extended'])){
	//  Loop through each Metadata object
	foreach($data['extended'] as $obj){
		//  Validate the Metadata
		$ret = validateMetaData($obj);
		if($ret){
			echo $obj['schema']." Metadata is APPROVED\r\n\r\n";
		}else{
			echo $obj['schema']." Metadata is NOT APPROVED\r\n\r\n";
		}
	}
//  Check if a single schema has been used
}elseif(isset($data['schema']) && $data['version']){
	$ret = validateMetaData($data);
		if($ret){
			echo $data['schema']." Metadata is VALID\r\n\r\n";
		}else{
			echo $data['schema']." Metadata is INVALID\r\n\r\n";
		}
//  At this point assume no schemas were used
}else{
	echo "No schemas found.\r\n\r\n";
	var_dump(json_encode($data));
}

function validateMetaData($data){
	
	//  Put together the link the to schema file used for the Metadata
	$schema = $data['schema'].'_schema_'.$data['version'].'.json';
	
	//  Validate if the MetaData exists.
	//  It's up to the software engineer to decide if the non existence of a Metadata Schema file results into a true or false validation
	if(file_exists('testschemas/'.$schema)){
		//  Read the Schema file and decode into an associative array
		//  NOTE: this part does not yet support nested arrays or objects!!!
		$schema = file_get_contents('testschemas/'.$schema);
		$schema = json_decode($schema,true); // Instructing PHP to turn the JSON Object into a associative array!
		
		//  Start the validation process
		$ret = validate($data,$schema);
		if(!$ret)
			return false;
	}elseif(isset($data["external_resource"])){
		$ret = loadExternalResource($data);
		if($ret){
			echo $data["external_resource"]." is VALID\r\n";
			return true;
		}else{
			echo $data["external_resource"]." is INVALID\r\n";
			return false;
		}		
	}else{
		//  The software engineer will have to decide how its program will behave when there is no Schema file found
		echo "Schema file not found.\r\n";
		echo "Assume ";
	}
	
	//  All is good. Metadata is valid
	return true;
}

function validate($data,$schema,$level=1){
	//  Get the fieldnames from the Schema by abstracting the array keys
	$schemaFields = array_keys($schema);
	
	//  Loop through all the fields
	foreach($schemaFields as $schemaField){
		//  Exlude the schema and version fields
		if($schemaField == "schema" || $schemaField == "version")
			continue;
		
		//  Check if a field is SET
		if(isset($data[$schemaField]) && !is_array($data[$schemaField])){
			//  Check to see if the data type of the field is according to the specification
			if(isset($schema[$schemaField]["type"]) && $schema[$schemaField]["type"] != "REGEX"){
				if(checkType($data[$schemaField], $schema[$schemaField]["type"])){
					echo $schemaField." is SET and VALID\r\n";
				}else{					
					echo $schemaField." is SET but INVALID \r\n";
					return false;
				}
			}else{
				echo $schemaField." is SET\r\n";
			}

		//  We assume that this means there is a JSON object in the schema containing the nested fields
		}elseif(isset($data[$schemaField]) && is_array($data[$schemaField])){
			$level++;
			//  Recursive function! Be careful. Considering limiting the number of levels appropriate.
			$ret = validate($data[$schemaField],$schema[$schemaField],$level);	
			if(!$ret)
				return false;
			
			$level--;
			echo "Back at level: ".$level."\r\n";
		
		//  If the field was not found in the data array, we must verify if it is required or not according to the schema
		}else{
			if($schema[$schemaField]['required']){
				//  An error that the field is missing. This should also mean that the Metadata is invalidated.
				echo "Schema validation error: ".$schemaField."-field is required...\r\n";
				return false;
			}

			//  A warning that the current field is missing, but not required. We do nothing at this point
			echo $schemaField." is NOT set\r\n";
		}
	}
	
	//  Check for unknown fields described in the metadata but NOT in the schema
	$dataFields = array_keys($data); //  Get keys from the metadata
	$unknownFields = array_diff($dataFields, $schemaFields); //  Return differences between the 2 arrays
	if($unknownFields){
		foreach($unknownFields as $unknownField){
			echo $unknownField."  is an unknown field.\r\n";
		}
	}
	
	//  If we get to here, we assume everything is OK
	return true;
}

function checkType($field, $type){
	switch($type){
		case "string":
			return is_string($field);
		case "integer":
			return is_integer($field);
		case "array":
			return is_array($field);
		case "float":
			return is_float($field);
		case "numeric":
			return is_numeric($field);
		case "boolean":
				return is_bool($field);
		case "json":
			json_decode($field);
			return (json_last_error() == JSON_ERROR_NONE);
		default:
			return false;
	}
}

function loadExternalResource($data){
	if(isset($data["external_schema"])){
		//  Loading external Schema
		$externalSchema = file_get_contents($data["external_schema"]);
		$externalSchema = json_decode($externalSchema,true);

		//Loading external resource
		$externalData = file_get_contents($data["external_resource"]);
		$externalData = json_decode($externalData,true);
		
		//  Start the validation process
		$ret = validate($externalData,$externalSchema);
		if($ret){
			return true;
		}else{
			return false;
		}			
	//  Assume the external resource is not a JSON metadata object
	}else{
		/*
		Code to load external resource
		Load at own discretion!
		*/
	}
}
?>
