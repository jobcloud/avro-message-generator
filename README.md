# avro-message-generator

This is a library that makes it easier to generate a random avro kafka message.

## Setup
### Docker :whale:
For Mac users: Try to use the official download from https://store.docker.com/editions/community/docker-ce-desktop-mac.
This way you are able to use it with less configuration. After install you have to add the volumes (defined in docker-composer.yml)
to the filesharing folders under preferences in the docker-gui.

#### Prerequisites

##### Add user id to your shell init file
Add the following to your .bashrc (or to the respective dot file if you don't use bash)
```bash
export USER_ID=$(id -u)
```

##### Create .env file
```bash
cp docker/.env.example docker/.env
```

##### Run schema registry declared in docker extra hosts.

## How it works
Avro message generator first loads avro schema from schema registry, based on topic name, and then generates avro message based on loaded schema and appropriate data source. 

###Data sources
Avro message generator supports multiple data sources.

#### Faker
Default data source is Faker (https://github.com/FakerPHP/Faker). That means that Avro message generator will generate message with dummy data using Faker, schema/field value will be generated based on a schema/field type, if other data sources are not provided. 

Faker can be used as data source in data definitions as well.

#### Data definition
Avro message generator supports data definitions as data source as well. 
Data definition is json file and it can be global or schema specific. Each data definition file can contain one or more definitions.
All data definitions files should be stored in the same directory, path of the directory should be passed during initialization of DataDefinitionProvider object.
Name of the global data definition file should be "global", name of schema specific data definition file should be the same as schema name in the schema registry (TopicNameStrategy).

#### Predefined payload
Avro message generator supports predefined payload as data source as well, and this data source has highest priority if it is used. Predefined payload should be either data appropriate to schema type or numeric/associative multidimensional array which follows schema structure (for complex schema types), and it can be passed directly in call of generate method. 

### Priority of data sources
Avro message generator uses SchemaFieldValueResolver which resolves schema/field value based on available data sources and some priority.

First it will check if predefined payload is provided, if it is, it will try to find value for current field based on field name by taking care about nesting level (for fields in complex schema types), for primitive schema types it will use predefined payload as it is. 

If predefined payload is not provided or resolver can not find value for particular schema/field in predefined payload it will check if schema specific data definition file is provided, if it is, it will try to find definition inside it. For particular field it will try to find definition based on numeric or associative key (for complex schema types). 
For primitive schema types it will use definition stored in file.

If schema specific data definition file is not provided or does not contain definition for schema/field, resolver will check if global data definition file is provided, if it is, it will try to find definition for particular field based on field name. Definitions for primitive schema types should not be defined in global data definition file.

And finally, in case that resolver can not resolve schema/field value based on predefined payload and data definitions files, it will generate value using Faker based on schema/field type.

### Predefined payload and data definition structure

Avro schema can be either primitive or complex type. 

Some primitive types are: int, string, boolean...

Some complex types are: Records, Enums, Arrays...

#### Predefined payload
As mentioned earlier, predefined payload is either data appropriate to schema type or numeric/associative multidimensional array which follows schema structure (for complex schema types).

##### Primitive schema types examples:
String schema type
```json
{"type": "string"}
```

```php
$predefinedPayload = 'testData';

$generator->generateAvroMessageBody($topicName, $predefinedPayload);
```

Int schema type
```json
{"type": "int"}
```

```php
$predefinedPayload = 123;

$generator->generateAvroMessageBody($topicName, $predefinedPayload);
```

Boolean schema type
```json
{"type": "boolean"}
```

```php
$predefinedPayload = true;

$generator->generateAvroMessageBody($topicName, $predefinedPayload);
```

##### Complex schema types examples:
Record schema type
```json
{
  "name": "testName",
  "namespace": "testNameSpace",
  "type": "record",
  "fields": [
    {
      "name": "id",
      "type": "string"
    },
    {
      "name": "name",
      "type": "string"
    },
    {
      "name": "fieldOfArraySchemaType",
      "type": {
        "items": {
          "fields": [
            {
              "name": "nestedField1",
              "type": "string"
            },
            {
              "name": "nestedField2",
              "type": "int"
            }
          ],
          "name": "fieldOfRecordSchemaType",
          "type": "record"
        },
        "type": "array"
      }
    }
  ]
}
```
Name field is skipped from predefined payload, it will be generated using other data source.
```php
$predefinedPayload = [
    'id' => 'testId',
    'fieldOfArraySchemaType' => [
        0 => [
            'fieldOfRecordSchemaType' => [
                'nestedField1' => 'testValue',
                'nestedField1' => 123
            ]
        ]
    ]
];

$generator->generateAvroMessageBody($topicName, $predefinedPayload);
```

Array schema type
```json
{
  "items": "string",
  "type": "array"
}
```

```php
$predefinedPayload = [
    'testValue',
];

$generator->generateAvroMessageBody($topicName, $predefinedPayload);
```

#### Data definition
As mentioned earlier, data definition is json file, can be global or schema specific, can use Faker as data source or can have hard coded data. 
Each data definition file can contain one or more definitions. 

Definition format with hard coded data: 
```json
{
  "value": "someValue"
}
```

Definition format with Faker as data source:
```json
{
  "command": "text"
}
```
If Faker formatter requires arguments, they can be passed using arguments field.
```json
{
  "command": "text",
  "arguments": [10]
}
```

Schema specific data definition files used for schemas of primitive type always contain exactly one definition, like these above.

In schema specific data definition files used for schemas of complex type, each definition referring to nested field, that means that data definition file can contain definition for each nested field. 
In these data definition files, each defined definition should have associative key which is "path" from the root to the target field (composed of fields names and indexes separated with ".") including target field name/index at the end (root schema name should be omitted if exists). That means that data definition does not follow schema nesting format, it is always "one-dimensional" json document which corresponds to one-dimensional associative array in php.  

Complex schema type example:
```json
{
  "name": "testName",
  "namespace": "testNameSpace",
  "type": "record",
  "fields": [
    {
      "name": "id",
      "type": "string"
    },
    {
      "name": "name",
      "type": "string"
    },
    {
      "name": "fieldOfArraySchemaType",
      "type": {
        "items": {
          "fields": [
            {
              "name": "nestedField1",
              "type": "string"
            },
            {
              "name": "nestedField2",
              "type": "int"
            }
          ],
          "name": "fieldOfRecordSchemaType",
          "type": "record"
        },
        "type": "array"
      }
    }
  ]
}
```

Data definition file (testNameSpace.testName.json) example:
```json
{
  "id": {
    "command": "uuid"
  },
  "name": {
    "value": "someName"
  },
  "fieldOfArraySchemaType.0.fieldOfRecordSchemaType.nestedField1": {
    "command": "text",
    "arguments": [10]
  },
  "fieldOfArraySchemaType.0.fieldOfRecordSchemaType.nestedField2": {
    "value": 123
  }
}
```

As you can see, definition key is path from the root to the target field, composed of fields names and indexes (for array type schema) separated with "." including target field name at the end. Root schema name is omitted.

Global data definition file is common data source for all complex schema types. If some nested field is repeated through the several schemas, it can/should have definition in global data definition file, it is ideal candidate. 

Globad data definition file has the same structure as schema specific data definition file, the only one difference is that definition's associative key should not be path from the root to the target field, it should be target field name.

Let's imagine that we have several schemas of complex type, with fields "accountId" and "companyId" on the different nesting levels. These fields are ideal candidates for global data definition, so global data definition file (global.json) would look like:
```json
{
  "accountId": {
    "command": "uuid"
  },
  "companyId": {
    "command": "uuid"
  }
}
```
## Example
Schema:
```json
{
  "name": "testName",
  "namespace": "testNameSpace",
  "type": "record",
  "fields": [
    {
      "name": "id",
      "type": "string"
    },
    {
      "name": "name",
      "type": "string"
    },
    {
      "name": "fieldOfArraySchemaType",
      "type": {
        "items": {
          "fields": [
            {
              "name": "nestedField1",
              "type": "string"
            },
            {
              "name": "nestedField2",
              "type": "int"
            }
          ],
          "name": "fieldOfRecordSchemaType",
          "type": "record"
        },
        "type": "array"
      }
    }
  ]
}
```

Global data definition (global.json):
```json
{
  "id": {
    "value": "someId"
  }
}
```

Schema specific data definition (testNameSpace.testName.json): 
```json
{
  "name": {
    "value": "someName"
  },
  "fieldOfArraySchemaType.0.fieldOfRecordSchemaType.nestedField1": {
    "value": "nestedField1 value"
  }
}
```

Script:
```php
include '../vendor/autoload.php';

use FlixTech\AvroSerializer\Objects\RecordSerializer;
use FlixTech\SchemaRegistryApi\Registry\BlockingRegistry;
use FlixTech\SchemaRegistryApi\Registry\Cache\AvroObjectCacheAdapter;
use FlixTech\SchemaRegistryApi\Registry\CachedRegistry;
use FlixTech\SchemaRegistryApi\Registry\PromisingRegistry;
use GuzzleHttp\Client;
use Jobcloud\Avro\Message\Generator\AvroMessageGenerator;
use Jobcloud\Avro\Message\Generator\DataDefinition\Factory\DataDefinitionFactory;
use Jobcloud\Avro\Message\Generator\DataDefinition\Field\Factory\DataDefinitionFieldFactory;
use Jobcloud\Avro\Message\Generator\DataDefinition\Field\Validator\DataDefinitionFieldValidator;
use Jobcloud\Avro\Message\Generator\DataDefinition\Provider\DataDefinitionProvider;
use Faker\Factory;
use Jobcloud\Avro\Message\Generator\Payload\Factory\PayloadGeneratorFactory;
use Jobcloud\Avro\Message\Generator\Schema\Field\Value\Resolver\Factory\SchemaFieldValueResolverFactory;
use Jobcloud\Kafka\Message\KafkaAvroSchema;
use Jobcloud\Kafka\Message\KafkaAvroSchemaInterface;
use Jobcloud\Kafka\Message\Registry\AvroSchemaRegistry;

$payloadGeneratorFactory = new PayloadGeneratorFactory();

$faker = Factory::create();

$dataDefinitionFieldValidator = new DataDefinitionFieldValidator();

$dataDefinitionFieldFactory = new DataDefinitionFieldFactory();

$dataDefinitionFactory = new DataDefinitionFactory($dataDefinitionFieldValidator, $dataDefinitionFieldFactory);

$dataDefinitionProvider = new DataDefinitionProvider('./Definitions', $dataDefinitionFactory);

$dataDefinitionProvider->load();

$schemaFieldValueResolverFactory = new SchemaFieldValueResolverFactory($faker, $dataDefinitionProvider);

$schemaRegistryUri = 'http://kafka-schema-registry:9444';

$schemaVersion = KafkaAvroSchemaInterface::LATEST_VERSION;

$cachedRegistry = new CachedRegistry(
    new BlockingRegistry(
        new PromisingRegistry(
            new Client(['base_uri' => $schemaRegistryUri])
        )
    ),
    new AvroObjectCacheAdapter()
);

$registry = new AvroSchemaRegistry($cachedRegistry);
$recordSerializer = new RecordSerializer($cachedRegistry);

$topicName = 'unity.candidate.entity.candidate';

$registry->addBodySchemaMappingForTopic(
    $topicName,
    new KafkaAvroSchema($topicName.'-value', $schemaVersion)
);

$generator = new AvroMessageGenerator(
    $recordSerializer,
    $registry,
    $payloadGeneratorFactory,
    $schemaFieldValueResolverFactory
);

$message = $generator->generateAvroMessageBody($topicName, [
    'name' => 'name from predefined payload',
]);

// do something with avro encoded $message
```

For each field from the schema SchemaFieldValueResolver will resolve value based on available data sources and them priorities, so message before encoding will be array like this:
```text
array(3) {
  ["id"]=>
  string(36) "someId"
  ["name"]=>
  string(28) "name from predefined payload"
  ["fieldOfArraySchemaType"]=>
  array(1) {
    [0]=>
    array(2) {
      ["nestedField1"]=>
      string(18) "nestedField1 value"
      ["nestedField2"]=>
      int(6)
    }
  }
}
```