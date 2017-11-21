#installation

To use this repository, do not forget to install composer. (The **doc** is available [here](https://getcomposer.org/))  
and to run the command
>composer install  

or 
>php composer.phar install

(depending of whether you installed it locally or ,globally.)

You'll need to create your own private.yml file, and within it,
 put the following line :   
 >
 >db_config:
 >> name: _'YOUR_DB_NAME'_  
 >> host: _'YOUR_HOST'_  
 >> user: _'YOUR_ADMIN_USERNAME'_  
 >> pass:  _'YOUR_ADMIN_PASSWORD'_
 >
 >

This file should be put in the config folder. 

As for the public.yml, it contains options for the orm itself, but to be honest, in V1 it won't 
barely serve at all, since almost all the options will have only one value allowed.  

Though, this will allow further improvement to be easier to develop. The only value currently
 allowed are the ones currently put in this file
(except for the entity_folder_path and the repository_folder_path).  

In the future, you shall be able to choose with this file : 
> - which language you want your orm to use for queries (currently only mySQL is supported)
> - whether you want your orm to add automatically 
the entities and / or attributes for which you haven't specified a relation for the database
(currently only the strict mode is supported)
> - how you want your data to be mapped (by annotation, in a yaml file etc.)
 Currently only the annotation format is supported.
> - whether the entities should generate the database or the opposite. (Currently, only the option 
where the entities generate the database is supported)
> - the entity folder path.
#use

The orm itself is in the Library folder. the entity folder contains some very basics entities to make example for the orm.  
Finally, the test folder contains the examples of how to use the orm itself.  
As a reminder, please note that your entity folder MUST have a getRepositoryName method, 
and that the corresponding repository must be in the repository folder path or the entity repository
will simply be the BaseRepository.  

You'll need to make sure all your entities extend the BaseEntity given in the library.
  