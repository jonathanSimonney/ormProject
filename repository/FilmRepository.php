<?php
namespace repository;

use Library\BaseRepository;

/**
 * Class FilmRepository
 * @Entity Film
 */

class FilmRepository extends BaseRepository
{
    public function getFilmsShownThe(\DateTime $date)
    {
        $dayBegin = $date->format('Y-m-d');
        $dayEnd = $date->modify('+1 day')->format('Y-m-d');
        $sqlWhere = '`seances`.`showtime` >= \''.$dayBegin.'\' AND `seances`.`showtime` < \''.$dayEnd.'\'';
        $joinStatement = 'JOIN seances ON `'.$this->dbColumn.'`.`id` = `seances`.film_id';

        return $this->parseToEntities($sqlWhere, [], $joinStatement);
    }
}