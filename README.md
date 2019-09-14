# Extended Ravencoin Metadata Specification
Please note:
The following is work in progress……
## Abstract
The following is a proposal for a new, more advanced metadata specification in order to describe assets. This specification provides for an extensible common system for Metadata to be stored in a structured JSON format.
Introduction
The following is a proposal to extend or replace the Ravencoin Metadata Specification to support more details about an assets stored on the blockchain. This proposal extends the specification uploaded by Tron Black July 24, 2018.
Support for multiple asset schemas
The metadata stored on IPFS currently consist of multiple non-required fields. These fields can describe certain assets, such as but not limited to securities, stocks, bonds, real-estate and contracts. However, the current specification has two challenges:
* There is no requirement to use any of the specified fields, and asset issuers can decide to either implement some of the fields above, choose different names or own, unknown fields. This unstructured way of providing metadata makes it difficult for asset viewers and explorers to display data when the input is filtered on specific field names.
* For some, there may be fields missing which would better describe their asset on the blockchain. The lack of additional fields and a more structure way of describing an asset could introduce low quality descriptions of the assets in the blockchain.
The proposal includes two ways to implement one or multiple schemas into the Ravencoin Metadata specification. This proposal also consist of developing multiple classes for various types of assets, such as books, toys, paintings, invoices and warranty documents and registrations. This proposal, inspired by schema.org, proposes to develop a more structured data approach in describing these assets. To goal is to setup a collaborative, community supported development with a mission to create, maintain, and promote schemas for structured data on blockchains, i.e. Ravencoin.
## Structure
The extended Ravencoin Metadata Specification consists of two types of JSON structured resources:
*	A JSON structured Ravencoin Metadata resource which describes the details of an asset,
*	A JSON structured Ravencoin Metadata Schema resource which describes the structure of the Metadata.
## Single asset schema
The current metadata structure is as follows:
```
{
   "name": "Yaris",
   "issuer": "Toyota",
   "description": "Toyota Yaris",
   "forsale": true,
   "forsale_price": "5000 RVN"
}
```
In this new specification the issuer is able to include the schema and schema version used, during the issuance of the asset as follow: 
```
{
   “schema”:”CAR”,
   “version”: “0.1”,
   "name": "Yaris",
   "issuer": "Toyota",
   "description": "Toyota Yaris",
   "forsale": true,
   "forsale_price": "5000 RVN"
}
```
**Schema** indicates which schema has been used to describe the asset in more detail. Similar fields, as opposed to the original metadata specification, can be merged together. 

**Version** indicates which version of the schema model has been used. Different version number will allow for future updates of schemas while providing backward compatibilities options for those who like to support it.

The inclusion of the Schema and Version tags will allow the use of structured Ravencoin Metadata Schemas. This will provide software engineers with a JSON structured set of field names, data types and whether or not a fields is mandatory. Below is an example of the CAR Metadata Schema.
```
{
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
Based on the above structure the following schema is valid:
(* = required fields)
```
{
   "model": "Yaris",*
   "manufacturer": "Toyota",*
   "buildyear": "2019",*
   "licenseplase": “RU-092-Y”,*
   "bodytype": "hatchback",
   "co2emissions": 0.2, 
   "fuelcapacity": 75
}
```
The combined metadata description in this example will result in the following JSON output:
```
{
   “schema”:”CAR”,*
   “version”: “0.1”,*
   "name": "Yaris",
   "issuer": "Toyota",
   "description": "Toyota Yaris",
   "forsale": true,
   "forsale_price": "5000 RVN",
   "model": "Yaris",*
   "manufacturer": "Toyota",*
   "buildyear": "2019",*
   "licenseplate": “RU-092-Y”,*
   "bodytype": "hatchback",
   "co2emissions": 0.2, 
   "fuelcapacity": 75
}
```
Please Note:
When using a single schema to describe an asset it is advised to maintain the older Ravencoin Metadata fieldnames for backwards compatibility reasons. Existing assts as well as older asset viewers and explorers can therefore coexist with those who do support extended Metadata descriptions. However, for issuers who plan to exchange the asset on a certain platform which supports the extended metadata specification, the original fields may be removed if backwards compatibility is not a requirement.

## Multiple Asset Schema’s
An Asset registered on the Ravencoin blockchain can only include one IPFS resource (hash). In order to support multiple schemas with metadata to describe your Asset two or more schemas can be combined into a single array.
```
{
   "name": "Yaris",
…………
   "extended": [{
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
One should implement this structure in cases where multiple schema specifications are used. As such, a cryptocurrency could use the schemas COIN, ICO and EXCHANGE to include a structured set of information which can be parsed by viewers and explorers.
```
{
   "name": "Ravencoin",
   "issuer": "Community",
   "description": "In a land……..",
   "extended": {
   “schema”:”COIN”,
   “version”: “0.1”,
  “Symbol”: “RVN”,
  “www”:”https://www.ravencoin.org/”
}, 
{
   “schema”:”EXCHANGE”,
   “version”: “0.1”, 
   “name”: “Binance”, 
   “url”: “www.binance.com”, 
   “tickers”: {
“RVNBTC”, 
“RVNLTC”, 
},
   “tickers”: {
“RVNBTC”, 
“RVNLTC”, 
},
},
}
```
## Resource
The extended Ravencoin Metadata Specification also allows for the support of external resources. External resources provides for more flexibilities. 
An issuer has access to the following fields to describe an external resource:
*	external_resource (required): an URI to a structured extended Ravencoin  Metadata resource in JSON or a different resource. When referencing a different resource such as a PDF file or HTML webpage the use of the external_mime field is advised. 
*	external_mime (optional): the mimetype of the resource.
*	external_size (optional): the size of the external resource in Kilobytes. Never required for redirecting links. 
*	external_schema (optional): an alternative JSON formatted schema being used by the external resource. In order to allow asset viewers or explorers, as well as other software systems to understand the structure of the metadata, issuers can include an URI to a JSON formatted resources describing the metadata which is being presented.
```
{
   "name": "tZero",
…………
   "extended": [{
   “schema”:”Schema name 1”,
   “version”: “0.1”,
  “fieldname1”:………., 
  “fieldname2”:……….,
  “fieldname3”:……….,
}, 
{
   “schema”:”STO”,
   “version”: “0.1”, 
   "external_resource": "https://www.tzero.com/sto-metadata.json",
},
{
   “schema”:”tZero_exchange”,
   “version”: “0.1”, 
   "external_resource": "https://www.tzero.com/exchange-metadata.json",
   "external_mime": "text/json",
   "external_size": 4, 
   "external_schema": "https://www.tzero.com/exchange-metadata-schema.json",,

},
………
]
}
```
The support of external resources is advised  when an issuer wants to use a different system or systems to store metadata instead of the currently support IPFS network. It can also be used to redirect users to a website or file.
