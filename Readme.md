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
serve at all, since all the options will have only one value allowed.  

Though, this will allow further improvement to be easier to develop. The only value currently allowed are the ones currently put in this file.  

In the future, you shall be able to choose with this file : 
> - which language you want your orm to use for queries (currently only mySQL is supported)
> - whether you want your orm to add automatically 
the entities and / or attributes for which you haven't specified a relation for the database
(currently only the strict mode is supported)
> - how you want yur data to be mapped (by annotation, in a yaml file etc.)
 Currently only the annotation format is supported.
#use

The orm itself is in the Library folder. the entity folder contains some very basics entities to make example for the orm.  
Finally, the test folder contains the examples of how to use the orm itself.  
  
####Further updates to do : 

- write the test
- make the orm pass the test
- make a better doc
- write the further improvement and some more examples.