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
  
## Quick reminder of rules
  
You don't have to keep _everything_ in this repository though : all that is really needed is the 
Library folder itself, and the composer dependency. Otherwise, it's only tests and example. You'll have 
some constraints to respect though : 
1. provide the good configuration data  

   As explained upper, the configuration data is needed. You may pass it without yaml file, 
   but you have to make sure an array with the $publicConfig and $privateConfig are given
   in the same manner than with yaml.
2. extend the class  

   You're free to make your repository and entity class as you wish, but they have to extend
   BaseRepository and BaseEntity class respectively (both are in the Library folder). Also, 
   your own entity must have getters and setters logical with your entity type.  
   (That is, take the examples of the entity : getField, setField, and if the field is an array of field,
    getFields, setFields and addField). Also, the array of field attribute MUST be after the entity id attribute.  
    Finally, you must give annotation slightly like doctrine (take the exemples here).
    
3. Follow the exceptions

   If you try to do something illegal (especially in annotation), you'll get an exception indicating
   the excepted behavior. Do as it says!
   
4. suppression and persist of entities:

   suppression and persist of entities functions well, but you have to be aware of some possibly misleading
   behavior : first, if you suppress the manyToOne side of a relationship, the OneToManySide won't be suppressed, and neither with the opposite.
   You must therefore first delete the children of the OneToMany relation, and then
   delete the OneToMany side of the relation (as shown in the deleteFilm.php example)
   
5. entities returned by the repository:

   For performance reason, please keep in mind that the repository return not real entities, but 
   but mocked entity with the real entity embed. Also, the request on linked entity won't be made
   before a getter or an adder is called, so if you need them in a custom method, please first call
   a getter for example.
   
6. repository usage:

   You'll have to use the parseToEntities method as shown in example in the FilmRepository.
   Still, the other methods of the baseRepository will still be available.