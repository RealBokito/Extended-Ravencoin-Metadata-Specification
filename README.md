# Extended Ravencoin Metadata Specification
<pre>
RIP: 15
Title: RIP 15 Extended Ravencoin Metadata Specification
Authors: Realbokito
Status: Draft
Type: Process
Reference Implementation: https://github.com/RealBokito/Extended-Ravencoin-Metadata-Specification
Created: 2019-09-14
</pre>

==Abstract==
The following is a proposal for a new, more advanced metadata specification in order to describe assets on the Ravencoin blockchain. This specification provides for a common extensible system for metadata to be stored in a structured JSON format as well as help with generating valid JSON formatted metadata for new or reissued assets.

==Motivation==
This Ravencoin Improvement Proposal (RIP) is a proposal to extend or replaces the currently used Ravencoin Metadata Specification by Tron Black July 24, 2018 to provide for a more detailed description of assets stored on the Ravencoin blockchain. The metadata currently stored on IPFS consist of multiple non-required fields. These fields can describe certain assets, such as but not limited to securities, stocks, bonds, real-estate and contracts. However, the current specification has a few challenges:
*	The lack of a structured way of describing an asset could introduce low quality descriptions of the assets in the blockchain.
*	For some, there may be fields missing which would better describe their asset(s) on the blockchain. 
*	There is no requirement to use any of the currently specified fields, and asset issuers can decide to either implement some of these fields or choose different names for these fields.
*	The unstructured way of providing metadata may make it difficult for wallet and explorers to display data when the input is filtered on specific field names.

This RIP:
*	will include a single way to implement one or multiple schemas into the Ravencoin Metadata specification. 
*	proposes the development of multiple schemas for various types of assets, such as books, toys, paintings, invoices and warranty documents and registrations to help improve the quality of the metadata on the blockchain.
*	proposes the use and implementation of validation mechanisms based on a schema system into Ravencoin Core and asset explorers on a voluntary basis as well as the development and use of online Ravencoin Metadata generators to create, store and utilize the new, higher quality structured information.

To goal is to setup a collaborative, community supported development with a mission to create, maintain, and promote schemas for structured data on blockchains, i.e. Ravencoin.

==Methodology==
RIP15 (Extended Ravencoin Metadata Specification) consists of two types of JSON structured resources:
*	A JSON structured Ravencoin Metadata Schema resource which describes the structure of a Metadata of the type JSON object
*	A JSON structured Ravencoin Metadata resource which describes the details of an asset using one or multiple Ravencoin Metadata JSON objects

==Ravencoin Metadata Schemas==
The Extended Ravencoin Metadata Specification utilizes a schema system in order to describe the set of (required) fields and data types used in describing the asset. Ravencoin Metadata Schema files have the following naming convention using lower cases only: Schema-name_Schema-Version.json, e.g. asset_data_0.1.json, ipfs_attachments_1.1.json.
As example, the following describes the metadata fields of the schema CAR.
```
{
   "schema": “CAR”,
   "version": 0.1,
   "model": {
      “type”: “string”,
      “required”: 1
   },
   "manufacturer":  {
      “type”: “string”,
      “required”: 1
   },
   "buildyear":  {
      “type”: “integer”,
      “required”: 1
   },
   "licenseplate":  {
      “type”: “string”,
      “required”: 1
   },
   "bodytype":  {
      “type”: “string”,
      “required”: 0
   },
   "co2emissions":  {
      “type”: “integer”,
      “required”: 0
   },
   "fuelcapacity": {
      “type”: “integer”,
      “required”: 0
   }
}
```
In some situations there might be a need for nested fields such as a physical addresses. Schema files can indicate nested fields and their data types using the key fields. In the example below the admin_data schema has a Physical_address-field which holds a JSON object of nested fields. The nested fields are directly accessible through the fields-key:
```
{
	"schema":"admin_data",
	"version": 0.1,
	"physical_address": {
		"required":1,
		"type": "string",
		 "fields":{
			"street_address1": {
				"required":1,
				"type": "string"
			},
			"street_address2": {
				"required":0,
				"type": "string"
			},
			"city": {
				"required":1,
				"type": "string"
			},
		}
	}
}
```
The fields-key is expected to be a JSON object. If a fields-key is found, but not of the data type Object and/or not a valid JSON structure, it can and should be treated as a normal field holding some kind of value.
The support of nested fields is provided in order to be as flexible as possible and nested fields can be unlimited. While implementing support for RIP15 developers are advised to take into account the number of levels for nested fields. While 2 to 5 levels seems reasonable, 10 or more levels might indicate misusage of the system. However, it might also be an indicator of a possible use case scenario for which a new schema is required but yet to be developed.

