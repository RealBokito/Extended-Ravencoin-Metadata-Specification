<?php
/*
The following is an example to dynamically load forms using schema files.

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

//  Load all schemas from a folder
$files = scandir('testschemas/');

//  Cycle through all files and create an line the the form for selection
echo "<table><form method='post' id='schemaForm'> ";
foreach($files as $file){
	if ($file != "." && $file != "..") {
		$checked = "";
		if(isset($_POST['schema'])){
			if(in_array($file,$_POST['schema']))
				$checked = "CHECKED";
		}
		echo "<tr><td><input type='checkbox' name='schema[]' value='$file' $checked></td><td>$file</td></tr>\r\n";
	}
}
echo "
</table>
<input name='submit' type='submit' class='buttons'>
</form>";

//  Called when the user has made the schema selection
if(isset($_POST['schema']))
	loadForm($_POST['schema']);

//  Called when the user request to generate the Metadata in JSON format
if(isset($_POST['submit2']))
	generateMetadata($_POST);


function loadForm($schemas){
	echo "<form method='post' id='schemaForm'>";

	//  Cycle through every selected schema and compile the appropriated forms
	foreach($schemas as $schema){
		if(file_exists('testschemas/'.$schema)){
			//  Load the schema as an associative array
			$schema = file_get_contents('testschemas/'.$schema);
			$schema = json_decode($schema,true);
			//  Take the keys as schema fields
			$schemaFields = array_keys($schema);
			
			//  Compile the form
			echo "Schema <b>".$schema['schema']."</b>:<br>\r\n";
			foreach($schemaFields as $field){
				//  quick shortcut for all the input names: https://stackoverflow.com/questions/5802057/how-can-i-group-form-elements
				$name = $schema['schema']."['".$field."']";

				//  Add the Schema and Version as hidden fields
				if($field == "schema" || $field == "version"){
					echo '<input type="hidden" name="'.$name.'" value="'.$schema[$field].'"><br>'."\r\n";
					continue;
				//  Do not forget to bypass nested fields! These objects should not contain any type field, i.e. any object without a type field should be ignored
				}elseif( !isset($schema[$field]['type'])){
					continue;
				}
				
				//  Example of dynamically generating the inputs for String and Booleans
				if($schema[$field]['type'] == "string"){
					echo $field.': <input type="text" name="'.$name.'"><br>'."\r\n";
				}elseif($schema[$field]['type'] == "boolean"){
					echo $field.': <input type="radio" id="'.$field.'false" name="'.$name.'" value="false"><label>False</label>
						<input type="radio" id="'.$field.'true" name="'.$name.'" value="true"><label>True</label><br>'."\r\n";
				}
			}
			echo "<br>\r\n";
		}
	}
	echo "<input name='submit2' type='submit' class='buttons' value='Generate Metadata'></form>";
}

function generateMetadata($data){

	/*
		Do some checks first to make sure all required fields are used and valid
		See validation Prototype
	*/

	// Generate Metadata output in JSON
	$output = '{"extended":['; //  First few rows of the output
	foreach($data as $item){
		if($item == "Generate Metadata")
			continue;
		
			$output .= json_encode($item).',';//  JSON encoded schema inside the "Extended" array
	}

	$output .= ']}';//  Last few rows of the output
	var_dump($output);
}
?>
