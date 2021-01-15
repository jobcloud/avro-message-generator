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
Avro message generator can use multiple data sources.

#### Faker
Default data source is Faker (https://github.com/FakerPHP/Faker). That means that Avro message generator will generate message with dummy data using Faker, schema/field value will be generated based on a schema/field type, if other data sources are not provided. 

Faker can be used as data source in data definitions as well.

#### Data definition
Avro message generator supports data definitions as data source as well. 
Data definition is json file and it can be global or schema specific. 
All data definitions files should be stored in the same directory, path of the directory should be passed during initialization of DataDefinitionProvider object.
Name of the global data definition file should be "global", name of schema specific data definition file should be the same as schema name in the schema registry.

#### Predefined payload
Avro message generator supports predefined payload as data source as well, and this data source has highest priority if it is used. Predefined payload should be associative multidimensional array which follows schema structure, and it can be passed directly in call of generate method. 

### Priority of data sources
Avro message generator uses SchemaFieldValueResolver which resolves schema/field value based on available data sources and some priority.

First it will check is predefined payload provided, if it is, it will try to find value for that schema/field based on schema/field name by taking care about nesting level (for fields in complex schema types). 