==Single Schema Usage==
In RIP15 the issuer is able to include the schema and schema version used in describing an asset: 
```
{
   “schema”:”CAR”,
   “version”: “0.1”,
    …….
}
```
Schema indicates which schema has been used to describe the asset in more detail. When using a single schema to describe an asset it is advised to maintain the older Ravencoin Metadata fieldnames for backwards compatibility for existing and/or older assts explorers. For issuers who plan to exchange the asset on a certain platform which supports the extended metadata specification, the original fields may be removed if backwards compatibility is not a requirement.
Version indicates which version of the schema model has been used. Different version numbers will allow for future updates of schemas while providing backward compatibilities for those who like to support it. The version numbering follows the major and minor versioning of schema files, e.g. 0.1, 1.5 and 2.0.
Combined, the schema and version number indicates which set of (required) fields and data types has been used. This will provide developers with dynamically loading the appropriate Ravencoin Metadata Schema to validate and display the metadata in a wallet, explorer or platform.
The combined CAR metadata description in this example will result in the following valid JSON output:
```
{
   “schema”:”CAR”,
   “version”: “0.1”,
   "name": "Yaris",
   "issuer": "Toyota",
   "description": "Toyota Yaris",
   "forsale": true,
   "forsale_price": "5000 RVN",
   "model": "Yaris",
   "manufacturer": "Toyota",
   "buildyear": "2019",
   "licenseplate": “RU-092-Y”,
   "bodytype": "hatchback",
   "co2emissions": 0.2, 
   "fuelcapacity": 75
}
```

==Multiple Schema’s==
An Asset registered on the Ravencoin blockchain can only include one IPFS resource (hash) and depending on the type and use of an asset the issuer may have the need to provide for high quality metadata for legal or other reasons. In order to support multiple schemas with metadata to describe an asset RIP 15 proposes the following mechanism to utilize and combine two or more schemas and introduces the usage of the metadata field. The metadata field will hold an array of JSON objects. Each JSON object is a single Ravencoin Metadata Schema object on its own, and describes a set of predefined (required) fields and data types, as specified in the Schema file.
```
{
   "metadata": [{
      “schema”:”Schema name 1”,
      “version”: “0.1”,
     “fieldname1”:………., 
     “fieldname2”:……….,
     “fieldname3”:……….,
   }, 
   {
      “schema”:”Schema name 2”,
      “version”: “0.1”, 
     “fieldname1”:………., 
     “fieldname2”:……….,
     “fieldname3”:……….,

   },
   ………
   ]
}
```
This structure should be followed in cases where multiple schema specifications are used. As such, an asset can include a structured set of information which can be parsed and displayed by wallets and explorers. See the file Proof of Concept/metadata.json for an example.

When using multiple schemas to describe an asset there is no reason to maintain the older Ravencoin Metadata fieldnames for backwards compatibility, but issuer who prefer to do so can still add and use these fields.

==External Resources==
Although primarily used by the Ravencoin blockchain, the Extended Ravencoin Metadata Specification does not require IPFS as the main storage supplier. External resources provides for more flexibilities and gives the issuer access to the following fields to describe an external resource:
*	**external_resource** (required): an URI to a structured Extended Ravencoin Metadata resource in JSON or a different type of resource. When referencing a different type resource such as a PDF file or HTML webpage the use of the external_mime and external_size field is advised and increases the chances of wallets or explorers to display the external resource. 
*	**external_mime** (optional): the mimetype of the resource.
*	**external_size** (optional): the size of the external resource. 
*	**external_schema** (optional): an alternative JSON formatted schema being used by the external resource. In order to allow wallets and asset explorers to understand the structure of the metadata, issuers can include an URI to a JSON formatted resources describing the metadata which is being presented. The schema needs to be structured according to the Ravencoin Metadata Schema guidelines above. 
The support of external resources is advised when an issuer wants to use a different system or systems to store metadata instead of the currently supported IPFS network. It can also be used to redirect users to a website or file.
The above keys will help developers with the detection of an external resource. Any valid combination of the above keys can be used and found in 1 or mutliple JSON schema object(s) and is an indication of an external reference. However, whether or not you, the developer, decides to support External Resources is at your own discretion.
The following example show three JSON Metadata Objects:
*	Object 1. A normal JSON Metadata object
*	Object 2. An external JSON Metadata object supporting an community supported Schema
*	Object 3. An custom external JSON Metadata object with a reference to an external Schema
```
{
   "name": "tZero",
…………
   "metadata": [{
      “schema”:”Schema name 1”,
      “version”: “0.1”,
     “fieldname1”:………., 
     “fieldname2”:……….,
     “fieldname3”:……….,
   }, 
   {
      “schema”:”sto_metadata”,
      “version”: “0.1”, 
      "external_resource": "https://www.tzero.com/sto-metadata.json",
   },
   {
      “schema”:”tZero_Exchange_Custom”,
      “version”: “0.1”, 
      "external_resource": "https://www.tzero.com/exchange-metadata.json",
      "external_mime": "text/json",
      "external_size": 4, 
      " external_schema ": "https://www.tzero.com/exchange-metadata-schema.json",
   },
   ………
   ]
}
```

==Implementation==
Some of the reasons to implement and support for RIP15 are:
*	It lowers the bar for issuers by supplying predefined and validated schemas to chose from,
*	It motivates issuers to provide for higher quality metadata linked to assets stored on the Ravencoin blockchain,
*	It may stimulate the development of future RIPs and proposals which can or may utilize this structured way of storing Ravencoin Metadata, such as RIP 11.
At https://github.com/RealBokito/Extended-Ravencoin-Metadata-Specification there is a Proof of Concept available which validates an Extended Ravencoin Metadata file using PHP.
Developers of wallets who support the Ravencoin Asset Protocol, as well as asset explorers are free to implement the whole or parts of this RIP15 at their own discretion.
The author of RIP15 proposes that the community starts developing various Ravencoin Metadata Schemas which should be maintained on the RavenProject Github page as a separate repository.
