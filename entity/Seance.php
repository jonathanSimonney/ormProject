<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Seance
 * @Entity @Table(name="seances")
 */
class Seance
{
    /**
     * @var int
     * @Id @Column(type="integer") @GeneratedValue
     */
    protected $id;

    /**
     * @var DateTime
     * @Column(type="date")
     */
    protected $showtime;

    /**
     * @var Film
     * @ORM\ManyToOne(targetEntity="Film", inversedBy="seances")
     */
    protected $film;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return DateTime
     */
    public function getShowtime(): DateTime
    {
        return $this->showtime;
    }

    /**
     * @param DateTime $showtime
     */
    public function setShowtime(DateTime $showtime)
    {
        $this->showtime = $showtime;
    }

    /**
     * @return Film
     */
    public function getFilm(): Film
    {
        return $this->film;
    }

    /**
     * @param Film $film
     */
    public function setFilm(Film $film)
    {
        $this->film = $film;
    }
}